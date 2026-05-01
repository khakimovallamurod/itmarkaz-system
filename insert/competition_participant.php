<?php
require_once __DIR__ . '/../api/bootstrap.php';
$competition_id = (int) ($_POST['competition_id'] ?? 0);
$studentIds = $_POST['student_ids'] ?? [];
if (!$studentIds && isset($_POST['student_id'])) {
    $studentIds = [$_POST['student_id']];
}
$studentIds = array_values(array_unique(array_filter(array_map('intval', (array) $studentIds), static fn ($id) => $id > 0)));

if ($competition_id < 1 || !$studentIds) {
    json_response(false, 'Noto\'g\'ri qiymat.');
}

$stmt = $db->prepare('INSERT IGNORE INTO competition_participants (competition_id, student_id) VALUES (?, ?)');
$added = 0;
foreach ($studentIds as $studentId) {
    $stmt->bind_param('ii', $competition_id, $studentId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $added += 1;
    }
}

$skipped = count($studentIds) - $added;
if ($added < 1) {
    json_response(false, 'Tanlangan talabalar allaqachon ishtirokchi sifatida mavjud.');
}

json_response(true, 'Ishtirokchilar qo\'shildi.', [
    'added_count' => $added,
    'skipped_count' => $skipped,
]);
