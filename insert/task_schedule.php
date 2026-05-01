<?php
require_once __DIR__ . '/../api/bootstrap.php';

$db->query("
  CREATE TABLE IF NOT EXISTS task_schedule (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    deadline DATE NOT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    target_groups LONGTEXT DEFAULT NULL,
    student_ids LONGTEXT DEFAULT NULL,
    course_student_ids LONGTEXT DEFAULT NULL,
    mentor_ids LONGTEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_task_schedule_deadline (deadline)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$title = clean_input($_POST['title'] ?? '');
$deadline = clean_input($_POST['deadline'] ?? '');
$description = clean_input($_POST['description'] ?? '');

if ($title === '' || $deadline === '') {
    json_response(false, 'Topshiriq nomi va muddati majburiy.');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $deadline)) {
    json_response(false, 'Sana formati xato.');
}

$allowedGroups = ['talaba', 'kurs_oqvchisi', 'mentor'];
$groupAliases = [
    'rezident' => 'talaba',
    'rezidentlar' => 'talaba',
    'talabalar' => 'talaba',
    'talaba' => 'talaba',
    'kurs_oqvchilari' => 'kurs_oqvchisi',
    'kurs_oqvchisi' => 'kurs_oqvchisi',
    'mentorlar' => 'mentor',
    'mentor' => 'mentor',
];
$rawGroups = (array) ($_POST['target_groups'] ?? []);
$normalizedGroups = [];
foreach ($rawGroups as $group) {
    $key = trim((string) $group);
    if (isset($groupAliases[$key])) {
        $normalizedGroups[] = $groupAliases[$key];
    }
}
$targetGroups = array_values(array_unique(array_intersect($allowedGroups, $normalizedGroups)));

$filePath = null;
if (!empty($_FILES['task_file']['name']) && (int) ($_FILES['task_file']['error'] ?? 1) === 0) {
    $uploadDir = __DIR__ . '/../uploads/task_files';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }
    $originalName = basename((string) $_FILES['task_file']['name']);
    $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $safeExt = preg_replace('/[^a-z0-9]/', '', $ext);
    $fileName = uniqid('task_', true) . ($safeExt !== '' ? '.' . $safeExt : '');
    $targetPath = $uploadDir . '/' . $fileName;
    if (!move_uploaded_file((string) $_FILES['task_file']['tmp_name'], $targetPath)) {
        json_response(false, 'Fayl yuklashda xatolik.');
    }
    $filePath = 'uploads/task_files/' . $fileName;
}

$stmt = $db->prepare("
  INSERT INTO task_schedule (title, deadline, file_path, description, target_groups, student_ids, course_student_ids, mentor_ids)
  VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$targetGroupsJson = json_encode($targetGroups, JSON_UNESCAPED_UNICODE);
$emptyJson = json_encode([], JSON_UNESCAPED_UNICODE);
$stmt->bind_param('ssssssss', $title, $deadline, $filePath, $description, $targetGroupsJson, $emptyJson, $emptyJson, $emptyJson);
$stmt->execute();

$short = function_exists('mb_substr') ? mb_substr($description, 0, 60) : substr($description, 0, 60);
json_response(true, 'Topshiriq saqlandi.', [
    'event' => [
        'id' => (int) $db->insert_id,
        'title' => $title,
        'start' => $deadline,
        'short' => $short,
    ],
]);
