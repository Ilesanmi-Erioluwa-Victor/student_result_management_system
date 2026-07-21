<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if ($_SESSION['role'] === 'student') {
    header('Location: /student/dashboard.php');
    exit;
}

$studentCount = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$subjectCount = $pdo->query('SELECT COUNT(*) FROM subjects')->fetchColumn();
$resultCount = $pdo->query('SELECT COUNT(*) FROM results')->fetchColumn();
$facultyCount = $pdo->query('SELECT COUNT(*) FROM faculties')->fetchColumn();
$departmentCount = $pdo->query('SELECT COUNT(*) FROM departments')->fetchColumn();
$institutionType = getInstitutionType();
$institutionName = getSetting('institution_name');

require_once 'includes/header.php';
?>

<h2 style="margin-top:30px;"><?= htmlspecialchars($institutionName) ?> - Admin Dashboard</h2>
<p style="color:#888;margin-bottom:10px;">Institution Type: <?= ucfirst($institutionType) ?></p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3><?= $studentCount ?></h3>
        <p>Students</p>
        <a href="/students/list.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage</a>
    </div>
    <div class="stat-card">
        <h3><?= $subjectCount ?></h3>
        <p>Subjects</p>
        <a href="/subjects/list.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage</a>
    </div>
    <div class="stat-card">
        <h3><?= $resultCount ?></h3>
        <p>Results Entered</p>
        <a href="/results/add.php" class="btn btn-info btn-sm" style="margin-top:10px;">Enter</a>
    </div>
    <div class="stat-card">
        <h3><?= $facultyCount ?></h3>
        <p>Faculties</p>
        <a href="/faculties/list.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage</a>
    </div>
    <div class="stat-card">
        <h3><?= $departmentCount ?></h3>
        <p>Departments</p>
        <a href="/departments/list.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage</a>
    </div>
</div>

<div class="table-container" style="margin-top:30px;">
    <div class="table-header">
        <h2>Recent Results</h2>
        <a href="/results/add.php" class="btn btn-success btn-sm">+ Add Result</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Subject</th>
                <th>Term</th>
                <th>Session</th>
                <th>Total</th>
                <th>Grade</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query('
                SELECT r.*, s.first_name, s.last_name, sub.subject_name
                FROM results r
                JOIN students s ON r.student_id = s.student_id
                JOIN subjects sub ON r.subject_code = sub.subject_code
                WHERE (sub.class = s.class OR sub.class = \'All\')
                AND (sub.department_id IS NULL OR sub.department_id = s.department_id)
                ORDER BY r.created_at DESC
                LIMIT 10
            ');
            while ($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['subject_name']) ?></td>
                <td><?= htmlspecialchars($row['term']) ?></td>
                <td><?= htmlspecialchars($row['session']) ?></td>
                <td><?= $row['total_score'] ?></td>
                <td><strong><?= htmlspecialchars($row['grade']) ?></strong></td>
                <td><a href="/results/view.php?student_id=<?= urlencode($row['student_id']) ?>&term=<?= urlencode($row['term']) ?>&session=<?= urlencode($row['session']) ?>" class="btn btn-info btn-sm">View</a></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($stmt->rowCount() === 0): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">No results found. <a href="/results/add.php">Enter results</a>.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
