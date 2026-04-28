<?php
require_once __DIR__ . '/../api/bootstrap.php';

$sql = "
  SELECT
    DISTINCT
    s.id,
    s.fio,
    s.telefon,
    s.telegram_chat_id
  FROM students s
  JOIN student_status ss ON ss.student_id = s.id
  JOIN statuses st ON st.id = ss.status_id
  WHERE LOWER(TRIM(st.name)) = LOWER('Rezident')
  ORDER BY s.fio ASC
";

$res = $db->query($sql);
json_response(true, 'Rezident talabalar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
