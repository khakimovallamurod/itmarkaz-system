<?php
require_once __DIR__ . '/../api/bootstrap.php';

$res = $db->query('SELECT id, code, name FROM week_days ORDER BY id ASC');
json_response(true, 'Hafta kunlari olindi.', ['items' => $res->fetch_all(MYSQLI_ASSOC)]);
