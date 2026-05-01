<?php
require_once __DIR__ . '/../api/bootstrap.php';
$id = (int) ($_POST['id'] ?? 0);
$awardTypeId = (int) ($_POST['award_type_id'] ?? 0);
$positionRaw = clean_input($_POST['position'] ?? '');
$position = $positionRaw === '' ? null : (int) $positionRaw;
$cashRaw = clean_input($_POST['cash_amount'] ?? '');
$cashAmount = $cashRaw === '' ? null : (float) $cashRaw;

if ($id < 1 || $awardTypeId < 1) {
    json_response(false, 'Xato parametr');
}
if ($position !== null && ($position < 1 || $position > 5)) {
    json_response(false, 'O\'rin 1 dan 5 gacha bo\'lishi kerak yoki bo\'sh qoldiriladi.');
}
if ($cashAmount !== null && $cashAmount < 0) {
    json_response(false, 'Pul miqdori manfiy bo\'lishi mumkin emas.');
}

$typeStmt = $db->prepare('SELECT id, code FROM competition_result_types WHERE id = ? LIMIT 1');
$typeStmt->bind_param('i', $awardTypeId);
$typeStmt->execute();
$type = $typeStmt->get_result()->fetch_assoc();
if (!$type) {
    json_response(false, 'Mukofot turi topilmadi.');
}
if (($type['code'] ?? '') === 'cash' && $cashAmount === null) {
    json_response(false, 'Pul miqdorini kiriting.');
}
if (($type['code'] ?? '') !== 'cash') {
    $cashAmount = null;
}

$stmt = $db->prepare("UPDATE competition_results SET award_type_id = ?, cash_amount = NULLIF(?, ''), position = NULLIF(?, '') WHERE id = ?");
$cashParam = $cashAmount === null ? '' : (string) $cashAmount;
$positionParam = $position === null ? '' : (string) $position;
$stmt->bind_param('issi', $awardTypeId, $cashParam, $positionParam, $id);
$stmt->execute();
json_response(true, 'Natija yangilandi');
