<?php
require_once __DIR__ . '/../api/bootstrap.php';
$res = $db->query("
  SELECT
    c.*,
    (SELECT COUNT(*) FROM competition_participants cp WHERE cp.competition_id = c.id) AS participant_count,
    (SELECT COUNT(*) FROM competition_results cr WHERE cr.competition_id = c.id) AS result_count
  FROM competitions c
  ORDER BY c.competition_date DESC, c.id DESC
");
json_response(true, 'Tanlovlar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
