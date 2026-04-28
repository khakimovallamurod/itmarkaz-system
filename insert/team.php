<?php
require_once __DIR__ . '/../api/bootstrap.php';

$teamName = clean_input($_POST['team_name'] ?? '');
$studentIds = $_POST['student_ids'] ?? [];
$studentIds = array_values(array_unique(array_filter(array_map('intval', (array) $studentIds), static fn ($id) => $id > 0)));

if ($teamName === '') {
    json_response(false, 'Jamoa nomi majburiy');
}
if (!$studentIds) {
    json_response(false, 'Kamida bitta talaba tanlang');
}

$db->begin_transaction();
try {
    $stmt = $db->prepare('INSERT INTO teams (team_name) VALUES (?)');
    $stmt->bind_param('s', $teamName);
    $stmt->execute();
    $teamId = (int) $db->insert_id;

    if ($studentIds) {
        $memberStmt = $db->prepare('INSERT IGNORE INTO team_members (team_id, student_id) VALUES (?, ?)');
        foreach ($studentIds as $studentId) {
            $memberStmt->bind_param('ii', $teamId, $studentId);
            $memberStmt->execute();
        }
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    json_response(false, 'Jamoa yaratishda xatolik yuz berdi');
}

json_response(true, 'Jamoa yaratildi');
