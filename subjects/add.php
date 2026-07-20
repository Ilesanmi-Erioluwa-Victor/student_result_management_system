<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    header('Location: /subjects/list.php');
    exit;
}

$error = '';
$levels = getLevels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = strtoupper(trim($_POST['subject_code']));
    $subject_name = trim($_POST['subject_name']);
    $class = trim($_POST['class']);
    $semester = trim($_POST['semester']);

    if ($subject_code && $subject_name && $class && $semester) {
        try {
            $stmt = $pdo->prepare('INSERT INTO subjects (subject_code, subject_name, class, semester) VALUES (?, ?, ?, ?)');
            $stmt->execute([$subject_code, $subject_name, $class, $semester]);
            header('Location: /subjects/list.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23505) {
                $error = 'Subject code already exists.';
            } else {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper">
    <h2>Add New Subject</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Subject Code *</label>
                <input type="text" name="subject_code" placeholder="e.g., MATH101" required>
            </div>
            <div class="form-group">
                <label>Subject Name *</label>
                <input type="text" name="subject_name" placeholder="e.g., Mathematics" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Level *</label>
                <select name="class" required>
                    <option value="">Select Level</option>
                    <option value="All">All Levels</option>
                    <?php foreach ($levels as $level): ?>
                    <option value="<?= $level ?>"><?= $level ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Semester *</label>
                <select name="semester" required>
                    <option value="">Select Semester</option>
                    <option value="First Semester">First Semester</option>
                    <option value="Second Semester">Second Semester</option>
                </select>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Subject</button>
            <a href="/subjects/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
