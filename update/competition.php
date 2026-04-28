<?php
require_once __DIR__ . '/../api/bootstrap.php';
$id = (int) ($_POST['id'] ?? 0);
$name = clean_input($_POST['name'] ?? '');
$description = clean_input($_POST['description'] ?? '');
$registration_deadline = clean_input($_POST['registration_deadline'] ?? '');
$competition_date = clean_input($_POST['competition_date'] ?? '');
$location = clean_input($_POST['location'] ?? '');
if ($id < 1 || $name === '' || $registration_deadline === '' || $competition_date === '') json_response(false, 'Maydonlar noto\'g\'ri.');
$stmt = $db->prepare('UPDATE competitions SET name=?, description=?, registration_deadline=?, competition_date=?, location=? WHERE id=?');
$stmt->bind_param('sssssi', $name, $description, $registration_deadline, $competition_date, $location, $id);
$stmt->execute();
json_response(true, 'Tanlov yangilandi.');
