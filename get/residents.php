<?php
require_once __DIR__ . '/../api/bootstrap.php';
$search = '%' . clean_input($_GET['search'] ?? '') . '%';
$status = clean_input($_GET['status'] ?? '');
$statusSql = '';
if ($status === 'assigned') {
    $statusSql = ' AND r.id IS NOT NULL ';
} elseif ($status === 'unassigned') {
    $statusSql = ' AND r.id IS NULL ';
}
$sql = "
  SELECT
    DISTINCT
    r.id,
    s.id AS student_id,
    s.fio,
    rm.id AS room_id,
    rm.room_number,
    r.computer_number
  FROM students s
  JOIN student_status ss ON ss.student_id = s.id
  JOIN statuses st ON st.id = ss.status_id AND LOWER(TRIM(st.name)) = LOWER('Rezident')
  LEFT JOIN residents r ON r.student_id = s.id
  LEFT JOIN rooms rm ON rm.id = r.room_id
  WHERE s.fio LIKE ?
  {$statusSql}
  ORDER BY s.fio ASC
";
$stmt = $db->prepare($sql);
$stmt->bind_param('s', $search);
$stmt->execute();
json_response(true, 'Rezidentlar olindi', ['items' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
