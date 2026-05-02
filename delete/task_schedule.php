<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) json_response(false, 'ID xato');

$res = $db->query("SELECT file_path FROM task_schedule WHERE id = $id");
if ($row = $res->fetch_assoc()) {
    $filePath = $row['file_path'];
    if ($filePath && file_exists(__DIR__ . '/../' . $filePath)) {
        @unlink(__DIR__ . '/../' . $filePath);
    }
}

$stmt = $db->prepare('DELETE FROM task_schedule WHERE id=?');
$stmt->bind_param('i', $id);
$stmt->execute();

json_response(true, 'Topshiriq o\'chirildi');
