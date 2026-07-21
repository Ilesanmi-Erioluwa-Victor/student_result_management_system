<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';
$levels = getLevels();
$faculties = $pdo->query('SELECT id, name FROM faculties ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $faculty_id = $_POST['faculty_id'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $class = trim($_POST['class']);
    $username = trim($_POST['username']);
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($student_id && $first_name && $last_name && $department_id && $class && $username && $password && $confirm) {
        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare('SELECT id FROM students WHERE student_id = ?');
                $stmt->execute([$student_id]);
                if ($stmt->fetch()) {
                    $error = 'Student ID already exists in the system.';
                } else {
                    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = 'Username already taken.';
                    } else {
                        $stmt = $pdo->prepare('INSERT INTO students (student_id, first_name, last_name, email, phone, class, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$student_id, $first_name, $last_name, $email, $phone, $class, $department_id]);

                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $full_name = $last_name . ', ' . $first_name;
                        $stmt = $pdo->prepare('INSERT INTO users (username, password, full_name, role, student_id) VALUES (?, ?, ?, ?, ?)');
                        $stmt->execute([$username, $hash, $full_name, 'student', $student_id]);

                        $pdo->commit();

                        $_SESSION['user_id'] = $pdo->lastInsertId();
                        $_SESSION['username'] = $username;
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['role'] = 'student';
                        $_SESSION['student_id'] = $student_id;

                        header('Location: student/dashboard.php');
                        exit;
                    }
                }
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
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
        <div class="login-box" style="max-width:500px;">
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
                    <input type="text" name="student_id" required placeholder="e.g. 2021/001">
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
                    <label>Faculty *</label>
                    <select name="faculty_id" id="facultySelect" required onchange="loadDepartments(this.value)">
                        <option value="">Select Faculty</option>
                        <?php foreach ($faculties as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department_id" id="departmentSelect" required onchange="loadLevels(this.value)">
                            <option value="">Select Faculty First</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Level *</label>
                        <select name="class" id="levelSelect" required>
                            <option value="">Select Department First</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required placeholder="Choose a username">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" required placeholder="Min 6 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:13px;">
                Already have an account? <a href="index.php">Sign In</a>
            </p>
        </div>
    </div>
    <script src="/js/script.js"></script>
    <script>
    function loadDepartments(facultyId) {
        var deptSelect = document.getElementById('departmentSelect');
        var levelSelect = document.getElementById('levelSelect');
        deptSelect.innerHTML = '<option value="">Loading...</option>';
        levelSelect.innerHTML = '<option value="">Select Department First</option>';
        if (!facultyId) {
            deptSelect.innerHTML = '<option value="">Select Faculty First</option>';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/departments_by_faculty.php?faculty_id=' + facultyId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                deptSelect.innerHTML = '<option value="">Select Department</option>' + xhr.responseText;
            } else {
                deptSelect.innerHTML = '<option value="">Error loading departments</option>';
            }
        };
        xhr.send();
    }

    function loadLevels(departmentId) {
        var levelSelect = document.getElementById('levelSelect');
        levelSelect.innerHTML = '<option value="">Loading...</option>';
        if (!departmentId) {
            levelSelect.innerHTML = '<option value="">Select Department First</option>';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/department_levels.php?department_id=' + departmentId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                levelSelect.innerHTML = '<option value="">Select Level</option>' + xhr.responseText;
            } else {
                levelSelect.innerHTML = '<option value="">Error loading levels</option>';
            }
        };
        xhr.send();
    }
    </script>
</body>
</html>
