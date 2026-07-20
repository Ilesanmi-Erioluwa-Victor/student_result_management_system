<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';
$levels = getLevels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class = trim($_POST['class']);

    if ($student_id && $first_name && $last_name && $class) {
        try {
            $stmt = $pdo->prepare('INSERT INTO students (student_id, first_name, last_name, email, phone, class) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$student_id, $first_name, $last_name, $email, $phone, $class]);
            header('Location: /students/list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) {
                $error = 'Student ID already exists.';
            } else {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper">
    <h2>Add New Student</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Student ID / Matric No *</label>
            <input type="text" name="student_id" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>
        </div>
        <div class="form-group">
            <label>Level *</label>
            <select name="class" required>
                <option value="">Select Level</option>
                <?php foreach ($levels as $level): ?>
                <option value="<?= $level ?>"><?= $level ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Student</button>
            <a href="/students/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
