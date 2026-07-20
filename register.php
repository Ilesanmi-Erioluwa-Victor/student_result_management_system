<?php
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($student_id && $username && $password && $confirm) {
        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
            $stmt->execute([$student_id]);
            $student = $stmt->fetch();

            if (!$student) {
                $error = 'Student ID not found. Contact the admin to register your profile first.';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR student_id = ?');
                $stmt->execute([$username, $student_id]);
                if ($stmt->fetch()) {
                    $error = 'Username or Student ID already registered.';
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $full_name = $student['last_name'] . ', ' . $student['first_name'];
                    $stmt = $pdo->prepare('INSERT INTO users (username, password, full_name, role, student_id) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$username, $hash, $full_name, 'student', $student_id]);

                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    $_SESSION['username'] = $username;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['role'] = 'student';
                    $_SESSION['student_id'] = $student_id;

                    header('Location: student/dashboard.php');
                    exit;
                }
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Student Result Management System</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23667eea'/%3E%3Ctext x='16' y='22' font-size='18' text-anchor='middle' fill='white' font-family='sans-serif'%3ES%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Student Registration</h2>
            <p>Create your account to access the portal</p>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Student / Matric No *</label>
                    <input type="text" name="student_id" required placeholder="Enter your student ID">
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Choose a username">
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required placeholder="Min 6 characters">
                </div>
                <div class="form-group">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:13px;">
                Already have an account? <a href="index.php">Sign In</a>
            </p>
        </div>
    </div>
    <script src="/js/script.js"></script>
</body>
</html>
