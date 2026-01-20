<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
  header('Location: login.html'); exit;
}
require_once 'conn.php';
$user_id = intval($_SESSION['user_id']);


$ustmt = mysqli_prepare($dbConn, "SELECT user_id, user_name, email_address, created_time, last_login FROM user_account WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($ustmt, 'i', $user_id);
mysqli_stmt_execute($ustmt);
$ures = mysqli_stmt_get_result($ustmt);
$user = $ures && mysqli_num_rows($ures) ? mysqli_fetch_assoc($ures) : null;
mysqli_stmt_close($ustmt);


$ostmt = mysqli_prepare($dbConn, "SELECT COUNT(DISTINCT organization_id) AS org_count FROM organization_user WHERE user_id = ?");
mysqli_stmt_bind_param($ostmt, 'i', $user_id);
mysqli_stmt_execute($ostmt);
$oRes = mysqli_stmt_get_result($ostmt);
$orgCount = ($oRes && $r = mysqli_fetch_assoc($oRes)) ? intval($r['org_count']) : 0;
mysqli_stmt_close($ostmt);


$pstmt = mysqli_prepare($dbConn, "SELECT COUNT(*) AS project_count FROM project WHERE user_id = ?");
mysqli_stmt_bind_param($pstmt, 'i', $user_id);
mysqli_stmt_execute($pstmt);
$pRes = mysqli_stmt_get_result($pstmt);
$projectCount = ($pRes && $r2 = mysqli_fetch_assoc($pRes)) ? intval($r2['project_count']) : 0;
mysqli_stmt_close($pstmt);


$listStmt = mysqli_prepare($dbConn, "SELECT project_id, name, priority, created_time FROM project WHERE user_id = ? ORDER BY created_time DESC LIMIT 8");
mysqli_stmt_bind_param($listStmt, 'i', $user_id);
mysqli_stmt_execute($listStmt);
$listRes = mysqli_stmt_get_result($listStmt);
$projects = [];
if ($listRes) while ($row = mysqli_fetch_assoc($listRes)) $projects[] = $row;
mysqli_stmt_close($listStmt);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Profile</title>
  <link rel="stylesheet" href="profile.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <main class="profile-wrap">
    <section class="profile-card">
      <div class="avatar" aria-hidden="true">
        <i class="fa fa-user"></i>
      </div>
      <h1 class="name"><?php echo $user ? htmlspecialchars($user['user_name']) : 'Unknown User'; ?></h1>
      <p class="email"><?php echo $user ? htmlspecialchars($user['email_address']) : ''; ?></p>

      <div class="stats">
        <div class="stat">
          <div class="num"><?php echo $orgCount; ?></div>
          <div class="label">Organizations</div>
        </div>
        <div class="stat">
          <div class="num"><?php echo $projectCount; ?></div>
          <div class="label">Projects</div>
        </div>
        <div class="stat">
          <div class="num"><?php echo $user ? htmlspecialchars(date('d M Y', strtotime($user['created_time'] ?? date('Y-m-d')))) : '-'; ?></div>
          <div class="label">Joined</div>
        </div>
      </div>

      <div class="section">
        <h3>Recent Projects</h3>
        <ul class="proj-list">
          <?php if (count($projects) === 0): ?>
            <li class="empty">No projects yet.</li>
          <?php else: foreach ($projects as $pr): ?>
            <li>
              <a href="personalhive.php?project_id=<?php echo (int)$pr['project_id']; ?>" class="proj-link"><?php echo htmlspecialchars($pr['name']); ?></a>
              <span class="proj-meta"><?php echo htmlspecialchars($pr['priority']); ?> • <?php echo htmlspecialchars(date('d M Y', strtotime($pr['created_time']))); ?></span>
            </li>
          <?php endforeach; endif; ?>
        </ul>
      </div>
      
      <div class="actions">
        <a class="btn" href="personal.php">Back to Dashboard</a>
        <a class="btn muted" href="login.php">Logout</a>
      </div>
    </section>
  </main>
</body>
</html>
