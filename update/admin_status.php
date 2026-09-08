<?php
require_once __DIR__ . '/../api/bootstrap.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    json_response(false, 'Faqat Super Administrator admin statusini o\'zgartirishi mumkin.');
}

$adminId = (int) ($_POST['admin_id'] ?? 0);
$status = clean_input($_POST['status'] ?? '');

if ($adminId < 1 || !in_array($status, ['active', 'blocked'], true)) {
    json_response(false, 'Noto\'g\'ri parametrlar.');
}

if ($adminId === (int) $_SESSION['admin_id']) {
    json_response(false, 'O\'z hisobingizni bloklay olmaysiz.');
}

$stmtSelect = $db->prepare('SELECT id, username, first_name, last_name, role FROM admins WHERE id = ? LIMIT 1');
$stmtSelect->bind_param('i', $adminId);
$stmtSelect->execute();
$admin = $stmtSelect->get_result()->fetch_assoc();
if (!$admin) {
    json_response(false, 'Admin topilmadi.');
}

$stmt = $db->prepare('UPDATE admins SET status = ? WHERE id = ?');
$stmt->bind_param('si', $status, $adminId);

if (!$stmt->execute()) {
    json_response(false, 'Statusni o\'zgartirishda xatolik yuz berdi.');
}

$statusUz = ($status === 'blocked') ? 'bloklandi' : 'faollashtirildi';
$actionType = ($status === 'blocked') ? 'admin_block' : 'admin_unblock';
log_admin_activity($db, $actionType, 'admins', "Admin @{$admin['username']} {$statusUz}", $adminId);

json_response(true, "Admin holati muvaffaqiyatli {$statusUz}.", ['status' => $status]);
