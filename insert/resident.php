<?php
require_once __DIR__ . '/../api/bootstrap.php';

$studentId = (int) ($_POST['student_id'] ?? 0);
$roomId = (int) ($_POST['room_id'] ?? 0);
$computerNumber = clean_input($_POST['computer_number'] ?? '');

if ($studentId < 1) {
    json_response(false, 'Talaba tanlanmagan');
}

$stmt = $db->prepare('
  INSERT INTO residents (student_id, room_id, computer_number)
  VALUES (?, NULLIF(?, 0), ?)
  ON DUPLICATE KEY UPDATE room_id = VALUES(room_id), computer_number = VALUES(computer_number)
');
$stmt->bind_param('iis', $studentId, $roomId, $computerNumber);
$stmt->execute();
json_response(true, 'Rezident ma\'lumotlari saqlandi.');
