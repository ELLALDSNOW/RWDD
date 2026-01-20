<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}
require_once 'conn.php';

$user_id = intval($_SESSION['user_id']);


$org_id = 0;
if (isset($_GET['org_id'])) $org_id = intval($_GET['org_id']);
elseif (isset($_SESSION['selectedOrgId'])) $org_id = intval($_SESSION['selectedOrgId']);
if ($org_id <= 0) {
    die("No organization selected.");
}


$chk = mysqli_prepare($dbConn, "SELECT role_in_org FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $org_id, $user_id);
mysqli_stmt_execute($chk);
$resChk = mysqli_stmt_get_result($chk);
if (!$resChk || mysqli_num_rows($resChk) === 0) {
    mysqli_stmt_close($chk);
    die("<script>alert('You are not a member of this organization.'); window.location.href='personal.php';</script>");
}
$roleRow = mysqli_fetch_assoc($resChk);
$myRole = $roleRow['role_in_org'] ?? 'member';
mysqli_stmt_close($chk);


$stmt = mysqli_prepare($dbConn, "SELECT organization_id, name, description, created_at, logo, priority, created_by FROM organization WHERE organization_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $org_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
    die("Organization not found.");
}
$org = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);


$projects = [];
$ps = mysqli_prepare($dbConn, "SELECT project_id, name, description, status, created_time, due_date, priority FROM project WHERE organization_id = ? ORDER BY created_time DESC");
mysqli_stmt_bind_param($ps, 'i', $org_id);
mysqli_stmt_execute($ps);
$rps = mysqli_stmt_get_result($ps);
if ($rps) {
    while ($p = mysqli_fetch_assoc($rps)) {
        $projects[(int)$p['project_id']] = $p;
    }
}
mysqli_stmt_close($ps);


if (count($projects) > 0) {
    $ids = array_keys($projects);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

  $tblCheck = mysqli_query($dbConn, "SHOW TABLES LIKE 'tasks'");
  if (!$tblCheck || mysqli_num_rows($tblCheck) === 0) {

    $createSql = "CREATE TABLE IF NOT EXISTS `tasks` (
      `task_id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(250) NOT NULL,
      `description` text DEFAULT NULL,
      `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
      `priority` enum('low','medium','high') DEFAULT 'medium',
      `comments` longtext DEFAULT NULL,
      `start_date` date DEFAULT NULL,
      `due_date` date DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `project_id` int(11) NOT NULL,
      PRIMARY KEY (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    if (!mysqli_query($dbConn, $createSql)) {
      error_log('organization.php: failed to create tasks table: ' . mysqli_error($dbConn));
    } else {
      error_log('organization.php: created missing tasks table');
    }
  }

  $intIds = array_map('intval', $ids);
  $inList = implode(',', $intIds);
  $sql = "SELECT task_id, title, description, status, priority, start_date, due_date, created_at, project_id FROM tasks WHERE project_id IN ($inList) ORDER BY created_at ASC";

  $rt = false;
  try {
    $rt = mysqli_query($dbConn, $sql);
  } catch (mysqli_sql_exception $e) {
    error_log('organization.php: tasks query threw exception: ' . $e->getMessage());
    $rt = false;
  }

  if ($rt) {
    while ($t = mysqli_fetch_assoc($rt)) {
      $pid = (int)$t['project_id'];
      if (!isset($projects[$pid]['tasks'])) $projects[$pid]['tasks'] = [];
      $projects[$pid]['tasks'][] = $t;
    }
    mysqli_free_result($rt);
  } else {
 
    error_log('organization.php: tasks query failed or returned no result: ' . mysqli_error($dbConn));
    $createSql = "CREATE TABLE IF NOT EXISTS `tasks` (
      `task_id` int(11) NOT NULL AUTO_INCREMENT,
      `title` varchar(250) NOT NULL,
      `description` text DEFAULT NULL,
      `status` enum('todo','in_progress','done','blocked') DEFAULT 'todo',
      `priority` enum('low','medium','high') DEFAULT 'medium',
      `comments` longtext DEFAULT NULL,
      `start_date` date DEFAULT NULL,
      `due_date` date DEFAULT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `project_id` int(11) NOT NULL,
      PRIMARY KEY (`task_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    try {
      $created = mysqli_query($dbConn, $createSql);
    } catch (mysqli_sql_exception $e) {
      error_log('organization.php: create tasks table threw exception: ' . $e->getMessage());
      $created = false;
    }
    if (!$created) {
      error_log('organization.php: failed to create tasks table: ' . mysqli_error($dbConn));

      foreach ($projects as $pid => $p) {
        if (!isset($projects[$pid]['tasks'])) $projects[$pid]['tasks'] = [];
      }
    } else {
      error_log('organization.php: created missing tasks table');
 
      $rt2 = false;
      try {
        $rt2 = mysqli_query($dbConn, $sql);
      } catch (mysqli_sql_exception $e) {
        error_log('organization.php: retry tasks query threw exception: ' . $e->getMessage());
        $rt2 = false;
      }
      if ($rt2) {
        while ($t = mysqli_fetch_assoc($rt2)) {
          $pid = (int)$t['project_id'];
          if (!isset($projects[$pid]['tasks'])) $projects[$pid]['tasks'] = [];
          $projects[$pid]['tasks'][] = $t;
        }
        mysqli_free_result($rt2);
      } else {

        foreach ($projects as $pid => $p) {
          if (!isset($projects[$pid]['tasks'])) $projects[$pid]['tasks'] = [];
        }
      }
    }
  }
}

function json_attr($data) {
    return htmlspecialchars(json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title><?php echo htmlspecialchars($org['name'] ?? 'Organization'); ?> — Organization</title>
  <link rel="stylesheet" href="organization.css">
  <link rel="stylesheet" href="richtext.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    .header {
      background: #ffe100;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
 
    body {
      padding-top: 72px;
    }

    .left-section {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .logo {
      height: 40px;
      width: auto;
    }

    .brand {
      font-size: 1.5rem;
      font-weight: 700;
      color: #222;
    }

    .home-btn {
      background: #222;
      color: #ffe100;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
    }

    .hamburger {
      width: 30px;
      height: 24px;
      position: relative;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .hamburger span {
      display: block;
      width: 100%;
      height: 3px;
      background: #222;
      border-radius: 3px;
      transition: all 0.3s;
    }

    .hamburger.active span:nth-child(1) {
      transform: translateY(10px) rotate(45deg);
    }

    .hamburger.active span:nth-child(2) {
      opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
      transform: translateY(-10px) rotate(-45deg);
    }

    .dropdown {
      position: fixed;
      top: 72px;
      right: -300px;
      width: 280px;
      background: white;
      box-shadow: -2px 2px 10px rgba(0,0,0,0.1);
      border-radius: 12px 0 0 12px;
      padding: 1rem 0;
      transition: right 0.3s ease;
      z-index: 999;
    }

    .dropdown.active {
      right: 0;
    }

    .dropdown a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0.8rem 1.5rem;
      color: #222;
      text-decoration: none;
      font-weight: 500;
      transition: background 0.2s;
    }

    .dropdown a:hover {
      background: #f5f5f5;
    }

    .dropdown a i {
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }
  </style>
</head>
<body>
  <header class="header">
    <div class="left-section">
      <a href="index.html">
        <img src="images/Group 24.png" alt="Hive Logo" class="logo">
      </a>
      <span class="brand">HIVE</span>
  <a href="personal.php"><button class="home-btn">HOME</button></a>
    </div>

    <div class="right-section">
      <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
  </header>

  <nav class="dropdown" id="dropdown">
  <a href="organizations.php"><i class="fa-solid fa-user-plus"></i> Join Org</a>
  <a href="index.html"><i class="fa-solid fa-gem"></i> Landing Page</a>
    <a href="#"><i class="fa-solid fa-building-columns"></i> Organizations</a>
  <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
    <a href="login.php" id="logint"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
  </nav>

  <main class="org-main" data-org-id="<?php echo (int)$org['organization_id']; ?>" data-has-projects="<?php echo count($projects) > 0 ? '1' : '0'; ?>" style="position: relative; z-index: 1;">
    <section class="org-header-section glass-card">
      <div class="org-img-wrap">
        <div class="hexagon hexagon-lg">
          <img id="orgImage" src="<?php echo $org['logo'] ? 'data:image/*;base64,'.base64_encode($org['logo']) : 'https://via.placeholder.com/220x128?text='.urlencode(substr($org['name'],0,12)); ?>" alt="Organization Image">
        </div>
      </div>

      <div class="org-info">
        <div class="org-title">
          <h1 id="orgName"><?php echo htmlspecialchars($org['name']); ?></h1>
        </div>

        <div class="org-desc">
          <p id="orgDesc"><?php echo nl2br(htmlspecialchars($org['description'])); ?></p>
        </div>

        <div class="org-date">
          <span id="orgDate">Created: <?php echo htmlspecialchars(date('d M Y', strtotime($org['created_at']))); ?></span>
        </div>


        <div class="org-actions-container">
          <div class="org-actions">
            <button onclick="showAddProjectModal()" class="org-btn add"><i class="fa fa-plus"></i> Add New Project</button>
            <button onclick="document.getElementById('editOrgModal').style.display='flex'" class="org-btn edit"><i class="fa fa-pen"></i> Edit Organization</button>
            <?php if ((int)$org['created_by'] === $user_id): ?>
              <form method="post" action="api/delete_organization.php" style="display:inline;" onsubmit="return confirm('Delete organization?');">
                <input type="hidden" name="org_id" value="<?php echo (int)$org['organization_id']; ?>">
                <button type="submit" class="org-btn delete"><i class="fa fa-trash"></i> Delete Organization</button>
              </form>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </section>

    <section class="org-projects-section">
      <h2>Projects</h2>
      <?php if (count($projects) === 0): ?>
        <div style="padding:24px; display:flex; flex-direction:column; gap:12px; align-items:center; justify-content:center;">
          <p style="color:#666; font-size:1.05rem;">No projects yet for this organization.</p>
          <button onclick="showAddProjectModal()" class="org-btn add"><i class="fa fa-plus"></i> Create your first project</button>
        </div>
      <?php else: ?>
      <div class="org-projects-list" id="orgProjectsList" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:24px; padding:24px;">
        <?php foreach ($projects as $proj):
            $projId = (int)$proj['project_id'];
            $proj_tasks = $proj['tasks'] ?? [];
         
            $total_tasks = count($proj_tasks);
            $completed = 0;
            $in_progress = 0;
            foreach ($proj_tasks as $task) {
              if ($task['status'] === 'Completed') $completed++;
              else if ($task['status'] === 'In Progress') $in_progress++;
            }
            $percent = $total_tasks > 0 ? round(($completed / $total_tasks) * 100) : 0;
        ?>
          <article class="project-card" data-project-id="<?php echo $projId; ?>" 
            style="background:white; border-radius:16px; padding:24px; box-shadow:0 4px 16px rgba(0,0,0,0.1);">
            
            <div class="project-header" style="display:flex; gap:16px; margin-bottom:16px;">
              <div class="hexagon" style="width:90px; height:90px; background:#f0f0f0; display:flex; align-items:center; justify-content:center; clip-path:polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);">
                <?php if ($proj['image']): ?>
                  <img src="data:image/*;base64,<?php echo base64_encode($proj['image']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                  <img src="https://via.placeholder.com/90x90?text=<?php echo urlencode(substr($proj['name'],0,1)); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                <?php endif; ?>
              </div>

              <div style="flex:1;">
                <h3 style="font-size:1.2rem; font-weight:600; margin:0 0 8px;"><?php echo htmlspecialchars($proj['name']); ?></h3>
                <div style="color:#666; font-size:0.9rem;">Created: <?php echo htmlspecialchars(date('d M Y', strtotime($proj['created_time']))); ?></div>
                <?php if ($proj['due_date']): ?>
                  <div style="color:#666; font-size:0.9rem;">Due: <?php echo htmlspecialchars(date('d M Y', strtotime($proj['due_date']))); ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="project-desc" style="margin-bottom:16px; color:#444; line-height:1.5;">
              <?php echo nl2br(htmlspecialchars($proj['description'])); ?>
            </div>

            <div class="project-stats" style="margin-bottom:16px;">
              <div class="progress-bar" style="width:100%; height:6px; background:#eee; border-radius:3px; margin-bottom:8px;">
                <div class="progress" style="width:<?php echo $percent; ?>%; height:100%; background:#ffe100; border-radius:3px;"></div>
              </div>
              <div style="display:flex; justify-content:space-between; color:#666; font-size:0.9rem;">
                <span><?php echo $percent; ?>% complete</span>
                <span><?php echo $total_tasks; ?> tasks</span>
              </div>
            </div>

            <div class="card-actions" style="display:flex; gap:8px;">
              <a href="personalhive.php?project_id=<?php echo $projId; ?>" class="org-btn add" style="flex:1; text-align:center; text-decoration:none;">
                <i class="fa fa-folder-open"></i> Open
              </a>
              <button onclick="showEditProjectModal(<?php echo htmlspecialchars(json_encode($proj)); ?>, false)" class="org-btn edit" style="flex:1;">
                <i class="fa fa-pen"></i> Edit
              </button>
              <form method="post" action="api/delete_project.php" style="flex:1;" onsubmit="return confirm('Delete this project and all its tasks?');">
                <input type="hidden" name="project_id" value="<?php echo $projId; ?>">
                <button type="submit" class="org-btn delete" style="width:100%;">
                  <i class="fa fa-trash"></i> Delete
                </button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </section>
  </main>


  <div class="modal" id="editModal" style="display:none">
    <div class="modal-content scrollable-modal">
      <span class="close" id="closeEditModal">&times;</span>
      <div id="editModalBody"></div>
    </div>
  </div>

  <div class="modal" id="projectModal">
    <div class="modal-content scrollable-modal">
      <span class="close" id="closeProjectModal">&times;</span>
      <div id="projectModalBody"></div>
    </div>
  </div>

 
  <div class="modal" id="editOrgModal" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-content scrollable-modal" style="max-width:700px; width:100%; border-radius:16px; box-shadow:0 4px 32px rgba(0,0,0,0.18); background:#fff; padding:32px 32px 24px 32px; position:relative;">
      <button id="closeEditOrgModal" class="close-btn" type="button" style="position:absolute; top:18px; right:18px; font-size:1.5rem; background:none; border:none; cursor:pointer;">&times;</button>
      <h2 id="modalTitle" style="font-size:2rem; font-weight:700; margin-bottom:24px;">Edit Organization</h2>
      <form id="editOrgForm" method="post" action="api/save_organization.php" style="display:flex; flex-direction:column; gap:18px;">
        <input type="hidden" id="edit_org_id" name="org_id" value="<?php echo (int)$org['organization_id']; ?>">
        <label for="edit_org_name" style="font-weight:600;">Name</label>
        <input id="edit_org_name" name="name" type="text" required value="<?php echo htmlspecialchars($org['name']); ?>" style="padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1.1rem;">
        <label for="edit_org_desc" style="font-weight:600;">Description</label>
        <textarea id="edit_org_desc" name="description" rows="3" style="padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1.1rem;"><?php echo htmlspecialchars($org['description']); ?></textarea>
        <label for="edit_org_priority" style="font-weight:600;">Priority</label>
        <select id="edit_org_priority" name="priority" style="padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1.1rem;">
          <option value="Heavy"<?php if ($org['priority'] === 'Heavy') echo ' selected'; ?>>Heavy</option>
          <option value="Medium"<?php if ($org['priority'] === 'Medium' || !$org['priority']) echo ' selected'; ?>>Medium</option>
          <option value="Light"<?php if ($org['priority'] === 'Light') echo ' selected'; ?>>Light</option>
        </select>
        <label for="edit_org_password" style="font-weight:600;">Organization password</label>
        <div style="position:relative; display:flex; align-items:center;">
          <input id="edit_org_password" name="org_password" type="password" placeholder="Leave blank to keep current password" style="padding:12px; border-radius:8px; border:1px solid #ccc; font-size:1.1rem; flex:1;">
          <button type="button" id="edit_org_password_toggle" class="pwd-toggle" aria-label="Show password"><i class="fa fa-eye"></i></button>
        </div>
        <div class="modal-actions" style="display:flex; justify-content:flex-end; gap:16px; margin-top:12px;">
          <button id="cancelEditOrgBtn2" class="org-btn delete" type="button" style="background:#fff; color:#e74c3c; border:1.5px solid #e74c3c; border-radius:8px; padding:10px 22px; font-weight:600; font-size:1.1rem; transition:background 0.2s;">Cancel</button>
          <button class="org-btn add" type="submit" style="background:#ffe100; color:#222; border:none; border-radius:8px; padding:10px 28px; font-weight:700; font-size:1.1rem; box-shadow:0 2px 8px rgba(0,0,0,0.06); transition:background 0.2s;">Save</button>
        </div>
      </form>
    </div>
  </div>

  <div class="modal" id="taskModal">
    <div class="modal-content task-modal-content scrollable-modal">
      <span class="close" id="closeTaskModal">&times;</span>
      <div id="taskModalBody"></div>
    </div>
  </div>

  <script src="organization.js" defer></script>
  <script>
   
    document.getElementById('hamburger').addEventListener('click', function() {
      this.classList.toggle('active');
      document.getElementById('dropdown').classList.toggle('active');
    });

  
    document.addEventListener('click', function(e) {
      const hamburger = document.getElementById('hamburger');
      const dropdown = document.getElementById('dropdown');
      if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) {
        hamburger.classList.remove('active');
        dropdown.classList.remove('active');
      }
    });
  </script>
</body>
</html>