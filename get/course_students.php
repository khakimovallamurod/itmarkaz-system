<?php
require_once __DIR__ . '/../api/bootstrap.php';

$search = '%' . clean_input($_GET['search'] ?? '') . '%';
$status = clean_input($_GET['status'] ?? '');
$statusSql = '';
if (in_array($status, ['active', 'completed'], true)) {
    $statusSql = ' AND cs.status = ? ';
}

$sql = "
  SELECT
    DISTINCT
    s.id AS student_id,
    s.fio,
    cs.id,
    cs.course_id,
    c.name AS course_name,
    cs.room_id,
    r.room_number,
    COALESCE(cs.status, 'active') AS status
  FROM students s
  JOIN student_status ss ON ss.student_id = s.id
  JOIN statuses st ON st.id = ss.status_id AND LOWER(TRIM(st.name)) = LOWER('Kurs o''quvchi')
  LEFT JOIN course_students cs ON cs.student_id = s.id
  LEFT JOIN courses c ON c.id = cs.course_id
  LEFT JOIN rooms r ON r.id = cs.room_id
  WHERE s.fio LIKE ?
  {$statusSql}
  ORDER BY s.fio ASC
";
$stmt = $db->prepare($sql);
if ($statusSql !== '') {
    $stmt->bind_param('ss', $search, $status);
} else {
    $stmt->bind_param('s', $search);
}
$stmt->execute();
json_response(true, 'Kurs o\'quvchilar olindi.', ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
