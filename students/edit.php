<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: /students/list.php');
    exit;
}

$error = '';
$levels = getLevels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class = trim($_POST['class']);

    if ($first_name && $last_name && $class) {
        $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ?, class = ? WHERE id = ?');
        $stmt->execute([$first_name, $last_name, $email, $phone, $class, $id]);
        header('Location: /students/list.php');
        exit;
    } else {
        $error = 'Please fill in all required fields.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper">
    <h2>Edit Student: <?= htmlspecialchars($student['student_id']) ?></h2>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Student ID / Matric No</label>
            <input type="text" value="<?= htmlspecialchars($student['student_id']) ?>" disabled style="background:#f0f2f5;">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Level *</label>
            <select name="class" required>
                <option value="">Select Level</option>
                <?php foreach ($levels as $level): ?>
                <option value="<?= $level ?>" <?= $student['class'] === $level ? 'selected' : '' ?>><?= $level ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="/students/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
