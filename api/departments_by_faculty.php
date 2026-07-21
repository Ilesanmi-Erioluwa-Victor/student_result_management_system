<?php
require_once __DIR__ . '/../config/database.php';

$faculty_id = $_GET['faculty_id'] ?? null;
if (!$faculty_id) {
    echo '<option value="">Select Faculty First</option>';
    exit;
}

$stmt = $pdo->prepare('SELECT id, name FROM departments WHERE faculty_id = ? ORDER BY name');
$stmt->execute([$faculty_id]);
$departments = $stmt->fetchAll();

if (!$departments) {
    echo '<option value="">No departments found</option>';
    exit;
}

foreach ($departments as $d) {
    echo '<option value="' . $d['id'] . '">' . htmlspecialchars($d['name']) . '</option>';
}
