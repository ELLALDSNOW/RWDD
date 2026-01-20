<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);
$todo_id = isset($_GET['todo_id']) ? intval($_GET['todo_id']) : 0;
if ($todo_id <= 0) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Missing todo_id']); exit; }

 
$chk = mysqli_prepare($dbConn, "SELECT 1 FROM todo_user WHERE todo_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $todo_id, $user_id);
mysqli_stmt_execute($chk);
$cres = mysqli_stmt_get_result($chk);
mysqli_stmt_close($chk);
if (!$cres || mysqli_num_rows($cres) === 0) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

$stmt = mysqli_prepare($dbConn, "SELECT todo_id, todo_name, todo_data, created_time, last_edited_date FROM todo WHERE todo_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $todo_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$out = ['ok'=>true,'todo'=>null];
if ($res && $row = mysqli_fetch_assoc($res)) {
  $data = [];
  if (!empty($row['todo_data'])) {
    $d = json_decode($row['todo_data'], true);
    if (is_array($d)) $data = $d;
  }
  $out['todo'] = ['todo_id'=>(int)$row['todo_id'],'name'=>$row['todo_name'],'data'=>$data,'created_time'=>$row['created_time'],'last_edited'=>$row['last_edited_date']];
}
mysqli_stmt_close($stmt);
echo json_encode($out);
exit;
