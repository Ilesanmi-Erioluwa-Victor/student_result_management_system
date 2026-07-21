<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$faculties = $pdo->query('SELECT id, name FROM faculties ORDER BY name')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class = trim($_POST['class']);
    $department_id = $_POST['department_id'] ?: null;

    if ($student_id && $first_name && $last_name && $class && $department_id) {
        try {
            $stmt = $pdo->prepare('INSERT INTO students (student_id, first_name, last_name, email, phone, class, department_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$student_id, $first_name, $last_name, $email, $phone, $class, $department_id]);
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
            <label>Faculty *</label>
            <select name="faculty_id" id="facultySelect" required onchange="loadDepts(this.value)">
                <option value="">Select Faculty</option>
                <?php foreach ($faculties as $f): ?>
                <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Department *</label>
                <select name="department_id" id="departmentSelect" required onchange="loadLvls(this.value)">
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
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Student</button>
            <a href="/students/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<script>
function loadDepts(facultyId) {
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

function loadLvls(departmentId) {
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
