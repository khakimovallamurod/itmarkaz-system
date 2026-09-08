<?php
require_once __DIR__ . '/../api/bootstrap.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    json_response(false, 'Faqat Super Administrator yangi admin yaratishi mumkin.');
}

$firstName = clean_input($_POST['first_name'] ?? '');
$lastName = clean_input($_POST['last_name'] ?? '');
$phone = clean_input($_POST['phone'] ?? '');
$username = clean_input($_POST['username'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$role = clean_input($_POST['role'] ?? 'admin');
$status = clean_input($_POST['status'] ?? 'active');
$studentId = !empty($_POST['student_id']) ? (int) $_POST['student_id'] : null;

if ($firstName === '' || $username === '' || $password === '' || $phone === '') {
    json_response(false, 'Barcha majburiy maydonlarni to\'ldiring.');
}

if (!in_array($role, ['admin', 'superadmin'], true)) {
    $role = 'admin';
}
if (!in_array($status, ['active', 'blocked'], true)) {
    $status = 'active';
}

// Check username uniqueness
$stmtCheck = $db->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
$stmtCheck->bind_param('s', $username);
$stmtCheck->execute();
if ($stmtCheck->get_result()->fetch_assoc()) {
    json_response(false, "Ushbu login (@{$username}) band. Boshqa login tanlang.");
}

$passwordHash = md5($password);

$stmt = $db->prepare('INSERT INTO admins (username, first_name, last_name, phone, role, status, student_id, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->bind_param('ssssssis', $username, $firstName, $lastName, $phone, $role, $status, $studentId, $passwordHash);

if (!$stmt->execute()) {
    json_response(false, 'Admin yaratishda xatolik yuz berdi.');
}

$newAdminId = (int) $db->insert_id;

$desc = "Yangi admin yaratildi: @{$username} ({$firstName} {$lastName})";
if ($studentId) {
    $desc .= " [Rezident talabadan biriktirildi]";
}
log_admin_activity($db, 'admin_create', 'admins', $desc, $newAdminId);

json_response(true, 'Yangi administrator muvaffaqiyatli yaratildi.', [
    'id' => $newAdminId,
    'username' => $username
]);
