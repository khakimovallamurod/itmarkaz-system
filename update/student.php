<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$fio = clean_input($_POST['fio'] ?? '');
$yonalishId = (int) ($_POST['yonalish_id'] ?? 0);
$guruh = clean_input($_POST['guruh'] ?? '');
$kirgan_yili = (int) ($_POST['kirgan_yili'] ?? 0);
$telefon = normalize_uz_phone($_POST['telefon'] ?? '');
$telegram_chat_id = clean_input($_POST['telegram_chat_id'] ?? '');
$statusIds = $_POST['status'] ?? ($_POST['status_ids'] ?? []);
$statusIds = array_map('intval', (array) $statusIds);
$statusIds = array_values(array_filter($statusIds, static fn ($value) => $value > 0));

if ($id < 1 || $fio === '' || $yonalishId < 1 || $guruh === '' || $kirgan_yili < 2000 || !$statusIds) {
    json_response(false, 'Majburiy maydonlar to\'ldirilmagan yoki ID xato.');
}
if ($telefon === null) {
    json_response(false, 'Telefon formati noto\'g\'ri. Masalan: +998 90 123 45 67');
}

$db->begin_transaction();
try {
    $stmt = $db->prepare('UPDATE students SET fio = ?, yonalish_id = ?, guruh = ?, kirgan_yili = ?, telefon = ?, telegram_chat_id = ? WHERE id = ?');
    $stmt->bind_param('sisissi', $fio, $yonalishId, $guruh, $kirgan_yili, $telefon, $telegram_chat_id, $id);
    $stmt->execute();

    $deleteStmt = $db->prepare('DELETE FROM student_status WHERE student_id = ?');
    $deleteStmt->bind_param('i', $id);
    $deleteStmt->execute();

    $insertStatusStmt = $db->prepare('INSERT IGNORE INTO student_status (student_id, status_id) VALUES (?, ?)');
    foreach ($statusIds as $statusId) {
        $insertStatusStmt->bind_param('ii', $id, $statusId);
        $insertStatusStmt->execute();
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    json_response(false, 'Talabani yangilashda xatolik yuz berdi.');
}

json_response(true, 'Talaba yangilandi.');
