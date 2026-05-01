<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_GET['competition_id'] ?? 0);
if ($competition_id < 1) json_response(false, 'competition_id talab qilinadi');
$stmt = $db->prepare("
  SELECT cr.id, cr.student_id, s.fio, cr.position, cr.cash_amount, cr.award_type_id, crt.code AS award_code, crt.name AS award_name
  FROM competition_results cr
  JOIN students s ON s.id = cr.student_id
  LEFT JOIN competition_result_types crt ON crt.id = cr.award_type_id
  WHERE cr.competition_id = ?
  ORDER BY CASE WHEN cr.position IS NULL THEN 1 ELSE 0 END ASC, cr.position ASC, s.fio ASC
");
$stmt->bind_param('i', $competition_id);
$stmt->execute();
json_response(true, 'Natijalar', ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
