<?php
require_once __DIR__ . '/../api/bootstrap.php';

$fio = clean_input($_POST['fio'] ?? '');
$yonalishId = (int) ($_POST['yonalish_id'] ?? 0);
$guruh = clean_input($_POST['guruh'] ?? '');
$kirgan_yili = (int) ($_POST['kirgan_yili'] ?? 0);
$telefon = normalize_uz_phone($_POST['telefon'] ?? '');
$telegram_chat_id = clean_input($_POST['telegram_chat_id'] ?? '');
$statusIds = $_POST['status'] ?? ($_POST['status_ids'] ?? []);
$statusIds = array_map('intval', (array) $statusIds);
$statusIds = array_values(array_filter($statusIds, static fn ($id) => $id > 0));

if ($fio === '' || $yonalishId < 1 || $guruh === '' || $kirgan_yili < 2000) {
    json_response(false, 'Majburiy maydonlar to\'ldirilmagan yoki noto\'g\'ri.');
}
if ($telefon === null) {
    json_response(false, 'Telefon formati noto\'g\'ri. Masalan: +998 90 123 45 67');
}

if (!$statusIds) {
    $defaultStatusStmt = $db->prepare("SELECT id FROM statuses WHERE LOWER(TRIM(name)) = LOWER('Talaba') LIMIT 1");
    $defaultStatusStmt->execute();
    $defaultStatus = $defaultStatusStmt->get_result()->fetch_assoc();
    if (!$defaultStatus) {
        json_response(false, 'Default status (Talaba) topilmadi.');
    }
    $statusIds = [(int) $defaultStatus['id']];
}

$db->begin_transaction();
try {
    $stmt = $db->prepare('INSERT INTO students (fio, yonalish_id, guruh, kirgan_yili, telefon, telegram_chat_id) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sisiss', $fio, $yonalishId, $guruh, $kirgan_yili, $telefon, $telegram_chat_id);
    $stmt->execute();
    $studentId = (int) $db->insert_id;

    $pivotStmt = $db->prepare('INSERT IGNORE INTO student_status (student_id, status_id) VALUES (?, ?)');
    foreach ($statusIds as $statusId) {
        $pivotStmt->bind_param('ii', $studentId, $statusId);
        $pivotStmt->execute();
    }

    $db->commit();
} catch (Throwable $e) {
    $db->rollback();
    json_response(false, 'Talaba qo\'shishda xatolik yuz berdi.');
}

json_response(true, 'Talaba qo\'shildi.');
