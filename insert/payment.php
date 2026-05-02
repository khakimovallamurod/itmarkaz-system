<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
$db = (new Database())->connect();

$projectId = (int) ($_POST['project_id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
$amount = (float) ($_POST['amount'] ?? 0);
$paymentTypeId = (int) ($_POST['payment_type_id'] ?? 0);

if ($projectId <= 0 || $studentId <= 0 || $amount <= 0 || $paymentTypeId <= 0) {
    json_response(false, 'Barcha maydonlarni to\'ldiring va summa 0 dan katta bo\'lishi kerak');
}

$stmt = $db->prepare("INSERT INTO payments (project_id, student_id, amount, payment_type_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param('iidi', $projectId, $studentId, $amount, $paymentTypeId);

if ($stmt->execute()) {
    // Clear dashboard stats cache because a new payment might affect it (though we don't show payments in main stats yet, better safe)
    @unlink(__DIR__ . '/../cache/' . md5('dashboard_stats') . '.cache');
    json_response(true, 'To\'lov muvaffaqiyatli saqlandi');
} else {
    json_response(false, 'Xatolik yuz berdi: ' . $db->error);
}
