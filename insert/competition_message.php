<?php
require_once __DIR__ . '/../api/bootstrap.php';
require_once __DIR__ . '/../bot/send.php';

$competitionId = (int) ($_POST['competition_id'] ?? 0);
$message = clean_input($_POST['message'] ?? '');
$studentIds = $_POST['student_ids'] ?? [];
$studentIds = array_values(array_unique(array_filter(array_map('intval', (array) $studentIds), static fn ($id) => $id > 0)));

if ($competitionId < 1 || $message === '' || !$studentIds) {
    json_response(false, 'Maydonlar to\'liq emas');
}

$competitionStmt = $db->prepare('SELECT name, description, registration_deadline, competition_date, location FROM competitions WHERE id = ? LIMIT 1');
$competitionStmt->bind_param('i', $competitionId);
$competitionStmt->execute();
$competition = $competitionStmt->get_result()->fetch_assoc();
if (!$competition) {
    json_response(false, 'Tanlov topilmadi');
}

$placeholders = implode(',', array_fill(0, count($studentIds), '?'));
$types = str_repeat('i', count($studentIds));
$studentStmt = $db->prepare("SELECT id, fio, telegram_chat_id FROM students WHERE id IN ($placeholders)");
$studentStmt->bind_param($types, ...$studentIds);
$studentStmt->execute();
$students = $studentStmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$students) {
    json_response(false, 'Tanlangan talabalar topilmadi');
}

$formatDate = static function (?string $date): string {
    if (!$date) return '-';
    $ts = strtotime($date);
    if ($ts === false) return (string) $date;
    return date('d.m.Y', $ts);
};

$fullMessage = "📢 TANLOV E'LONI\n\n"
    . "🏆 Nomi: " . ($competition['name'] ?: '-') . "\n\n"
    . "📄 Tavsif:\n" . (($competition['description'] ?? '') !== '' ? $competition['description'] : '-') . "\n\n"
    . "📅 Ro'yxatdan o'tish oxirgi sana:\n" . $formatDate($competition['registration_deadline'] ?? null) . "\n\n"
    . "🎯 O'tkazilish sanasi:\n" . $formatDate($competition['competition_date'] ?? null) . "\n\n"
    . "📍 Manzil:\n" . (($competition['location'] ?? '') !== '' ? $competition['location'] : '-') . "\n\n"
    . "✉️ Xabar:\n" . $message;
$sentCount = 0;
$failed = [];

foreach ($students as $student) {
    $chatId = trim((string) ($student['telegram_chat_id'] ?? ''));
    if ($chatId === '') {
        telegram_error_log('telegram_chat_id mavjud emas, skip qilindi', [
            'student_id' => (int) $student['id'],
            'fio' => (string) ($student['fio'] ?? ''),
            'competition_id' => $competitionId,
        ]);
        $failed[] = [
            'student_id' => (int) $student['id'],
            'fio' => $student['fio'],
            'reason' => 'telegram_chat_id mavjud emas',
        ];
        continue;
    }

    $result = send_telegram_message($chatId, $fullMessage);
    if ($result['success']) {
        $sentCount++;
    } else {
        telegram_error_log('Xabar yuborilmadi', [
            'student_id' => (int) $student['id'],
            'fio' => (string) ($student['fio'] ?? ''),
            'competition_id' => $competitionId,
            'chat_id' => $chatId,
            'reason' => $result['error'] ?? 'Xatolik',
        ]);
        $failed[] = [
            'student_id' => (int) $student['id'],
            'fio' => $student['fio'],
            'reason' => $result['error'] ?? 'Xatolik',
        ];
    }
}

json_response(true, 'Xabar yuborish yakunlandi', [
    'sent_count' => $sentCount,
    'failed_count' => count($failed),
    'failed' => $failed,
]);
