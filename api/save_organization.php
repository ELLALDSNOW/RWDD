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
 $name = trim($_POST['name'] ?? '');
 $description = trim($_POST['description'] ?? '');
 $priority = $_POST['priority'] ?? 'Medium';

 $org_id = null;
 if (isset($_POST['org_id']) && $_POST['org_id'] !== '') $org_id = intval($_POST['org_id']);
 else if (isset($_POST['organization_id']) && $_POST['organization_id'] !== '') $org_id = intval($_POST['organization_id']);
 

 $org_password_plain = trim($_POST['org_password'] ?? '');
 $has_password = $org_password_plain !== '';


if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'name_required']);
    exit;
}
$allowed_priorities = ['Heavy','Medium','Light'];
if (!in_array($priority, $allowed_priorities, true)) {
    $priority = 'Medium';
}

if ($org_id === null) {

    if ($has_password) {
        $ins = mysqli_prepare($dbConn, "INSERT INTO organization (name, description, priority, org_password, created_at, updated_at, created_by) VALUES (?, ?, ?, ?, NOW(), NOW(), ?)");
        if (!$ins) {
            http_response_code(500);
            echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
            exit;
        }
        mysqli_stmt_bind_param($ins, 'ssssi', $name, $description, $priority, $org_password_plain, $user_id);
    } else {
        $ins = mysqli_prepare($dbConn, "INSERT INTO organization (name, description, priority, created_at, updated_at, created_by) VALUES (?, ?, ?, NOW(), NOW(), ?)");
        if (!$ins) {
            http_response_code(500);
            echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
            exit;
        }
        mysqli_stmt_bind_param($ins, 'sssi', $name, $description, $priority, $user_id);
    }
    if (!mysqli_stmt_execute($ins)) {
        http_response_code(500);
        echo json_encode(['error' => 'execute_failed', 'msg' => mysqli_stmt_error($ins)]);
        mysqli_stmt_close($ins);
        exit;
    }
    $new_org_id = mysqli_insert_id($dbConn);
    mysqli_stmt_close($ins);

  
    $ins2 = mysqli_prepare($dbConn, "INSERT INTO organization_user (organization_id, user_id, role_in_org, created_at) VALUES (?, ?, 'owner', NOW())");
    if ($ins2) {
        mysqli_stmt_bind_param($ins2, 'ii', $new_org_id, $user_id);
        mysqli_stmt_execute($ins2);
        mysqli_stmt_close($ins2);
    }

    echo json_encode(['ok' => true, 'org_id' => $new_org_id]);
    exit;
} else {

    $check = mysqli_prepare($dbConn, "SELECT role_in_org FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
    if (!$check) {
        http_response_code(500);
        echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
        exit;
    }
    mysqli_stmt_bind_param($check, 'ii', $org_id, $user_id);
    mysqli_stmt_execute($check);
    $res = mysqli_stmt_get_result($check);
    if ($res === false) {
        mysqli_stmt_store_result($check);
        if (mysqli_stmt_num_rows($check) === 0) {
            mysqli_stmt_close($check);
            http_response_code(403);
            echo json_encode(['error' => 'not_member']);
            exit;
        }
  
    } else {
        $r = mysqli_fetch_assoc($res);
        if (!$r) {
            mysqli_stmt_close($check);
            http_response_code(403);
            echo json_encode(['error' => 'not_member']);
            exit;
        }
    }
    mysqli_stmt_close($check);


    if ($has_password) {
        $upd = mysqli_prepare($dbConn, "UPDATE organization SET name = ?, description = ?, priority = ?, org_password = ?, updated_at = NOW() WHERE organization_id = ?");
        if (!$upd) {
            http_response_code(500);
            echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
            exit;
        }
        mysqli_stmt_bind_param($upd, 'ssssi', $name, $description, $priority, $org_password_plain, $org_id);
    } else {
        $upd = mysqli_prepare($dbConn, "UPDATE organization SET name = ?, description = ?, priority = ?, updated_at = NOW() WHERE organization_id = ?");
        if (!$upd) {
            http_response_code(500);
            echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
            exit;
        }
        mysqli_stmt_bind_param($upd, 'sssi', $name, $description, $priority, $org_id);
    }
    if (!mysqli_stmt_execute($upd)) {
        http_response_code(500);
        echo json_encode(['error' => 'execute_failed', 'msg' => mysqli_stmt_error($upd)]);
        mysqli_stmt_close($upd);
        exit;
    }
    mysqli_stmt_close($upd);
    echo json_encode(['ok' => true, 'org_id' => $org_id]);
    exit;
}