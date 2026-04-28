<?php
require_once __DIR__ . '/../api/bootstrap.php';

$res = $db->query('SELECT id, room_number, capacity, computers_count FROM rooms ORDER BY room_number ASC');
json_response(true, 'Xonalar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
