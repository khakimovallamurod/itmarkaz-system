<?php
require_once __DIR__ . '/../api/bootstrap.php';

$projectsRes = $db->query('SELECT id, project_name, status, created_at FROM projects ORDER BY id DESC');
$projects = $projectsRes ? $projectsRes->fetch_all(MYSQLI_ASSOC) : [];

$memberStmt = $db->prepare('
  SELECT pm.id, pm.project_id, pm.student_id, s.fio
  FROM project_members pm
  JOIN students s ON s.id = pm.student_id
  WHERE pm.project_id = ?
  ORDER BY s.fio ASC
');

foreach ($projects as &$project) {
    $projectId = (int) $project['id'];
    $memberStmt->bind_param('i', $projectId);
    $memberStmt->execute();
    $project['members'] = $memberStmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
unset($project);

json_response(true, 'Loyihalar olindi', ['items' => $projects]);

