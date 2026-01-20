<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo 'Unauthorized'; exit; }
require_once __DIR__ . '/../conn.php';

$task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
if ($task_id <= 0) { http_response_code(400); echo 'Bad request'; exit; }

$fields = [];
$types = '';
$vals = [];
if (isset($_POST['status'])) { $fields[] = 'status = ?'; $types .= 's'; $vals[] = $_POST['status']; }
if (isset($_POST['priority'])) { $fields[] = 'priority = ?'; $types .= 's'; $vals[] = $_POST['priority']; }
if (isset($_POST['due_date'])) { $fields[] = 'due_date = ?'; $types .= 's'; $vals[] = $_POST['due_date']; }

if (empty($fields)) { http_response_code(200); echo json_encode(['ok' => true, 'changed' => false]); exit; }

$sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE task_id = ?";
$stmt = mysqli_prepare($dbConn, $sql);
if (!$stmt) { http_response_code(500); echo json_encode(['ok' => false, 'error' => 'DB prepare failed']); exit; }


$bindTypes = $types . 'i';
$bindValues = array_merge($vals, [$task_id]);
$bindParams = array_merge([$bindTypes], $bindValues);
$refs = [];
foreach ($bindParams as $key => $value) $refs[$key] = &$bindParams[$key];
call_user_func_array([$stmt, 'bind_param'], $refs);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
http_response_code(200); echo json_encode(['ok' => true, 'task_id' => $task_id]); exit;
