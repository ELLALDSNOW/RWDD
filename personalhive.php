<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.html'); exit;
}
require_once 'conn.php';
$user_id = intval($_SESSION['user_id']);


$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : (isset($_SESSION['selectedProjectId']) ? intval($_SESSION['selectedProjectId']) : 0);
if ($project_id <= 0) {

  $project = null;
  $tasks = [];
} else {

  $stmt = mysqli_prepare($dbConn, "SELECT project_id, name, description, created_time, due_date, priority, user_id, organization_id FROM project WHERE project_id = ? LIMIT 1");
  mysqli_stmt_bind_param($stmt, 'i', $project_id);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  $project = $res && mysqli_num_rows($res) ? mysqli_fetch_assoc($res) : null;
  mysqli_stmt_close($stmt);

  $tasks = [];
  if ($project) {

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
        error_log('personalhive.php: failed to create tasks table: ' . mysqli_error($dbConn));
      } else {
        error_log('personalhive.php: created missing tasks table');
      }
    }

    $ts = false;
    try {
      $ts = mysqli_prepare($dbConn, "SELECT task_id, title, description, status, priority, due_date, created_at FROM tasks WHERE project_id = ? ORDER BY created_at ASC");
    } catch (mysqli_sql_exception $e) {
      error_log('personalhive.php: mysqli_prepare threw exception: ' . $e->getMessage());

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
        mysqli_query($dbConn, $createSql);
        error_log('personalhive.php: attempted to create missing tasks table');
      } catch (mysqli_sql_exception $e2) {
        error_log('personalhive.php: create tasks table threw exception: ' . $e2->getMessage());
      }
 
      try {
        $ts = mysqli_prepare($dbConn, "SELECT task_id, title, description, status, priority, due_date, created_at FROM tasks WHERE project_id = ? ORDER BY created_at ASC");
      } catch (mysqli_sql_exception $e3) {
        error_log('personalhive.php: mysqli_prepare retry threw exception: ' . $e3->getMessage());
        $ts = false;
      }
    }

    if ($ts) {
      mysqli_stmt_bind_param($ts, 'i', $project_id);
      mysqli_stmt_execute($ts);
      $trs = mysqli_stmt_get_result($ts);
      if ($trs) {
        while ($t = mysqli_fetch_assoc($trs)) $tasks[] = $t;
      }
      mysqli_stmt_close($ts);
    } else {
  
      error_log('personalhive.php: failed to prepare tasks select (final): ' . mysqli_error($dbConn));
      $tasks = [];
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Hive</title>
  <link rel="stylesheet" href="personalhive.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="hive-main-container">
  
    <div id="editProjectModal" class="hive-modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; align-items:center; justify-content:center;">
      <div class="hive-modal-backdrop" style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45);"></div>
      <div class="hive-modal-content" style="position:relative; z-index:2; background:#fff; border-radius:18px; box-shadow:0 8px 32px rgba(0,0,0,0.25); width:480px; max-width:96vw; padding:20px;">
          <h2 style="margin:0 0 12px; font-size:1.25rem;">Edit Project</h2>
          <div style="display:grid; grid-template-columns: 1fr 120px; gap:12px; align-items:start;">
            <div>
              <label style="display:block; font-weight:600; margin-bottom:6px;">Name</label>
              <input type="text" id="editProjectName" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
              <label style="display:block; font-weight:600; margin-bottom:6px;">Description</label>
              <textarea id="editProjectDesc" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; height:90px;"></textarea>
            </div>
            <div style="text-align:center;">
              <div style="width:100px; height:100px; margin:0 auto 8px; border-radius:10px; overflow:hidden; background:#f6f6f6; display:flex; align-items:center; justify-content:center;">
                <img id="editProjectImagePreview" src="" alt="Preview" style="width:100%; height:100%; object-fit:cover; display:none;">
              </div>
              <label style="display:block; font-weight:600; margin-bottom:6px;">Image</label>
              <input type="file" id="editProjectImage" accept="image/*" style="display:block; margin:0 auto;">
              <small style="color:#888; display:block; margin-top:8px;">Optional. Max 2MB.</small>
            </div>
          </div>
          <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:8px;">
            <button id="editProjectCancelBtn" style="background:#fff; border:1px solid #ccc; padding:8px 14px; border-radius:8px; cursor:pointer;">Cancel</button>
            <button id="editProjectSaveBtn" style="background:#ffe100; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">Save</button>
          </div>
        </div>
    </div>
    <aside class="hive-sidebar">
      <div class="hive-profile">
        <div class="hive-avatar" aria-hidden="true" style="width:72px;height:72px;border-radius:10px;background:#f0f0f0;flex:0 0 72px;margin-right:12px;"></div>
        <div class="hive-profile-info">
          <div class="hive-project-name">Project: <?php echo $project ? htmlspecialchars($project['name']) : 'No project selected'; ?></div>
          <div class="hive-project-desc"><?php echo $project ? nl2br(htmlspecialchars($project['description'] ?? '')) : ''; ?></div>
            <div class="hive-date">Date Created: <?php echo $project ? htmlspecialchars(date('d/m/Y', strtotime($project['created_time']))) : '-'; ?></div>
        </div>
      </div>
      <div class="hive-sidebar-btns">
        <button class="hive-btn add" id="addnewtask"><i class="fa fa-plus"></i> Add New Task</button>
        <button class="hive-btn edit"><i class="fa fa-pen"></i> Edit Project</button>
        <button class="hive-btn delete"><i class="fa fa-trash"></i> Delete Project</button>
      </div>
      
    </aside>
  <main class="hive-content" data-project-id="<?php echo (int)$project_id; ?>">
      <?php
       
        $totalTasks = count($tasks);
        $completed = 0;
        $inProgress = 0;
        $uninitiated = 0;
        foreach ($tasks as $t) {
          $s = strtolower($t['status'] ?? '');
          if ($s === 'completed' || $s === 'done') $completed++;
          else if ($s === 'in_progress' || $s === 'in progress') $inProgress++;
          else $uninitiated++;
        }
        $percent = $totalTasks > 0 ? round(($completed / $totalTasks) * 100) : 0;
  $dueDate = $project['due_date'] ?? null;
      ?>
      <div class="hive-summary">
        <div class="hive-stats">
          <span>Tasks: <b><?php echo $totalTasks; ?></b></span>
          <span>Completed: <b><?php echo $completed; ?></b></span>
          <span>In-progress: <b><?php echo $inProgress; ?></b></span>
        </div>
        <div class="hive-dates">
          <div>Project Due Date: <b><?php echo $dueDate ? htmlspecialchars(date('d/m/Y', strtotime($dueDate))) : '-'; ?></b></div>
        </div>
        <div class="hive-progress-bar">
          <div class="hive-progress" style="width:<?php echo $percent; ?>%;"></div>
        </div>
        <div class="hive-progress-labels">
          <span class="hive-progress-completed"><?php echo $percent; ?>% completed</span>
          <span class="hive-progress-days"><i class="fa fa-calendar"></i> <?php
            if ($dueDate) {
              $now = new DateTime();
              $d = new DateTime($dueDate);
              $diff = $d->diff($now)->format('%r%a');
              echo ($diff > 0 ? $diff . ' Days Left' : 'Due!');
            } else echo '-';
          ?></span>
        </div>
      </div>
      <section class="hive-section">
        <h2>In Progress</h2>
        <div class="hive-tasks-row">
          <?php foreach ($tasks as $t):
            $s = strtolower($t['status'] ?? '');
            if ($s === 'in_progress' || $s === 'in progress'):
          ?>
          <div class="hive-task-card yellowpale">
            <div class="hive-task-title"><?php echo htmlspecialchars($t['title']); ?></div>
            <div class="hive-task-meta">Priority: <b><?php echo htmlspecialchars($t['priority'] ?? '-'); ?></b></div>
            <div class="hive-task-meta">Deadline: <b><?php echo $t['due_date'] ? htmlspecialchars(date('d/m/Y', strtotime($t['due_date']))) : '-'; ?></b></div>
            <div class="hive-task-meta">Status: <b>In Progress</b></div>
          </div>
          <?php endif; endforeach; ?>
        </div>
      </section>
      <section class="hive-section">
        <h2>Completed</h2>
        <div class="hive-tasks-row">
          <?php foreach ($tasks as $t):
            $s = strtolower($t['status'] ?? '');
            if ($s === 'completed' || $s === 'done'):
          ?>
          <div class="hive-task-card yellow">
            <div class="hive-task-title"><?php echo htmlspecialchars($t['title']); ?></div>
            <div class="hive-task-meta">Priority: <b><?php echo htmlspecialchars($t['priority'] ?? '-'); ?></b></div>
            <div class="hive-task-meta">Deadline: <b><?php echo $t['due_date'] ? htmlspecialchars(date('d/m/Y', strtotime($t['due_date']))) : '-'; ?></b></div>
            <div class="hive-task-meta">Status: <b>Completed</b></div>
          </div>
          <?php endif; endforeach; ?>
        </div>
      </section>
      <section class="hive-section">
        <h2>Uninitiated</h2>
        <div class="hive-tasks-row">
          <?php foreach ($tasks as $t):
            $s = strtolower($t['status'] ?? '');
            if ($s !== 'completed' && $s !== 'done' && $s !== 'in_progress' && $s !== 'in progress'):
          ?>
          <div class="hive-task-card black">
            <div class="hive-task-title"><?php echo htmlspecialchars($t['title']); ?></div>
            <div class="hive-task-meta">Priority: <b><?php echo htmlspecialchars($t['priority'] ?? '-'); ?></b></div>
            <div class="hive-task-meta">Deadline: <b><?php echo $t['due_date'] ? htmlspecialchars(date('d/m/Y', strtotime($t['due_date']))) : '-'; ?></b></div>
            <div class="hive-task-meta">Status: <b><?php echo htmlspecialchars($t['status'] ?? '-'); ?></b></div>
          </div>
          <?php endif; endforeach; ?>
        </div>
      </section>
    </main>
  </div>
  <script id="project-data" type="application/json">
    <?php
      // Normalize project keys for the client-side script (camelCase fields expected)
      $projOut = null;
      if ($project) {
        $projOut = $project;
        // ensure camelCase fields exist for JS
        $projOut['dateCreated'] = $project['created_time'] ?? null;
        $projOut['dueDate'] = $project['due_date'] ?? null;
        // Only include image if the column/value exists
        $projOut['image'] = (isset($project['image']) && !empty($project['image'])) ? 'data:image/*;base64,'.base64_encode($project['image']) : null;
      }
      // Emit raw JSON inside the <script type="application/json"> so the client can parse it directly.
      // Do NOT HTML-escape the JSON here (json_attr used htmlspecialchars previously and broke parsing).
      echo json_encode(['project' => $projOut, 'tasks' => $tasks], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    ?>
  </script>
  <script src="personalhive.js"></script>
</body>
</html>
