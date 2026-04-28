<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    json_response(false, 'ID noto\'g\'ri.');
}

$stmt = $db->prepare('DELETE FROM statuses WHERE id = ?');
$stmt->bind_param('i', $id);
if (!$stmt->execute()) {
    json_response(false, 'Statusni o\'chirishda xatolik.');
}
json_response(true, 'Status o\'chirildi.');
