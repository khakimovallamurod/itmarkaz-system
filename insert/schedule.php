<?php
require_once __DIR__ . '/../api/bootstrap.php';
$type = clean_input($_POST['type'] ?? '');
$title = clean_input($_POST['title'] ?? '');
$date = clean_input($_POST['date'] ?? '');
if (!in_array($type, ['weekly', 'daily'], true) || $title === '' || $date === '') json_response(false, 'Maydonlar xato');
$stmt = $db->prepare('INSERT INTO schedule (type, title, date) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $type, $title, $date);
$stmt->execute();
json_response(true, 'Jadval qo\'shildi');
