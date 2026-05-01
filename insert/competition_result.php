<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_POST['competition_id'] ?? 0);
$awardTypeId = (int) ($_POST['award_type_id'] ?? 0);
$positionRaw = clean_input($_POST['position'] ?? '');
$position = $positionRaw === '' ? null : (int) $positionRaw;
$cashRaw = clean_input($_POST['cash_amount'] ?? '');
$cashAmount = $cashRaw === '' ? null : (float) $cashRaw;

$studentIds = $_POST['student_ids'] ?? [];
if (!$studentIds && isset($_POST['student_id'])) {
    $studentIds = [$_POST['student_id']];
}
$studentIds = array_values(array_unique(array_filter(array_map('intval', (array) $studentIds), static fn ($id) => $id > 0)));

if ($competition_id < 1 || !$studentIds || $awardTypeId < 1) {
    json_response(false, 'Maydonlar xato');
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

$stmt = $db->prepare("
  INSERT INTO competition_results (competition_id, student_id, award_type_id, cash_amount, position)
  VALUES (?, ?, ?, NULLIF(?, ''), NULLIF(?, ''))
  ON DUPLICATE KEY UPDATE
    award_type_id = VALUES(award_type_id),
    cash_amount = VALUES(cash_amount),
    position = VALUES(position)
");

$saved = 0;
foreach ($studentIds as $studentId) {
    $cashParam = $cashAmount === null ? '' : (string) $cashAmount;
    $positionParam = $position === null ? '' : (string) $position;
    $stmt->bind_param('iiiss', $competition_id, $studentId, $awardTypeId, $cashParam, $positionParam);
    $stmt->execute();
    if ($stmt->affected_rows >= 0) {
        $saved += 1;
    }
}

json_response(true, 'Natijalar saqlandi.', ['saved_count' => $saved]);
