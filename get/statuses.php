<?php
require_once __DIR__ . '/../api/bootstrap.php';

$res = $db->query('SELECT id, name FROM statuses ORDER BY name ASC');
json_response(true, 'Statuslar olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
