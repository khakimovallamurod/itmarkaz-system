<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$name = clean_input($_POST['name'] ?? '');
if ($id < 1 || $name === '') {
    json_response(false, 'ID yoki nom noto\'g\'ri.');
}

$stmt = $db->prepare('UPDATE statuses SET name = ? WHERE id = ?');
$stmt->bind_param('si', $name, $id);
if (!$stmt->execute()) {
    json_response(false, 'Statusni yangilashda xatolik.');
}

json_response(true, 'Status yangilandi.');
