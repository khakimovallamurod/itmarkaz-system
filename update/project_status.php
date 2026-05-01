<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$status = clean_input($_POST['status'] ?? '');
if ($id < 1 || !in_array($status, ['boshlanish', 'qurish', 'testlash', 'tugallash'], true)) {
    json_response(false, 'Qiymatlar noto\'g\'ri');
}

$stmt = $db->prepare('UPDATE projects SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $id);
$stmt->execute();

json_response(true, 'Loyiha holati yangilandi');

