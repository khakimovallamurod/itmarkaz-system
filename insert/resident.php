<?php
require_once __DIR__ . '/../api/bootstrap.php';

$studentId = (int) ($_POST['student_id'] ?? 0);
$roomId = (int) ($_POST['room_id'] ?? 0);
$computerNumber = clean_input($_POST['computer_number'] ?? '');

if ($studentId < 1) {
    json_response(false, 'Talaba tanlanmagan');
}

$statusStmt = $db->prepare("
  SELECT 1
  FROM student_status ss
  JOIN statuses st ON st.id = ss.status_id
  WHERE ss.student_id = ? AND LOWER(TRIM(st.name)) = LOWER('Rezident')
  LIMIT 1
");
$statusStmt->bind_param('i', $studentId);
$statusStmt->execute();
if (!$statusStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Talabada Rezident status yo\'q.');
}

$stmt = $db->prepare('
  INSERT INTO residents (student_id, room_id, computer_number)
  VALUES (?, NULLIF(?, 0), ?)
  ON DUPLICATE KEY UPDATE room_id = VALUES(room_id), computer_number = VALUES(computer_number)
');
$stmt->bind_param('iis', $studentId, $roomId, $computerNumber);
$stmt->execute();
json_response(true, 'Rezident ma\'lumotlari saqlandi.');
