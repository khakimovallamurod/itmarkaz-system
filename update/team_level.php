<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$level = clean_input($_POST['level'] ?? '');
if ($id < 1 || !in_array($level, ['junior', 'middle', 'senior'], true)) {
    json_response(false, 'Qiymatlar noto\'g\'ri');
}

$stmt = $db->prepare('UPDATE teams SET level = ? WHERE id = ?');
$stmt->bind_param('si', $level, $id);
$stmt->execute();

json_response(true, 'Jamoa darajasi yangilandi');

