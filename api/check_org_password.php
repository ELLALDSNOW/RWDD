<?php

header('Content-Type: application/json; charset=utf-8');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'error'=>'not_authenticated']);
    exit;
}
require_once __DIR__ . '/../conn.php';

$user_id = intval($_SESSION['user_id']);
$org_id = isset($_POST['org_id']) ? intval($_POST['org_id']) : 0;
$entered = isset($_POST['password']) ? trim($_POST['password']) : '';

if ($org_id <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'invalid_org']);
    exit;
}


$stmt = mysqli_prepare($dbConn, "SELECT org_password FROM organization WHERE organization_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $org_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if (!$res || mysqli_num_rows($res) === 0) {
    echo json_encode(['ok'=>false,'error'=>'not_found']);
    exit;
}
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

$stored = $row['org_password'];


$ok = false;
if ($stored === null || $stored === '') {
    $ok = true;
} else {

    if ($entered !== '' && $entered === $stored) $ok = true;
}

if (!$ok) {
    echo json_encode(['ok'=>false,'error'=>'incorrect']);
    exit;
}


$chk = mysqli_prepare($dbConn, "SELECT 1 FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, 'ii', $org_id, $user_id);
mysqli_stmt_execute($chk);
$cres = mysqli_stmt_get_result($chk);
$exists = false;
if ($cres && mysqli_num_rows($cres) > 0) $exists = true;
mysqli_stmt_close($chk);

if (!$exists) {
    $ins = mysqli_prepare($dbConn, "INSERT INTO organization_user (organization_id, user_id, role_in_org, created_at) VALUES (?, ?, 'member', NOW())");
    if ($ins) {
        mysqli_stmt_bind_param($ins, 'ii', $org_id, $user_id);
        mysqli_stmt_execute($ins);
        mysqli_stmt_close($ins);
    }
}

if (!isset($_SESSION['joined_orgs'])) $_SESSION['joined_orgs'] = [];
$_SESSION['joined_orgs'][$org_id] = time();

echo json_encode(['ok'=>true, 'org_id'=>$org_id]);
exit;

?>
