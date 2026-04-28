<?php
require_once __DIR__ . '/../api/bootstrap.php';

$res = $db->query('SELECT id, name, description, days, time, duration FROM courses ORDER BY id DESC');
json_response(true, 'Kurslar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
