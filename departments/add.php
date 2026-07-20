<?php
require_once __DIR__ . '/../includes/auth.php';

$faculty_id = $_GET['faculty_id'] ?? null;

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM departments WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    $fid = $_GET['faculty_id'] ?? null;
    header('Location: /departments/list.php' . ($fid ? '?faculty_id=' . $fid : ''));
    exit;
}

$faculties = $pdo->query('SELECT * FROM faculties ORDER BY name ASC')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $faculty = $_POST['faculty_id'];
    if ($name && $faculty) {
        $stmt = $pdo->prepare('INSERT INTO departments (name, faculty_id) VALUES (?, ?)');
        $stmt->execute([$name, $faculty]);
        header('Location: /departments/list.php?faculty_id=' . $faculty);
        exit;
    } else {
        $error = 'Please fill in all fields.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper">
    <h2>Add New Department</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Department Name *</label>
            <input type="text" name="name" placeholder="e.g., Computer Science" required>
        </div>
        <div class="form-group">
            <label>Faculty *</label>
            <select name="faculty_id" required>
                <option value="">Select Faculty</option>
                <?php foreach ($faculties as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $faculty_id == $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Department</button>
            <a href="/departments/list.php<?= $faculty_id ? '?faculty_id=' . $faculty_id : '' ?>" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
