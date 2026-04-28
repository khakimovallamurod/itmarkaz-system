<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_POST['competition_id'] ?? 0);
$student_id = (int) ($_POST['student_id'] ?? 0);
$position = (int) ($_POST['position'] ?? 0);
if ($competition_id < 1 || $student_id < 1 || !in_array($position, [1, 2, 3], true)) json_response(false, 'Maydonlar xato');

$positionConflictStmt = $db->prepare('SELECT id FROM competition_results WHERE competition_id = ? AND position = ? AND student_id <> ? LIMIT 1');
$positionConflictStmt->bind_param('iii', $competition_id, $position, $student_id);
$positionConflictStmt->execute();
if ($positionConflictStmt->get_result()->fetch_assoc()) {
    json_response(false, 'Ushbu o\'rin allaqachon boshqa talaba uchun band.');
}

$stmt = $db->prepare('
  INSERT INTO competition_results (competition_id, student_id, position)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE position = VALUES(position)
');
$stmt->bind_param('iii', $competition_id, $student_id, $position);
$stmt->execute();
json_response(true, 'Natija saqlandi');
