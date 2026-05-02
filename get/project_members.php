<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
$db = (new Database())->connect();

$projectId = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;

if ($projectId <= 0) {
    json_response(false, 'Loyiha ID noto\'g\'ri');
}

$sql = "
    SELECT s.id, s.fio 
    FROM students s
    JOIN project_members pm ON pm.student_id = s.id
    WHERE pm.project_id = ?
    ORDER BY s.fio ASC
";

$stmt = $db->prepare($sql);
$stmt->bind_param('i', $projectId);
$stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

json_response(true, 'Muvaffaqiyatli', $members);
