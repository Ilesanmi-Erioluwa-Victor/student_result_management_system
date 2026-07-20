<?php
require_once __DIR__ . '/../includes/auth.php';

$faculty_id = $_GET['faculty_id'] ?? null;
$faculty = null;

if ($faculty_id) {
    $stmt = $pdo->prepare('SELECT * FROM faculties WHERE id = ?');
    $stmt->execute([$faculty_id]);
    $faculty = $stmt->fetch();
}

$departments = $pdo->prepare('
    SELECT d.*, f.name AS faculty_name,
        (SELECT COUNT(*) FROM students WHERE department_id = d.id) AS student_count
    FROM departments d
    JOIN faculties f ON d.faculty_id = f.id
    WHERE ($faculty_id IS NULL OR d.faculty_id = ?)
    ORDER BY f.name, d.name
');
if ($faculty_id) {
    $departments->execute([$faculty_id]);
} else {
    $departments->execute([null]);
}
$departments = $departments->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2><?= $faculty ? htmlspecialchars($faculty['name']) : 'All' ?> Departments</h2>
        <a href="add.php<?= $faculty_id ? '?faculty_id=' . $faculty_id : '' ?>" class="btn btn-success btn-sm">+ Add Department</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Department Name</th>
                <th>Faculty</th>
                <th>Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($departments as $d): ?>
            <tr>
                <td><strong><?= htmlspecialchars($d['name']) ?></strong></td>
                <td><?= htmlspecialchars($d['faculty_name']) ?></td>
                <td><?= $d['student_count'] ?></td>
                <td class="actions">
                    <a href="add.php?delete=<?= $d['id'] ?>&faculty_id=<?= $faculty_id ?>" class="btn btn-danger btn-sm delete-confirm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($departments)): ?>
            <tr><td colspan="4" style="text-align:center;color:#888;">No departments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <div style="padding:15px;text-align:right;">
        <a href="/faculties/list.php" class="btn btn-sm" style="background:#e0e0e0;color:#555;">Back to Faculties</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
