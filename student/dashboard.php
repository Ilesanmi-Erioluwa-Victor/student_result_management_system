<?php
require_once __DIR__ . '/../config/database.php';
redirectIfNotLoggedIn();

if ($_SESSION['role'] !== 'student') {
    header('Location: /dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare('
    SELECT s.*, d.name AS department_name, f.name AS faculty_name
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN faculties f ON d.faculty_id = f.id
    WHERE s.student_id = ?
');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM course_registrations WHERE student_id = ?');
$stmt->execute([$student_id]);
$registeredCount = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM results WHERE student_id = ?');
$stmt->execute([$student_id]);
$resultCount = $stmt->fetchColumn();

$institutionName = getSetting('institution_name');

require_once __DIR__ . '/../includes/header.php';
?>

<h2 style="margin-top:30px;">Student Dashboard</h2>
<p style="color:#888;margin-bottom:10px;"><?= htmlspecialchars($institutionName) ?></p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
        <p><?= htmlspecialchars($student['student_id']) ?></p>
        <p style="margin-top:5px;font-size:12px;color:#888;">
            Level: <?= htmlspecialchars($student['class']) ?> |
            Dept: <?= htmlspecialchars($student['department_name'] ?? 'N/A') ?>
        </p>
    </div>
    <div class="stat-card">
        <h3><?= $registeredCount ?></h3>
        <p>Registered Courses</p>
        <a href="/student/courses.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage Courses</a>
    </div>
    <div class="stat-card">
        <h3><?= $resultCount ?></h3>
        <p>Results Available</p>
        <a href="/student/results.php" class="btn btn-info btn-sm" style="margin-top:10px;">View Results</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
