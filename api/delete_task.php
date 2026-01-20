<?php
session_start();
require_once __DIR__ . '/../conn.php';
if (!isset($_SESSION['user_id'])) { header('Location: ../login.html'); exit; }
$user_id = intval($_SESSION['user_id']);
$task_id = intval($_POST['task_id'] ?? 0);
$project_id = intval($_POST['project_id'] ?? 0);
if ($task_id <= 0 || $project_id <= 0) { header('Location: ../organization.php'); exit; }



$chk = mysqli_prepare($dbConn, "SELECT project_id FROM project WHERE project_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'i', $project_id);
mysqli_stmt_execute($chk);
$res = mysqli_stmt_get_result($chk);
mysqli_stmt_close($chk);
if (!$res || mysqli_num_rows($res) === 0) { header('Location: ../organization.php'); exit; }


$del = mysqli_prepare($dbConn, "DELETE FROM tasks WHERE task_id = ? AND project_id = ?");
mysqli_stmt_bind_param($del, 'ii', $task_id, $project_id);
mysqli_stmt_execute($del);
mysqli_stmt_close($del);


if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
	header('Content-Type: application/json');
	echo json_encode(['ok' => true, 'task_id' => $task_id]);
	exit;
}

header('Location: ../organization.php');
exit;