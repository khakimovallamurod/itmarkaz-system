<?php
require_once __DIR__ . '/../api/bootstrap.php';

$name = clean_input($_POST['name'] ?? '');
$description = clean_input($_POST['description'] ?? '');
$days = $_POST['days'] ?? [];
$time = clean_input($_POST['time'] ?? '');
$duration = clean_input($_POST['duration'] ?? '');

if ($name === '' || $time === '' || $duration === '' || !is_array($days) || count($days) === 0) {
    json_response(false, 'Majburiy maydonlarni to\'ldiring.');
}

$dayCodes = array_values(array_unique(array_map('clean_input', $days)));
$placeholders = implode(',', array_fill(0, count($dayCodes), '?'));
$types = str_repeat('s', count($dayCodes));
$dayCheck = $db->prepare("SELECT code FROM week_days WHERE code IN ($placeholders)");
$dayCheck->bind_param($types, ...$dayCodes);
$dayCheck->execute();
$validDayCodes = array_column($dayCheck->get_result()->fetch_all(MYSQLI_ASSOC), 'code');
if (count($validDayCodes) !== count($dayCodes)) {
    json_response(false, 'Tanlangan kunlar noto\'g\'ri.');
}

$daysJson = json_encode($dayCodes, JSON_UNESCAPED_UNICODE);
$stmt = $db->prepare('INSERT INTO courses (name, description, days, time, duration) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $name, $description, $daysJson, $time, $duration);
$stmt->execute();

json_response(true, 'Kurs qo\'shildi.');
