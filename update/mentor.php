<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
$courseId = (int) ($_POST['course_id'] ?? 0);

if ($id < 1 || $studentId < 1 || $courseId < 1) {
    json_response(false, 'Majburiy maydonlar to\'ldirilmagan.');
}

ensure_mentor_module_schema($db);

$studentStmt = $db->prepare('SELECT id FROM students WHERE id = ? LIMIT 1');
$studentStmt->bind_param('i', $studentId);
$studentStmt->execute();
$student = $studentStmt->get_result()->fetch_assoc();
if (!$student) {
    json_response(false, 'Talaba topilmadi.');
}

$courseStmt = $db->prepare('SELECT id FROM courses WHERE id = ? LIMIT 1');
$courseStmt->bind_param('i', $courseId);
$courseStmt->execute();
if (!$courseStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Kurs topilmadi.');
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
    json_response(false, 'Faqat Rezident statusidagi talabani mentor qilib saqlash mumkin.');
}

$dupStmt = $db->prepare('SELECT id FROM mentors WHERE student_id = ? AND course_id = ? AND id <> ? LIMIT 1');
$dupStmt->bind_param('iii', $studentId, $courseId, $id);
$dupStmt->execute();
if ($dupStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Bu mentor-kurs birikmasi allaqachon mavjud.');
}

try {
    $mentorStmt = $db->prepare('UPDATE mentors SET student_id = ?, course_id = ? WHERE id = ?');
    $mentorStmt->bind_param('iii', $studentId, $courseId, $id);
    $mentorStmt->execute();
} catch (Throwable $e) {
    json_response(false, 'Mentorni yangilashda xatolik yuz berdi.');
}

json_response(true, 'Mentor yangilandi.');
