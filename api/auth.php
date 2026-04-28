<?php
session_start();
require_once __DIR__ . '/../config/config.php';

$db = (new Database())->connect();
$action = $_GET['action'] ?? '';

if ($action === 'login') {
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        json_response(false, 'Login yoki parol bo\'sh bo\'lmasligi kerak.');
    }

    $stmt = $db->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        json_response(false, 'Login yoki parol noto\'g\'ri.');
    }

    $passwordHash = $admin['password_hash'] ?? '';
    $isValidMd5 = hash_equals($passwordHash, md5($password));
    $isValidLegacyHash = password_verify($password, $passwordHash);

    if (!$isValidMd5 && !$isValidLegacyHash) {
        json_response(false, 'Login yoki parol noto\'g\'ri.');
    }

    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    json_response(true, 'Muvaffaqiyatli kirildi.', ['redirect' => 'admin/index.php']);
}

if ($action === 'logout') {
    session_destroy();
    json_response(true, 'Tizimdan chiqildi.', ['redirect' => '../index.php']);
}

json_response(false, 'Noto\'g\'ri so\'rov.');
