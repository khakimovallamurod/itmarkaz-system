<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_GET['competition_id'] ?? 0);
if ($competition_id < 1) json_response(false, 'competition_id talab qilinadi');
$stmt = $db->prepare('SELECT cp.id, cp.student_id, s.fio FROM competition_participants cp JOIN students s ON s.id = cp.student_id WHERE cp.competition_id = ? ORDER BY s.fio ASC');
$stmt->bind_param('i', $competition_id);
$stmt->execute();
json_response(true, 'Ishtirokchilar', ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
