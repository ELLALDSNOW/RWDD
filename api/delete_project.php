<?php
session_start();
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo 'Unauthorized'; exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);
$project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
if ($project_id <= 0) { http_response_code(400); echo 'Bad request'; exit; }


$chk = mysqli_prepare($dbConn, "SELECT user_id, organization_id FROM project WHERE project_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'i', $project_id);
mysqli_stmt_execute($chk);
$res = mysqli_stmt_get_result($chk);
if (!$res || mysqli_num_rows($res) === 0) { mysqli_stmt_close($chk); http_response_code(404); echo 'Not found'; exit; }
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($chk);


$allowed = false;
if ((int)$row['user_id'] === $user_id) {
	$allowed = true;
} else if (!empty($row['organization_id'])) {
	$orgId = intval($row['organization_id']);
	$chk2 = mysqli_prepare($dbConn, "SELECT role_in_org FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
	if ($chk2) {
		mysqli_stmt_bind_param($chk2, 'ii', $orgId, $user_id);
		mysqli_stmt_execute($chk2);
		$r2 = mysqli_stmt_get_result($chk2);
		if ($r2 && mysqli_num_rows($r2) > 0) {
			$row2 = mysqli_fetch_assoc($r2);
			if (in_array($row2['role_in_org'], ['owner','admin'])) $allowed = true;
		}
		mysqli_stmt_close($chk2);
	}
}
if (!$allowed) { http_response_code(403); echo 'Forbidden'; exit; }


$del = mysqli_prepare($dbConn, "DELETE FROM project WHERE project_id = ?");
mysqli_stmt_bind_param($del, 'i', $project_id);
mysqli_stmt_execute($del);
mysqli_stmt_close($del);

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
	http_response_code(200);
	echo 'OK';
	exit;
} else {
	header('Location: ../organization.php');
	exit;
}