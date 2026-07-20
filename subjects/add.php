<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    header('Location: list.php');
    exit;
}

$message = '';
$error = '';
$levels = getLevels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_code = strtoupper(trim($_POST['subject_code']));
    $subject_name = trim($_POST['subject_name']);
    $class = trim($_POST['class']);

    if ($subject_code && $subject_name && $class) {
        try {
            $stmt = $pdo->prepare('INSERT INTO subjects (subject_code, subject_name, class) VALUES (?, ?, ?)');
            $stmt->execute([$subject_code, $subject_name, $class]);
            $message = 'Subject added successfully.';
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
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
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
        <button type="submit" class="btn btn-primary">Save Subject</button>
        <a href="list.php" class="btn" style="background:#e0e0e0;margin-left:10px;">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
