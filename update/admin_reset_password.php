<?php
require_once __DIR__ . '/../api/bootstrap.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    json_response(false, 'Faqat Super Administrator parolni yangilashi mumkin.');
}

$adminId = (int) ($_POST['admin_id'] ?? 0);
$newPassword = (string) ($_POST['new_password'] ?? '');

if ($adminId < 1 || mb_strlen($newPassword) < 4) {
    json_response(false, 'Parol kamida 4 ta belgidan iborat bo\'lishi kerak.');
}

$stmtSelect = $db->prepare('SELECT id, username FROM admins WHERE id = ? LIMIT 1');
$stmtSelect->bind_param('i', $adminId);
$stmtSelect->execute();
$admin = $stmtSelect->get_result()->fetch_assoc();
if (!$admin) {
    json_response(false, 'Admin topilmadi.');
}

$passwordHash = md5($newPassword);
$stmt = $db->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
$stmt->bind_param('si', $passwordHash, $adminId);

if (!$stmt->execute()) {
    json_response(false, 'Parolni yangilashda xatolik yuz berdi.');
}

log_admin_activity($db, 'admin_reset_pwd', 'admins', "Admin @{$admin['username']} paroli Super Admin tomonidan yangilandi", $adminId);

json_response(true, "Admin (@{$admin['username']}) paroli muvaffaqiyatli yangilandi.");
