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

    $stmt = $db->prepare('SELECT id, username, first_name, last_name, role, status, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if (!$admin) {
        json_response(false, 'Login yoki parol noto\'g\'ri.');
    }

    if (($admin['status'] ?? 'active') === 'blocked') {
        json_response(false, 'Ushbu hisob bloklangan. Tizimga kirish uchun Super administratorga murojaat qiling.');
    }

    $passwordHash = $admin['password_hash'] ?? '';
    $isValidMd5 = hash_equals($passwordHash, md5($password));
    $isValidLegacyHash = password_verify($password, $passwordHash);

    if (!$isValidMd5 && !$isValidLegacyHash) {
        json_response(false, 'Login yoki parol noto\'g\'ri.');
    }

    $adminRole = $admin['role'] ?? 'admin';
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $adminRole;
    $_SESSION['admin_first_name'] = $admin['first_name'] ?? '';
    $_SESSION['admin_last_name'] = $admin['last_name'] ?? '';

    $redirectUrl = ($adminRole === 'superadmin') ? 'superadmin/index.php' : 'admin/index.php';

    log_admin_activity($db, 'login', 'auth', "Tizimga kirdi (@{$admin['username']})", (int) $admin['id'], (int) $admin['id']);

    json_response(true, 'Muvaffaqiyatli kirildi.', ['redirect' => $redirectUrl]);
}

if ($action === 'logout') {
    session_destroy();
    json_response(true, 'Tizimdan chiqildi.', ['redirect' => '../index.php']);
}

json_response(false, 'Noto\'g\'ri so\'rov.');
