<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$date = clean_input($_POST['date'] ?? '');

if ($id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    json_response(false, 'Noto\'g\'ri ma\'lumot.');
}

$stmt = $db->prepare('UPDATE task_schedule SET deadline = ? WHERE id = ?');
$stmt->bind_param('si', $date, $id);
$stmt->execute();

json_response(true, 'Sana yangilandi.');
