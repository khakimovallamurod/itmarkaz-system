<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$roomNumber = clean_input($_POST['room_number'] ?? '');
$capacity = (int) ($_POST['capacity'] ?? 0);
$computersCount = (int) ($_POST['computers_count'] ?? 0);

if ($id < 1 || $roomNumber === '' || $capacity < 0 || $computersCount < 0) {
    json_response(false, 'Xona maydonlari noto\'g\'ri.');
}

$stmt = $db->prepare('UPDATE rooms SET room_number = ?, capacity = ?, computers_count = ? WHERE id = ?');
$stmt->bind_param('siii', $roomNumber, $capacity, $computersCount, $id);
if (!$stmt->execute()) {
    json_response(false, 'Xonani yangilashda xatolik.');
}

json_response(true, 'Xona yangilandi.');
