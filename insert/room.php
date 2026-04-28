<?php
require_once __DIR__ . '/../api/bootstrap.php';

$roomNumber = clean_input($_POST['room_number'] ?? '');
$capacity = (int) ($_POST['capacity'] ?? 0);
$computersCount = (int) ($_POST['computers_count'] ?? 0);

if ($roomNumber === '' || $capacity < 0 || $computersCount < 0) {
    json_response(false, 'Xona maydonlari noto\'g\'ri.');
}

$stmt = $db->prepare('INSERT INTO rooms (room_number, capacity, computers_count) VALUES (?, ?, ?)');
$stmt->bind_param('sii', $roomNumber, $capacity, $computersCount);
if (!$stmt->execute()) {
    json_response(false, 'Xona qo\'shishda xatolik yoki dublikat mavjud.');
}

json_response(true, 'Xona qo\'shildi.');
