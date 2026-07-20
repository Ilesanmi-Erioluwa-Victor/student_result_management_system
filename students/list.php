<?php
require_once __DIR__ . '/../includes/auth.php';

$students = $pdo->query('SELECT * FROM students ORDER BY student_id ASC')->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="table-container">
    <div class="table-header">
        <h2>Students</h2>
        <a href="add.php" class="btn btn-success btn-sm">+ Add Student</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Level</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
            <tr>
                <td><?= htmlspecialchars($student['student_id']) ?></td>
                <td><?= htmlspecialchars($student['first_name']) ?></td>
                <td><?= htmlspecialchars($student['last_name']) ?></td>
                <td><?= htmlspecialchars($student['email'] ?: '-') ?></td>
                <td><?= htmlspecialchars($student['phone'] ?: '-') ?></td>
                <td><?= htmlspecialchars($student['class']) ?></td>
                <td class="actions">
                    <a href="edit.php?id=<?= $student['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $student['id'] ?>" class="btn btn-danger btn-sm delete-confirm">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($students)): ?>
            <tr><td colspan="7" style="text-align:center;color:#888;">No students registered yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
