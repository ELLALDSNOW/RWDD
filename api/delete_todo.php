<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);

$todo_id = isset($_POST['todo_id']) ? intval($_POST['todo_id']) : 0;
if ($todo_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing todo_id']); exit; }


$chk = mysqli_prepare($dbConn, "SELECT 1 FROM todo_user WHERE todo_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $todo_id, $user_id);
mysqli_stmt_execute($chk);
$cres = mysqli_stmt_get_result($chk);
mysqli_stmt_close($chk);
if (!$cres || mysqli_num_rows($cres) === 0) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }


$delmap = mysqli_prepare($dbConn, "DELETE FROM todo_user WHERE todo_id = ? AND user_id = ?");
mysqli_stmt_bind_param($delmap, 'ii', $todo_id, $user_id);
mysqli_stmt_execute($delmap);
mysqli_stmt_close($delmap);

$chk2 = mysqli_prepare($dbConn, "SELECT 1 FROM todo_user WHERE todo_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk2, 'i', $todo_id);
mysqli_stmt_execute($chk2);
$r2 = mysqli_stmt_get_result($chk2);
mysqli_stmt_close($chk2);
if ($r2 && mysqli_num_rows($r2) === 0) {
  $del = mysqli_prepare($dbConn, "DELETE FROM todo WHERE todo_id = ?");
  mysqli_stmt_bind_param($del, 'i', $todo_id);
  mysqli_stmt_execute($del);
  mysqli_stmt_close($del);
}
echo json_encode(['ok'=>true]);
exit;
