<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) json_response(false, 'ID noto\'g\'ri.');

$db->begin_transaction();
try {
    $res = $db->query("SELECT student_id FROM course_students WHERE id = $id");
    if ($row = $res->fetch_assoc()) {
        $studentId = $row['student_id'];
        $db->query("DELETE FROM course_students WHERE id = $id");
        $db->query("DELETE FROM student_status WHERE student_id = $studentId AND status_id = (SELECT id FROM statuses WHERE name = 'Kurs o\'quvchi' LIMIT 1)");
        $db->query("INSERT IGNORE INTO student_status (student_id, status_id) VALUES ($studentId, (SELECT id FROM statuses WHERE name = 'Talaba' LIMIT 1))");
    }
    $db->commit();
    json_response(true, 'Kursdan o\'chirildi va statusi yangilandi');
} catch (Exception $e) {
    $db->rollback();
    json_response(false, 'Xatolik: ' . $e->getMessage());
}
