<?php
require_once __DIR__ . '/../api/bootstrap.php';

ensure_mentor_module_schema($db);

$sql = "
  SELECT
    m.id,
    m.student_id,
    s.fio,
    c.id AS course_id,
    c.name AS course_name
  FROM mentors m
  JOIN students s ON s.id = m.student_id
  JOIN courses c ON c.id = m.course_id
  ORDER BY m.id DESC
";
$res = $db->query($sql);
json_response(true, 'Mentorlar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
