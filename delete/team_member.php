<?php
require_once __DIR__ . '/../api/bootstrap.php';

$id = (int) ($_POST['id'] ?? 0);
if ($id < 1) {
    json_response(false, 'ID xato');
}

$stmt = $db->prepare('DELETE FROM team_members WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();

json_response(true, 'A\'zo jamoadan chiqarildi');
