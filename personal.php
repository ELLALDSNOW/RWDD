<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

require_once 'conn.php'; 
$user_id = intval($_SESSION['user_id']);


$sql = "
  SELECT o.organization_id, o.name, o.description, o.priority, o.created_at, o.updated_at, o.created_by
  FROM organization o
  INNER JOIN organization_user ou ON o.organization_id = ou.organization_id
  WHERE ou.user_id = ?
  ORDER BY o.created_at DESC
";
$stmt = mysqli_prepare($dbConn, $sql);
if (!$stmt) {
    die("DB prepare failed: " . mysqli_error($dbConn));
}
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$orgs = [];
if ($res) {
    while ($r = mysqli_fetch_assoc($res)) $orgs[] = $r;
}
mysqli_stmt_close($stmt);


$pstmt = mysqli_prepare($dbConn, "SELECT project_id, name, description, priority, created_time FROM project WHERE user_id = ? AND (organization_id IS NULL OR organization_id = 0) ORDER BY created_time DESC");
mysqli_stmt_bind_param($pstmt, 'i', $user_id);
mysqli_stmt_execute($pstmt);
$pres = mysqli_stmt_get_result($pstmt);
$personal_projects = [];
if ($pres) { while ($r = mysqli_fetch_assoc($pres)) $personal_projects[] = $r; }
mysqli_stmt_close($pstmt);


function json_attr($data) {
    return htmlspecialchars(json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hive Personal</title>
  <link rel="stylesheet" href="personal.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>

<body>
  <header class="header">
    <div class="left-section">
      <a href="index.html">
        <img src="images/Group 24.png" alt="Hive Logo" class="logo">
      </a>
      <span class="brand">HIVE</span>

    </div>

    <div class="right-section">
      <div class="hamburger" id="hamburger">
        <span></span><span></span><span></span>
      </div>
    </div>
  </header>

  <nav class="dropdown" id="dropdown">
  <a href="organizations.php"><i class="fa-solid fa-user-plus"></i> Join Org</a>
  <a href="index.html"><i class="fa-solid fa-gem"></i> Landing Page</a>
  <a href="organizations.php"><i class="fa-solid fa-building-columns"></i> Organizations</a>
  <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
    <a href="login.php" id="logint"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </nav>

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-section">
      <div class="sidebar-section-header">
        <span>DASHBOARD</span>
      </div>
      <div class="sidebar-section-content">
        <button class="sidebar-btn org-btn" id="createOrgSidebar">
          <i class="fa-solid fa-building-columns"></i> New Organization HIVE
        </button>
        <button class="sidebar-btn personal-btn" id="createPersonalSidebar">
          <i class="fa-solid fa-user"></i> New Personal HIVE
        </button>
      </div>
    </div>



    <div class="sidebar-section">
      <div class="sidebar-section-header">
        <span>To-Do</span>
      </div>
      <div class="sidebar-section-content">
        <a href="todo.php" class="sidebar-btn todo-btn"><i class="fa-solid fa-square-check"></i> To-Do</a>
      </div>
    </div>

    <div class="sidebar-section">
      <div class="sidebar-section-header">
        <span>APPEARANCE</span>
      </div>
      <div class="sidebar-section-content">
        <label class="dark-mode-toggle">
          <input type="checkbox" id="darkModeToggle">
          <span class="toggle-slider"></span>
          <span class="toggle-label">Dark Mode</span>
        </label>
      </div>
    </div>
  </aside>

  <button class="sidebar-hamburger" id="sidebarToggle"><i class="fa fa-bars"></i></button>

  <main class="content">
    <div class="card-container" id="cardContainer">

      <section class="card-container" id="orgList">
 
        <?php if (count($personal_projects) > 0): ?>
          <?php foreach ($personal_projects as $proj): ?>
            <div class="card project-card yellow-card" data-id="<?php echo (int)$proj['project_id']; ?>">
              <div class="card-top">
                <div class="card-image-placeholder" aria-hidden="true"></div>
              </div>

              <h3 class="card-title"><?php echo htmlspecialchars($proj['name'], ENT_QUOTES); ?></h3>
              <div class="card-subtitle"><?php echo htmlspecialchars($proj['description']); ?></div>

              <div class="card-meta">Priority: <strong><?php echo htmlspecialchars($proj['priority']); ?></strong></div>

              <div class="card-actions">
                <a href="personalhive.php?project_id=<?php echo (int)$proj['project_id']; ?>" class="card-open">Open</a>

                <button class="card-btn edit"
                        data-id="<?php echo (int)$proj['project_id']; ?>"
                        data-name="<?php echo htmlspecialchars($proj['name'], ENT_QUOTES); ?>"
                        data-desc="<?php echo htmlspecialchars($proj['description'], ENT_QUOTES); ?>"
                        data-priority="<?php echo htmlspecialchars($proj['priority'], ENT_QUOTES); ?>">Edit</button>

                <form method="post" action="api/delete_project.php" style="display:inline;">
                  <input type="hidden" name="project_id" value="<?php echo (int)$proj['project_id']; ?>">
                  <button type="submit" class="card-btn delete">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        <?php if (count($orgs) === 0): ?>
          <div class="no-orgs">You have no organizations yet. Click "Create Organization" to add one.</div>
        <?php else: ?>
          <?php foreach ($orgs as $org): ?>
            <div class="card org-card" data-id="<?php echo (int)$org['organization_id']; ?>">
              <button class="uploadBtn" style="display:none"><i class="fa fa-image"></i></button>
              <div class="hexagon"><img src="https://via.placeholder.com/140x80?text=<?php echo urlencode(substr($org['name'],0,12)); ?>" alt=""></div>
              <h3><?php echo htmlspecialchars($org['name'], ENT_QUOTES); ?></h3>
              <div class="card-details">
                <div class="card-desc"><?php echo htmlspecialchars($org['description']); ?></div>
                <div class="card-meta">Priority: <strong><?php echo htmlspecialchars($org['priority']); ?></strong></div>
                <div class="card-actions" style="margin-top:8px;">
       
                  <a href="organization.php?org_id=<?php echo (int)$org['organization_id']; ?>" class="org-btn add">Open</a>

                  <button class="org-btn edit btn-edit-org"
                          data-id="<?php echo (int)$org['organization_id']; ?>"
                          data-name="<?php echo htmlspecialchars($org['name'], ENT_QUOTES); ?>"
                          data-desc="<?php echo htmlspecialchars($org['description'], ENT_QUOTES); ?>"
                          data-priority="<?php echo htmlspecialchars($org['priority'], ENT_QUOTES); ?>">Edit</button>
                  <?php if ((int)$org['created_by'] === $user_id): ?>
                    <form method="post" action="api/delete_organization.php" style="display:inline;" onsubmit="return confirm('Delete this organization?');">
                      <input type="hidden" name="org_id" value="<?php echo (int)$org['organization_id']; ?>">
                      <button type="submit" class="org-btn delete">Delete</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  </main>


  <div id="orgModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-content">
      <button id="closeOrgModal" class="close-btn">&times;</button>
      <h2 id="modalTitle">Create Organization</h2>
      <form id="orgForm" method="post" action="api/save_organization.php">
        <input type="hidden" id="org_id" name="org_id" value="">
        <label for="org_name">Name</label>
        <input id="org_name" name="name" type="text" required>
        <label for="org_desc">Description</label>
        <textarea id="org_desc" name="description" rows="3"></textarea>
        <label for="org_priority">Priority</label>
        <select id="org_priority" name="priority">
          <option>Heavy</option>
          <option selected>Medium</option>
          <option>Light</option>
        </select>
        <label for="org_password">Organization password (optional)</label>
        <div style="position:relative; display:flex; align-items:center;">
          <input id="org_password" name="org_password" type="password" placeholder="Optional: set password for access" style="padding:10px; border-radius:8px; border:1px solid #ccc; flex:1;">
          <button type="button" id="org_password_toggle" class="pwd-toggle" aria-label="Show password"><i class="fa fa-eye"></i></button>
        </div>
        <div class="modal-actions" style="margin-top:12px;">
          <button id="cancelOrgBtn" class="org-btn delete" type="button">Cancel</button>
          <button class="org-btn add" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>


  <div id="projectModal" class="modal" aria-hidden="true" style="display:none;">
    <div class="modal-content">
      <button id="closeProjectModal" class="close-btn">&times;</button>
      <h2 id="projectModalTitle">Create Personal HIVE</h2>
      <form id="projectForm" method="post" action="api/save_project.php" enctype="multipart/form-data">
        <input type="hidden" id="project_id" name="project_id" value="">
        <input type="hidden" name="organization_id" value="0">
        <label for="project_name">Name</label>
        <input id="project_name" name="name" type="text" required>
        <label for="project_desc">Description</label>
        <textarea id="project_desc" name="description" rows="3"></textarea>
        <label for="project_priority">Priority</label>
        <select id="project_priority" name="priority">
          <option value="high">High</option>
          <option value="medium" selected>Medium</option>
          <option value="low">Low</option>
        </select>
        <label for="project_due">Due Date</label>
        <input id="project_due" name="due_date" type="date">
        <div class="modal-actions" style="margin-top:12px;">
          <button id="cancelProjectBtn" class="org-btn delete" type="button">Cancel</button>
          <button class="org-btn add" type="submit">Save</button>
        </div>
      </form>
    </div>
  </div>

  <script src="personal.js"></script>
</body>
</html>