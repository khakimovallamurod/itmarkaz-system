<?php
require_once __DIR__ . '/../api/bootstrap.php';

$studentId = (int) ($_POST['student_id'] ?? 0);
$courseId = (int) ($_POST['course_id'] ?? 0);
$roomId = (int) ($_POST['room_id'] ?? 0);

if ($studentId < 1 || $courseId < 1) {
    json_response(false, 'Talaba va kursni tanlang.');
}

$stmt = $db->prepare('
  INSERT INTO course_students (student_id, course_id, room_id)
  VALUES (?, ?, NULLIF(?, 0))
  ON DUPLICATE KEY UPDATE room_id = VALUES(room_id)
');
$stmt->bind_param('iii', $studentId, $courseId, $roomId);
$stmt->execute();

json_response(true, 'Kurs biriktirildi.');
