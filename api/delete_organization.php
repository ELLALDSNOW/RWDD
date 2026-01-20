<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'not_authenticated']);
    exit;
}
require_once __DIR__ . '/../conn.php';

$user_id = intval($_SESSION['user_id']);
$org_id = isset($_POST['org_id']) ? intval($_POST['org_id']) : 0;
if ($org_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_org_id']);
    exit;
}


$check = mysqli_prepare($dbConn, "SELECT role_in_org FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($check, 'ii', $org_id, $user_id);
mysqli_stmt_execute($check);
$res = mysqli_stmt_get_result($check);
$role = null;
if ($res) {
    $row = mysqli_fetch_assoc($res);
    if ($row) $role = $row['role_in_org'];
} else {
    mysqli_stmt_store_result($check);
    if (mysqli_stmt_num_rows($check) > 0) {
        mysqli_stmt_bind_result($check, $role);
        mysqli_stmt_fetch($check);
    }
}
mysqli_stmt_close($check);

if ($role !== 'owner') {
    http_response_code(403);
    echo json_encode(['error' => 'not_owner']);
    exit;
}


$del = mysqli_prepare($dbConn, "DELETE FROM organization WHERE organization_id = ?");
if (!$del) {
    http_response_code(500);
    echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
    exit;
}
mysqli_stmt_bind_param($del, 'i', $org_id);
if (!mysqli_stmt_execute($del)) {
    http_response_code(500);
    echo json_encode(['error' => 'execute_failed', 'msg' => mysqli_stmt_error($del)]);
    mysqli_stmt_close($del);
    exit;
}
mysqli_stmt_close($del);
echo json_encode(['ok' => true]);
exit;