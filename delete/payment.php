<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
$db = (new Database())->connect();

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    json_response(false, 'ID noto\'g\'ri');
}

$stmt = $db->prepare("DELETE FROM payments WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    @unlink(__DIR__ . '/../cache/' . md5('dashboard_stats') . '.cache');
    json_response(true, 'To\'lov o\'chirildi');
} else {
    json_response(false, 'Xatolik yuz berdi: ' . $db->error);
}
