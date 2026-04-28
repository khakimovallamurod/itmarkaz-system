<?php
require_once __DIR__ . '/../api/bootstrap.php';

$term = clean_input($_GET['q'] ?? '');
if ($term === '') {
    json_response(true, 'Bo\'sh qidiruv.', []);
}

$q = '%' . $term . '%';
$results = [];

$stmt = $db->prepare('SELECT id, fio AS title, \'Talaba\' AS type FROM students WHERE fio LIKE ? LIMIT 5');
$stmt->bind_param('s', $q);
$stmt->execute();
$results = array_merge($results, $stmt->get_result()->fetch_all(MYSQLI_ASSOC));

$stmt2 = $db->prepare('SELECT id, name AS title, \'Kurs\' AS type FROM courses WHERE name LIKE ? LIMIT 5');
$stmt2->bind_param('s', $q);
$stmt2->execute();
$results = array_merge($results, $stmt2->get_result()->fetch_all(MYSQLI_ASSOC));

$stmt3 = $db->prepare('SELECT id, name AS title, \'Tanlov\' AS type FROM competitions WHERE name LIKE ? LIMIT 5');
$stmt3->bind_param('s', $q);
$stmt3->execute();
$results = array_merge($results, $stmt3->get_result()->fetch_all(MYSQLI_ASSOC));

$stmt4 = $db->prepare('SELECT id, team_name AS title, \'Jamoa\' AS type FROM teams WHERE team_name LIKE ? LIMIT 5');
$stmt4->bind_param('s', $q);
$stmt4->execute();
$results = array_merge($results, $stmt4->get_result()->fetch_all(MYSQLI_ASSOC));

json_response(true, 'Qidiruv natijasi', $results);
