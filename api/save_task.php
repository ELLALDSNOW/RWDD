<?php
session_start();
require_once __DIR__ . '/../conn.php';
if (!isset($_SESSION['user_id'])) {
 
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    exit;
  }
  header('Location: ../login.html');
  exit;
}
$user_id = intval($_SESSION['user_id']);

$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
$task_id = isset($_POST['task_id']) && $_POST['task_id'] !== '' ? intval($_POST['task_id']) : null;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority = $_POST['priority'] ?? 'medium';
$status = $_POST['status'] ?? 'in_progress';
$due_date = isset($_POST['due_date']) && $_POST['due_date'] !== '' ? $_POST['due_date'] : null;

if ($title === '' || $project_id <= 0) {
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Bad request']);
    exit;
  }
  header('Location: ../organization.php');
  exit;
}


$chk = mysqli_prepare($dbConn, "SELECT project_id FROM project WHERE project_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'i', $project_id);
mysqli_stmt_execute($chk);
$res = mysqli_stmt_get_result($chk);
mysqli_stmt_close($chk);
if (!$res || mysqli_num_rows($res) === 0) {
  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Project not found']);
    exit;
  }
  die("<script>alert('Project not found'); window.location.href='../personal.php';</script>");
}

if ($task_id === null) {
  $ins = mysqli_prepare($dbConn, "INSERT INTO tasks (title, description, status, priority, start_date, due_date, created_at, updated_at, project_id) VALUES (?, ?, ?, ?, NULL, ?, NOW(), NOW(), ?)");
  if ($ins) {
  
    mysqli_stmt_bind_param($ins, 'sssssi', $title, $description, $status, $priority, $due_date, $project_id);
    mysqli_stmt_execute($ins);
    $newId = mysqli_insert_id($dbConn);
    mysqli_stmt_close($ins);
  } else {
 
    $safe_title = mysqli_real_escape_string($dbConn, $title);
    $safe_description = mysqli_real_escape_string($dbConn, $description);
    $safe_status = mysqli_real_escape_string($dbConn, $status);
    $safe_priority = mysqli_real_escape_string($dbConn, $priority);
    $safe_due = $due_date ? "'" . mysqli_real_escape_string($dbConn, $due_date) . "'" : 'NULL';
    $q = "INSERT INTO tasks (title, description, status, priority, start_date, due_date, created_at, updated_at, project_id) VALUES ('{$safe_title}','{$safe_description}','{$safe_status}','{$safe_priority}',NULL,{$safe_due},NOW(),NOW(),{$project_id})";
    mysqli_query($dbConn, $q);
    $newId = mysqli_insert_id($dbConn);
  }

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['ok' => true, 'task_id' => (int)$newId]);
    exit;
  }
} else {
  $upd = mysqli_prepare($dbConn, "UPDATE tasks SET title = ?, description = ?, status = ?, priority = ?, due_date = ?, updated_at = NOW() WHERE task_id = ? AND project_id = ?");
  if ($upd) {

    mysqli_stmt_bind_param($upd, 'sssssii', $title, $description, $status, $priority, $due_date, $task_id, $project_id);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
  } else {

    $safe_title = mysqli_real_escape_string($dbConn, $title);
    $safe_description = mysqli_real_escape_string($dbConn, $description);
    $safe_status = mysqli_real_escape_string($dbConn, $status);
    $safe_priority = mysqli_real_escape_string($dbConn, $priority);
    $safe_due = $due_date ? "'" . mysqli_real_escape_string($dbConn, $due_date) . "'" : 'NULL';
    $q = "UPDATE tasks SET title='{$safe_title}', description='{$safe_description}', status='{$safe_status}', priority='{$safe_priority}', due_date={$safe_due}, updated_at=NOW() WHERE task_id=".intval($task_id)." AND project_id=".intval($project_id);
    mysqli_query($dbConn, $q);
  }

  if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    echo json_encode(['ok' => true, 'task_id' => (int)$task_id]);
    exit;
  }
}

header('Location: ../organization.php');
exit;