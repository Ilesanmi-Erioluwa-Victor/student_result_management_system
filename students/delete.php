<?php
require_once __DIR__ . '/../includes/auth.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
$stmt->execute([$id]);

header('Location: list.php');
exit;
