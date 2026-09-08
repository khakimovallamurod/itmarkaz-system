<?php
require_once __DIR__ . '/../api/bootstrap.php';

$adminId = (int) ($_SESSION['admin_id'] ?? 0);
if ($adminId < 1) {
    json_response(false, 'Avtorizatsiya talab etiladi.');
}

$firstName = clean_input($_POST['first_name'] ?? '');
$lastName = clean_input($_POST['last_name'] ?? '');
$phone = clean_input($_POST['phone'] ?? '');
$username = clean_input($_POST['username'] ?? '');
$currentPassword = (string) ($_POST['current_password'] ?? '');
$newPassword = (string) ($_POST['new_password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');

if ($username === '') {
    json_response(false, 'Login (foydalanuvchi nomi) bo\'sh bo\'lmasligi kerak.');
}

// Check username uniqueness
$stmtCheck = $db->prepare('SELECT id FROM admins WHERE username = ? AND id != ? LIMIT 1');
$stmtCheck->bind_param('si', $username, $adminId);
$stmtCheck->execute();
if ($stmtCheck->get_result()->fetch_assoc()) {
    json_response(false, 'Bu login band. Iltimos, boshqa login tanlang.');
}

// Fetch current admin data
$stmtAdmin = $db->prepare('SELECT id, username, password_hash FROM admins WHERE id = ? LIMIT 1');
$stmtAdmin->bind_param('i', $adminId);
$stmtAdmin->execute();
$currentAdmin = $stmtAdmin->get_result()->fetch_assoc();
if (!$currentAdmin) {
    json_response(false, 'Admin ma\'lumotlari topilmadi.');
}

$updatePassword = false;
$newPasswordHash = '';

if ($newPassword !== '' || $currentPassword !== '') {
    if ($currentPassword === '') {
        json_response(false, 'Parolni o\'zgartirish uchun joriy parolingizni kiriting.');
    }
    if ($newPassword === '') {
        json_response(false, 'Yangi parolni kiriting.');
    }
    if (mb_strlen($newPassword) < 4) {
        json_response(false, 'Yangi parol kamida 4 ta belgidan iborat bo\'lishi kerak.');
    }
    if ($newPassword !== $confirmPassword) {
        json_response(false, 'Yangi parol va tasdiqlovchi parol bir-biriga mos kelmadi.');
    }

    $existingHash = $currentAdmin['password_hash'] ?? '';
    $isValid = hash_equals($existingHash, md5($currentPassword)) || password_verify($currentPassword, $existingHash);
    if (!$isValid) {
        json_response(false, 'Joriy parol noto\'g\'ri kiritildi.');
    }

    $updatePassword = true;
    $newPasswordHash = md5($newPassword);
}

if ($updatePassword) {
    $stmtUpdate = $db->prepare('UPDATE admins SET username = ?, first_name = ?, last_name = ?, phone = ?, password_hash = ? WHERE id = ?');
    $stmtUpdate->bind_param('sssssi', $username, $firstName, $lastName, $phone, $newPasswordHash, $adminId);
} else {
    $stmtUpdate = $db->prepare('UPDATE admins SET username = ?, first_name = ?, last_name = ?, phone = ? WHERE id = ?');
    $stmtUpdate->bind_param('ssssi', $username, $firstName, $lastName, $phone, $adminId);
}

if (!$stmtUpdate->execute()) {
    json_response(false, 'Ma\'lumotlarni saqlashda xatolik yuz berdi.');
}

$_SESSION['admin_username'] = $username;
$_SESSION['admin_first_name'] = $firstName;
$_SESSION['admin_last_name'] = $lastName;

json_response(true, 'Profil ma\'lumotlari muvaffaqiyatli yangilandi.', [
    'username' => $username,
    'first_name' => $firstName,
    'last_name' => $lastName,
    'phone' => $phone,
]);
