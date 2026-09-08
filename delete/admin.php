<?php
require_once __DIR__ . '/../api/bootstrap.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    json_response(false, 'Faqat Super Administrator adminni o\'chirishi mumkin.');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    json_response(false, 'ID noto\'g\'ri.');
}

if ($id === (int) $_SESSION['admin_id']) {
    json_response(false, 'O\'z hisobingizni o\'chira olmaysiz.');
}

$stmtSelect = $db->prepare('SELECT id, username, role FROM admins WHERE id = ? LIMIT 1');
$stmtSelect->bind_param('i', $id);
$stmtSelect->execute();
$admin = $stmtSelect->get_result()->fetch_assoc();
if (!$admin) {
    json_response(false, 'Admin topilmadi.');
}

$stmt = $db->prepare('DELETE FROM admins WHERE id = ?');
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    json_response(false, 'Adminni o\'chirishda xatolik yuz berdi.');
}

log_admin_activity($db, 'admin_delete', 'admins', "Admin o'chirildi: @{$admin['username']} (ID: #{$id})", $id);

json_response(true, 'Administrator o\'chirildi.');
