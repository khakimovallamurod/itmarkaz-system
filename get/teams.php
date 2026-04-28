<?php
require_once __DIR__ . '/../api/bootstrap.php';

$teamsRes = $db->query('SELECT id, team_name, created_at FROM teams ORDER BY id DESC');
$teams = $teamsRes->fetch_all(MYSQLI_ASSOC);

$teamStmt = $db->prepare('
  SELECT tm.id, tm.team_id, tm.student_id, s.fio
  FROM team_members tm
  JOIN students s ON s.id = tm.student_id
  WHERE tm.team_id = ?
  ORDER BY s.fio ASC
');

foreach ($teams as &$team) {
    $teamId = (int) $team['id'];
    $teamStmt->bind_param('i', $teamId);
    $teamStmt->execute();
    $team['members'] = $teamStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
unset($team);

json_response(true, 'Jamoalar olindi', ['items' => $teams]);
