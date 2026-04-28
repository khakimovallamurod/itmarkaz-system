<?php
require_once __DIR__ . '/../api/bootstrap.php';
$name = clean_input($_POST['name'] ?? '');
$description = clean_input($_POST['description'] ?? '');
$registration_deadline = clean_input($_POST['registration_deadline'] ?? '');
$competition_date = clean_input($_POST['competition_date'] ?? '');
$location = clean_input($_POST['location'] ?? '');
if ($name === '' || $registration_deadline === '' || $competition_date === '') json_response(false, 'Majburiy maydonlar bo\'sh.');
$stmt = $db->prepare('INSERT INTO competitions (name, description, registration_deadline, competition_date, location) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $name, $description, $registration_deadline, $competition_date, $location);
$stmt->execute();
json_response(true, 'Tanlov qo\'shildi.');
