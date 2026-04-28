<?php
require_once __DIR__ . '/../api/bootstrap.php';

$studentId = (int) ($_POST['student_id'] ?? 0);
$courseId = (int) ($_POST['course_id'] ?? 0);
$roomId = (int) ($_POST['room_id'] ?? 0);

if ($studentId < 1 || $courseId < 1) {
    json_response(false, 'Talaba va kursni tanlang.');
}

$statusStmt = $db->prepare("
  SELECT 1
  FROM student_status ss
  JOIN statuses st ON st.id = ss.status_id
  WHERE ss.student_id = ? AND LOWER(TRIM(st.name)) = LOWER('Kurs o''quvchi')
  LIMIT 1
");
$statusStmt->bind_param('i', $studentId);
$statusStmt->execute();
if (!$statusStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Talabada Kurs o\'quvchi status yo\'q.');
}

$stmt = $db->prepare('
  INSERT INTO course_students (student_id, course_id, room_id)
  VALUES (?, ?, NULLIF(?, 0))
  ON DUPLICATE KEY UPDATE room_id = VALUES(room_id)
');
$stmt->bind_param('iii', $studentId, $courseId, $roomId);
$stmt->execute();

json_response(true, 'Kurs biriktirildi.');
