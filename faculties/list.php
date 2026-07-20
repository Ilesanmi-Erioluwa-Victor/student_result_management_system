<?php
require_once __DIR__ . '/../includes/auth.php';

$faculties = $pdo->query('SELECT f.*, (SELECT COUNT(*) FROM departments WHERE faculty_id = f.id) AS dept_count FROM faculties f ORDER BY f.name ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>Faculties</h2>
        <a href="add.php" class="btn btn-success btn-sm">+ Add Faculty</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Faculty Name</th>
                <th>Departments</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($faculties as $f): ?>
            <tr>
                <td><strong><?= htmlspecialchars($f['name']) ?></strong></td>
                <td><?= $f['dept_count'] ?></td>
                <td class="actions">
                    <a href="/departments/list.php?faculty_id=<?= $f['id'] ?>" class="btn btn-info btn-sm">View Depts</a>
                    <a href="add.php?delete=<?= $f['id'] ?>" class="btn btn-danger btn-sm delete-confirm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($faculties)): ?>
            <tr><td colspan="3" style="text-align:center;color:#888;">No faculties created yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
