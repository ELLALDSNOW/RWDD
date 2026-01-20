<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Unauthorized']); exit; }
require_once __DIR__ . '/../conn.php';
$user_id = intval($_SESSION['user_id']);

$stmt = mysqli_prepare($dbConn, "SELECT t.todo_id, t.todo_name, t.todo_data, t.created_time, t.last_edited_date
  FROM todo t
  INNER JOIN todo_user tu ON tu.todo_id = t.todo_id
  WHERE tu.user_id = ?
  ORDER BY t.last_edited_date DESC");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$out = ['ok'=>true,'todos'=>[]];
if ($res) {
  while ($row = mysqli_fetch_assoc($res)) {

    $data = [];
    if (!empty($row['todo_data'])) {
      $decoded = json_decode($row['todo_data'], true);
      if (is_array($decoded)) $data = $decoded;
    }
    $out['todos'][] = [
      'todo_id' => (int)$row['todo_id'],
      'name' => $row['todo_name'],
      'data' => $data,
      'created_time' => $row['created_time'],
      'last_edited' => $row['last_edited_date']
    ];
  }
}
mysqli_stmt_close($stmt);
echo json_encode($out);
exit;
