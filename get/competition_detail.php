<?php
require_once __DIR__ . '/../api/bootstrap.php';

$competitionId = (int) ($_GET['id'] ?? 0);
if ($competitionId < 1) {
    json_response(false, 'competition id xato');
}

$stmt = $db->prepare('SELECT * FROM competitions WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $competitionId);
$stmt->execute();
$competition = $stmt->get_result()->fetch_assoc();
if (!$competition) {
    json_response(false, 'Tanlov topilmadi');
}

$participantStmt = $db->prepare('
  SELECT cp.id, cp.student_id, s.fio
  FROM competition_participants cp
  JOIN students s ON s.id = cp.student_id
  WHERE cp.competition_id = ?
  ORDER BY s.fio ASC
');
$participantStmt->bind_param('i', $competitionId);
$participantStmt->execute();
$participants = $participantStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$resultStmt = $db->prepare('
  SELECT cr.id, cr.student_id, cr.position, cr.cash_amount, cr.award_type_id, crt.code AS award_code, crt.name AS award_name, s.fio
  FROM competition_results cr
  JOIN students s ON s.id = cr.student_id
  LEFT JOIN competition_result_types crt ON crt.id = cr.award_type_id
  WHERE cr.competition_id = ?
  ORDER BY
    CASE WHEN cr.position IS NULL THEN 1 ELSE 0 END ASC,
    cr.position ASC,
    s.fio ASC
');
$resultStmt->bind_param('i', $competitionId);
$resultStmt->execute();
$results = $resultStmt->get_result()->fetch_all(MYSQLI_ASSOC);

json_response(true, 'Tanlov tafsiloti', [
    'competition' => $competition,
    'participants' => $participants,
    'results' => $results,
]);
