<?php
session_start();
require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_id'])) {
    json_response(false, 'Avtorizatsiya talab etiladi.');
}

$db = (new Database())->connect();
ensure_system_schema($db);
ensure_mentor_module_schema($db);
