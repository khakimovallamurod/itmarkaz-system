<?php
require_once __DIR__ . '/../api/bootstrap.php';
$type = clean_input($_GET['type'] ?? '');
$where = '';
if (in_array($type, ['daily', 'weekly'], true)) {
    $where = ' WHERE type = ? ';
}

$sql = "SELECT * FROM schedule {$where} ORDER BY date DESC, id DESC";
$stmt = $db->prepare($sql);
if ($where !== '') {
    $stmt->bind_param('s', $type);
}
$stmt->execute();
json_response(true, 'Jadvallar olindi', ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
