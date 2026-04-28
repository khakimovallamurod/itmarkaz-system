<?php
require_once __DIR__ . '/../api/bootstrap.php';

$statusFilter = clean_input($_GET['status'] ?? '');
if ($statusFilter !== '') {
    $stmt = $db->prepare('
      SELECT s.id, s.fio
      FROM students s
      JOIN student_status ss ON ss.student_id = s.id
      JOIN statuses st ON st.id = ss.status_id
      WHERE st.name = ?
      ORDER BY s.fio ASC
    ');
    $stmt->bind_param('s', $statusFilter);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    json_response(true, 'Student options', ['items' => $items]);
}

$res = $db->query('SELECT id, fio FROM students ORDER BY fio ASC');
json_response(true, 'Student options', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
