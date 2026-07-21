<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$department_id = $_GET['department_id'] ?? null;
$levels = getDepartmentLevels($pdo, $department_id);

if (!$levels) {
    echo '<option value="">No levels available</option>';
    exit;
}

foreach ($levels as $level) {
    echo '<option value="' . htmlspecialchars($level) . '">' . htmlspecialchars($level) . '</option>';
}
