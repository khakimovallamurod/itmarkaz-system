<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
$title = clean_input($_POST['title'] ?? '');
$deadline = clean_input($_POST['deadline'] ?? '');
$description = clean_input($_POST['description'] ?? '');

if ($id < 1 || $title === '' || $deadline === '') {
    json_response(false, 'Ma\'lumotlar to\'liq emas.');
}

$allowedGroups = ['talaba', 'kurs_oqvchisi', 'mentor'];
$rawGroups = (array) ($_POST['target_groups'] ?? []);
$targetGroups = array_values(array_unique(array_intersect($allowedGroups, $rawGroups)));
$tgJson = json_encode($targetGroups, JSON_UNESCAPED_UNICODE);

$db->begin_transaction();
try {
    $res = $db->query("SELECT file_path FROM task_schedule WHERE id = $id");
    $row = $res->fetch_assoc();
    $filePath = $row['file_path'];

    if (!empty($_FILES['task_file']['name']) && (int) ($_FILES['task_file']['error'] ?? 1) === 0) {
        $uploadDir = __DIR__ . '/../uploads/task_files';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        
        $originalName = basename((string) $_FILES['task_file']['name']);
        $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            json_response(false, 'Faqat PDF fayl yuklashga ruxsat berilgan.');
        }
        $fileName = uniqid('task_', true) . '.pdf';
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file((string) $_FILES['task_file']['tmp_name'], $targetPath)) {
            // Delete old file if exists
            if ($filePath && file_exists(__DIR__ . '/../' . $filePath)) {
                @unlink(__DIR__ . '/../' . $filePath);
            }
            $filePath = 'uploads/task_files/' . $fileName;
        }
    }

    $stmt = $db->prepare("UPDATE task_schedule SET title = ?, deadline = ?, file_path = ?, description = ?, target_groups = ? WHERE id = ?");
    $stmt->bind_param('sssssi', $title, $deadline, $filePath, $description, $tgJson, $id);
    $stmt->execute();
    $db->commit();

    $short = function_exists('mb_substr') ? mb_substr($description, 0, 60) : substr($description, 0, 60);
    json_response(true, 'Topshiriq yangilandi.', [
        'event' => [
            'id' => $id,
            'title' => $title,
            'start' => $deadline,
            'short' => $short
        ]
    ]);
} catch (Exception $e) {
    $db->rollback();
    json_response(false, 'Xatolik: ' . $e->getMessage());
}
