<?php
require_once __DIR__ . '/../api/bootstrap.php';

$teamId = (int) ($_POST['team_id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
if ($teamId < 1 || $studentId < 1) {
    json_response(false, 'Qiymatlar noto\'g\'ri');
}

$stmt = $db->prepare('INSERT IGNORE INTO team_members (team_id, student_id) VALUES (?, ?)');
$stmt->bind_param('ii', $teamId, $studentId);
$stmt->execute();

if ($stmt->affected_rows < 1) {
    json_response(false, 'Talaba allaqachon jamoadagi a\'zo');
}

json_response(true, 'A\'zo qo\'shildi');
