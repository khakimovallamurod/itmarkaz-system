<?php
require_once __DIR__ . '/../api/bootstrap.php';
$id = (int) ($_POST['id'] ?? 0);
$position = (int) ($_POST['position'] ?? 0);
if ($id < 1 || !in_array($position, [1, 2, 3], true)) json_response(false, 'Xato parametr');

$competitionStmt = $db->prepare('SELECT competition_id, student_id FROM competition_results WHERE id = ? LIMIT 1');
$competitionStmt->bind_param('i', $id);
$competitionStmt->execute();
$row = $competitionStmt->get_result()->fetch_assoc();
if (!$row) {
    json_response(false, 'Natija topilmadi.');
}

$positionConflictStmt = $db->prepare('SELECT id FROM competition_results WHERE competition_id = ? AND position = ? AND id <> ? LIMIT 1');
$positionConflictStmt->bind_param('iii', $row['competition_id'], $position, $id);
$positionConflictStmt->execute();
if ($positionConflictStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Ushbu o\'rin allaqachon band.');
}

$stmt = $db->prepare('UPDATE competition_results SET position=? WHERE id=?');
$stmt->bind_param('ii', $position, $id);
$stmt->execute();
json_response(true, 'Natija yangilandi');
