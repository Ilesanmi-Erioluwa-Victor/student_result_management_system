<?php
require_once __DIR__ . '/../includes/auth.php';

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM faculties WHERE id = ?');
    $stmt->execute([$_GET['delete']]);
    header('Location: /faculties/list.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name) {
        $stmt = $pdo->prepare('INSERT INTO faculties (name) VALUES (?)');
        $stmt->execute([$name]);
        header('Location: /faculties/list.php');
        exit;
    } else {
        $error = 'Please enter a faculty name.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper">
    <h2>Add New Faculty</h2>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Faculty Name *</label>
            <input type="text" name="name" placeholder="e.g., Faculty of Science" required>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Faculty</button>
            <a href="/faculties/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
