<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit;
}
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);


$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$project_id = isset($_POST['project_id']) && $_POST['project_id'] !== '' ? intval($_POST['project_id']) : null;
$org_id = isset($_POST['organization_id']) ? intval($_POST['organization_id']) : 0;
$priority = $_POST['priority'] ?? 'medium';
$due_date = isset($_POST['due_date']) && $_POST['due_date'] !== '' ? $_POST['due_date'] : null;


$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($name === '') {
    if ($isAjax) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Project name is required']); exit; }
    die("<script>alert('Project name is required'); window.history.back();</script>");
}


$image = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image = file_get_contents($_FILES['image']['tmp_name']);
}


if ($org_id > 0) {
    $chk = mysqli_prepare($dbConn, "SELECT role_in_org FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($chk, 'ii', $org_id, $user_id);
    mysqli_stmt_execute($chk);
    $res = mysqli_stmt_get_result($chk);
    if (!$res || mysqli_num_rows($res) === 0) {
        mysqli_stmt_close($chk);
        if ($isAjax) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'You are not a member of this organization']); exit; }
        die("<script>alert('You are not a member of this organization'); window.location.href='../personal.php';</script>");
    }
    mysqli_stmt_close($chk);
}

if ($project_id === null) {
 
    $cols = "name, description, created_time, updated_time, priority, visibility, status, user_id";
    $vals = "?, ?, NOW(), NOW(), ?, 'private', 'Active', ?";
    $types = "sssi";
    $params = [$name, $description, $priority, $user_id];
    
   
    if ($org_id > 0) {
        $cols .= ", organization_id";
        $vals .= ", ?";
        $types .= "i";
        $params[] = $org_id;
    }


    if ($due_date !== null) {
        $cols .= ", due_date";
        $vals .= ", ?";
        $types .= "s";
        $params[] = $due_date;
    }

 
    if ($image !== null) {
        $cols .= ", image";
        $vals .= ", ?";
        $types .= "s";
        $params[] = $image;
    }

    $ins = mysqli_prepare($dbConn, "INSERT INTO project ($cols) VALUES ($vals)");
    if (!$ins) {
        if ($isAjax) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Failed to create project: ' . mysqli_error($dbConn)]); exit; }
        die("<script>alert('Failed to create project: " . mysqli_error($dbConn) . "'); window.history.back();</script>");
    }

    mysqli_stmt_bind_param($ins, $types, ...$params);
    mysqli_stmt_execute($ins);
    $newId = mysqli_insert_id($dbConn);
    mysqli_stmt_close($ins);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true,'project_id'=>$newId]);
        exit;
    }
    if ($org_id > 0) {
        header("Location: ../organization.php?org_id=$org_id");
    } else {
        header("Location: ../personalhive.php?project_id=$newId");
    }
    exit;

} else {

    $chk = mysqli_prepare($dbConn, 
        "SELECT user_id, organization_id FROM project WHERE project_id = ? LIMIT 1");
    mysqli_stmt_bind_param($chk, 'i', $project_id);
    mysqli_stmt_execute($chk);
    $res = mysqli_stmt_get_result($chk);
    
    if (!$res || mysqli_num_rows($res) === 0) {
        if ($isAjax) { http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Project not found']); exit; }
        die("<script>alert('Project not found'); window.history.back();</script>");
    }
    
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($chk);
    

    $canEdit = false;
    if ((int)$row['user_id'] === $user_id) {
        $canEdit = true;
    } else if ($row['organization_id'] > 0) {
        $chk = mysqli_prepare($dbConn, 
            "SELECT 1 FROM organization_user WHERE organization_id = ? AND user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($chk, 'ii', $row['organization_id'], $user_id);
        mysqli_stmt_execute($chk);
        $res2 = mysqli_stmt_get_result($chk);
        $canEdit = ($res2 && mysqli_num_rows($res2) > 0);
        mysqli_stmt_close($chk);
    }
    
    if (!$canEdit) {
        if ($isAjax) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'You cannot edit this project']); exit; }
        die("<script>alert('You cannot edit this project'); window.history.back();</script>");
    }


    $sets = "name = ?, description = ?, priority = ?, updated_time = NOW()";
    $types = "sss";
    $params = [$name, $description, $priority];

    if ($due_date !== null) {
        $sets .= ", due_date = ?";
        $types .= "s";
        $params[] = $due_date;
    }
    if ($image !== null) {
        $sets .= ", image = ?";
        $types .= "s";
        $params[] = $image;
    }


    $types .= "i";
    $params[] = $project_id;

    $upd = mysqli_prepare($dbConn, "UPDATE project SET $sets WHERE project_id = ?");
    if (!$upd) {
        if ($isAjax) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Update failed: ' . mysqli_error($dbConn)]); exit; }
        die("<script>alert('Update failed: " . mysqli_error($dbConn) . "'); window.history.back();</script>");
    }
    
    mysqli_stmt_bind_param($upd, $types, ...$params);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['ok'=>true,'project_id'=>$project_id]);
        exit;
    }
    if ($org_id > 0) {
        header("Location: ../organization.php?org_id=$org_id");
    } else {
        header("Location: ../personalhive.php?project_id=$project_id");
    }
    exit;
}
