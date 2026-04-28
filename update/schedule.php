<?php
require_once __DIR__ . '/../api/bootstrap.php';
$id = (int) ($_POST['id'] ?? 0);
$type = clean_input($_POST['type'] ?? '');
$title = clean_input($_POST['title'] ?? '');
$date = clean_input($_POST['date'] ?? '');
if ($id < 1 || !in_array($type, ['weekly', 'daily'], true) || $title === '' || $date === '') json_response(false, 'Maydonlar xato');
$stmt = $db->prepare('UPDATE schedule SET type=?, title=?, date=? WHERE id=?');
$stmt->bind_param('sssi', $type, $title, $date, $id);
$stmt->execute();
json_response(true, 'Jadval yangilandi');
