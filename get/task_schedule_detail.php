<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) json_response(false, 'ID xato');

$res = $db->query("SELECT * FROM task_schedule WHERE id = $id");
if ($task = $res->fetch_assoc()) {
    $task['target_groups'] = json_decode($task['target_groups'] ?? '[]', true);
    json_response(true, 'Topshiriq topildi', $task);
} else {
    json_response(false, 'Topshiriq topilmadi');
}
