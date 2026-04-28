<?php
require_once __DIR__ . '/../api/bootstrap.php';
ensure_mentor_module_schema($db);

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    json_response(false, 'ID noto\'g\'ri.');
}

$stmt = $db->prepare('DELETE FROM mentors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
json_response(true, 'Mentor o\'chirildi.');
