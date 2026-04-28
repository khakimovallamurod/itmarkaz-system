<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_POST['competition_id'] ?? 0);
$student_id = (int) ($_POST['student_id'] ?? 0);
if ($competition_id < 1 || $student_id < 1) json_response(false, 'Noto\'g\'ri qiymat.');
$stmt = $db->prepare('INSERT IGNORE INTO competition_participants (competition_id, student_id) VALUES (?, ?)');
$stmt->bind_param('ii', $competition_id, $student_id);
$stmt->execute();
if ($stmt->affected_rows < 1) {
    json_response(false, 'Ushbu talaba allaqachon ishtirokchi sifatida qo\'shilgan.');
}
json_response(true, 'Ishtirokchi qo\'shildi.');
