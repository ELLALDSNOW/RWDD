<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);

$body = json_decode(file_get_contents('php://input'), true);
if (!$body || !isset($body['todo_id']) || !isset($body['todo_data'])) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Invalid payload']); exit; }
$todo_id = intval($body['todo_id']);
$todo_data = json_encode($body['todo_data']);
$todo_name = isset($body['todo_name']) ? trim($body['todo_name']) : null;


$chk = mysqli_prepare($dbConn, "SELECT 1 FROM todo_user WHERE todo_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $todo_id, $user_id);
mysqli_stmt_execute($chk);
$cres = mysqli_stmt_get_result($chk);
mysqli_stmt_close($chk);
if (!$cres || mysqli_num_rows($cres) === 0) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Not found']); exit; }

$ok = false;
if ($todo_name !== null && $todo_name !== '') {

	$stmt = mysqli_prepare($dbConn, "UPDATE todo SET todo_name = ?, todo_data = ?, last_edited_date = NOW() WHERE todo_id = ? LIMIT 1");
	mysqli_stmt_bind_param($stmt, 'ssi', $todo_name, $todo_data, $todo_id);
	$ok = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
} else {
	$stmt = mysqli_prepare($dbConn, "UPDATE todo SET todo_data = ?, last_edited_date = NOW() WHERE todo_id = ? LIMIT 1");
	mysqli_stmt_bind_param($stmt, 'si', $todo_data, $todo_id);
	$ok = mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
}
mysqli_stmt_close($stmt);
if ($ok) echo json_encode(['ok'=>true]); else echo json_encode(['ok'=>false,'error'=>mysqli_error($dbConn)]);
exit;
