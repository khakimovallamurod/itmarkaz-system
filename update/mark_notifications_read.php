<?php
require_once __DIR__ . '/../api/bootstrap.php';

if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
    json_response(false, 'Ruxsat yo\'q.');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id > 0) {
    $stmt = $db->prepare("UPDATE admin_activity_logs SET is_read = 1 WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $unreadCount = get_unread_notifications_count($db);
    json_response(true, 'Bildirishnoma o\'qilgan deb belgilandi.', ['id' => $id, 'unread_count' => $unreadCount]);
} else {
    $db->query("UPDATE admin_activity_logs SET is_read = 1 WHERE is_read = 0");
    json_response(true, 'Barcha bildirishnomalar o\'qilgan deb belgilandi.', ['unread_count' => 0]);
}
