<?php
require_once __DIR__ . '/../includes/auth.php';

$subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_code ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>Subjects</h2>
        <a href="add.php" class="btn btn-success btn-sm">+ Add Subject</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject Name</th>
                <th>Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $subject): ?>
            <tr>
                <td><?= htmlspecialchars($subject['subject_code']) ?></td>
                <td><?= htmlspecialchars($subject['subject_name']) ?></td>
                <td><?= htmlspecialchars($subject['class']) ?></td>
                <td class="actions">
                    <a href="add.php?delete=<?= $subject['id'] ?>" class="btn btn-danger btn-sm delete-confirm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($subjects)): ?>
            <tr><td colspan="4" style="text-align:center;color:#888;">No subjects added yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
