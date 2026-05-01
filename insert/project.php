<?php
require_once __DIR__ . '/../api/bootstrap.php';

$projectName = clean_input($_POST['project_name'] ?? '');
$status = clean_input($_POST['status'] ?? 'boshlanish');
$studentIds = $_POST['student_ids'] ?? [];
$studentIds = array_values(array_unique(array_filter(array_map('intval', (array) $studentIds), static fn ($id) => $id > 0)));

if ($projectName === '') {
    json_response(false, 'Loyiha nomi majburiy');
}
if (!in_array($status, ['boshlanish', 'qurish', 'testlash', 'tugallash'], true)) {
    json_response(false, 'Loyiha statusi noto\'g\'ri.');
}
if (!$studentIds) {
    json_response(false, 'Kamida bitta talaba tanlang');
}

$db->begin_transaction();
try {
    $stmt = $db->prepare('INSERT INTO projects (project_name, status) VALUES (?, ?)');
    $stmt->bind_param('ss', $projectName, $status);
    $stmt->execute();
    $projectId = (int) $db->insert_id;

    $memberStmt = $db->prepare('INSERT IGNORE INTO project_members (project_id, student_id) VALUES (?, ?)');
    foreach ($studentIds as $studentId) {
        $memberStmt->bind_param('ii', $projectId, $studentId);
        $memberStmt->execute();
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    json_response(false, 'Loyiha yaratishda xatolik yuz berdi');
}

json_response(true, 'Loyiha yaratildi');

