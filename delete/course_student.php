<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    json_response(false, 'ID noto\'g\'ri.');
}

$stmt = $db->prepare('DELETE FROM course_students WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
json_response(true, 'Kurs biriktirish o\'chirildi.');
