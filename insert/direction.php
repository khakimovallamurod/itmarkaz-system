<?php
require_once __DIR__ . '/../api/bootstrap.php';

$name = clean_input($_POST['name'] ?? '');
if ($name === '') {
    json_response(false, 'Nom bo\'sh bo\'lmasin.');
}

$stmt = $db->prepare('INSERT INTO directions (name) VALUES (?)');
$stmt->bind_param('s', $name);
if (!$stmt->execute()) {
    json_response(false, 'Yo\'nalish qo\'shishda xatolik yoki dublikat mavjud.');
}

json_response(true, 'Yo\'nalish qo\'shildi.');

