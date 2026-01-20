<?php
// Returns JSON list of organizations for current logged in user
header('Content-Type: application/json; charset=utf-8');
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'not_authenticated']);
    exit;
}
require_once __DIR__ . '/../conn.php'; // uses $dbConn

$user_id = intval($_SESSION['user_id']);

$sql = "
  SELECT o.organization_id, o.name, o.description, o.priority, o.created_at, o.updated_at, o.created_by, ou.role_in_org
  FROM organization o
  INNER JOIN organization_user ou ON o.organization_id = ou.organization_id
  WHERE ou.user_id = ?
  ORDER BY o.created_at DESC
";
$stmt = mysqli_prepare($dbConn, $sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'prepare_failed', 'msg' => mysqli_error($dbConn)]);
    exit;
}
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
if ($res === false) {
    // fallback if get_result not available
    mysqli_stmt_store_result($stmt);
    $meta = mysqli_stmt_result_metadata($stmt);
    $fields = [];
    $row = [];
    while ($field = mysqli_fetch_field($meta)) {
        $fields[] = &$row[$field->name];
    }
    call_user_func_array('mysqli_stmt_bind_result', array_merge([$stmt], $fields));
    $rows = [];
    while (mysqli_stmt_fetch($stmt)) {
        $r = [];
        foreach ($row as $k => $v) $r[$k] = $v;
        $rows[] = $r;
    }
    echo json_encode(['data' => $rows]);
    mysqli_stmt_close($stmt);
    exit;
}
$rows = [];
while ($r = mysqli_fetch_assoc($res)) {
    $rows[] = $r;
}
mysqli_stmt_close($stmt);
echo json_encode(['data' => $rows]);
exit;