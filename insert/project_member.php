<?php
require_once __DIR__ . '/../api/bootstrap.php';

$projectId = (int) ($_POST['project_id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
if ($projectId < 1 || $studentId < 1) {
    json_response(false, 'Qiymatlar noto\'g\'ri');
}

$stmt = $db->prepare('INSERT IGNORE INTO project_members (project_id, student_id) VALUES (?, ?)');
$stmt->bind_param('ii', $projectId, $studentId);
$stmt->execute();

if ($stmt->affected_rows < 1) {
    json_response(false, 'Talaba allaqachon loyihadagi a\'zo');
}

json_response(true, 'A\'zo qo\'shildi');

