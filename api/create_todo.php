<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);

$name = trim($_POST['name'] ?? 'New To-dos');
if ($name === '') $name = 'New To-dos';


$ins = mysqli_prepare($dbConn, "INSERT INTO todo (todo_name, list_type, todo_data, created_time, last_edited_date) VALUES (?, 'personal', ?, NOW(), NOW())");
$jsonEmpty = json_encode([]);
mysqli_stmt_bind_param($ins, 'ss', $name, $jsonEmpty);
mysqli_stmt_execute($ins);
$newId = mysqli_insert_id($dbConn);
mysqli_stmt_close($ins);


$ins2 = mysqli_prepare($dbConn, "INSERT INTO todo_user (todo_id, user_id) VALUES (?, ?)");
mysqli_stmt_bind_param($ins2, 'ii', $newId, $user_id);
mysqli_stmt_execute($ins2);
mysqli_stmt_close($ins2);

echo json_encode(['ok'=>true,'todo_id'=>$newId]);
exit;
