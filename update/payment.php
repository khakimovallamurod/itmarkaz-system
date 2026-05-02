<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$projectId = (int) ($_POST['project_id'] ?? 0);
$studentId = (int) ($_POST['student_id'] ?? 0);
$amount = (float) str_replace(' ', '', $_POST['amount'] ?? '0');
$paymentTypeId = (int) ($_POST['payment_type_id'] ?? 0);

if ($id < 1 || $projectId < 1 || $studentId < 1 || $amount <= 0 || $paymentTypeId < 1) {
    json_response(false, 'Ma\'lumotlar to\'liq emas yoki xato.');
}

$stmt = $db->prepare("UPDATE payments SET project_id = ?, student_id = ?, amount = ?, payment_type_id = ? WHERE id = ?");
$stmt->bind_param('iidii', $projectId, $studentId, $amount, $paymentTypeId, $id);

if ($stmt->execute()) {
    json_response(true, 'To\'lov muvaffaqiyatli yangilandi.');
} else {
    json_response(false, 'Xatolik yuz berdi: ' . $db->error);
}
