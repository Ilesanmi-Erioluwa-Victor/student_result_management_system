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
$faculties = $pdo->query('SELECT id, name FROM faculties ORDER BY name')->fetchAll();
$studentFaculty = null;
$studentDept = null;
if ($student['department_id']) {
    $deptStmt = $pdo->prepare('SELECT faculty_id FROM departments WHERE id = ?');
    $deptStmt->execute([$student['department_id']]);
    $studentFaculty = $deptStmt->fetchColumn();
    $studentDept = $student['department_id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $class = trim($_POST['class']);
    $department_id = $_POST['department_id'] ?: null;

    if ($first_name && $last_name && $class && $department_id) {
        $stmt = $pdo->prepare('UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ?, class = ?, department_id = ? WHERE id = ?');
        $stmt->execute([$first_name, $last_name, $email, $phone, $class, $department_id, $id]);
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
            <label>Faculty *</label>
            <select name="faculty_id" id="facultySelect" required onchange="loadDepts(this.value, null)">
                <option value="">Select Faculty</option>
                <?php foreach ($faculties as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $studentFaculty == $f['id'] ? 'selected' : '' ?>><?= htmlspecialchars($f['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Department *</label>
                <select name="department_id" id="departmentSelect" required onchange="loadLvls(this.value, null)">
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
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="/students/list.php" class="btn" style="background:#e0e0e0;color:#555;">Cancel</a>
        </div>
    </form>
</div>

<script>
function loadDepts(facultyId, selectedDept) {
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
            if (selectedDept) {
                deptSelect.value = selectedDept;
                loadLvls(selectedDept, '<?= $student['class'] ?>');
            }
        } else {
            deptSelect.innerHTML = '<option value="">Error loading departments</option>';
        }
    };
    xhr.send();
}

function loadLvls(departmentId, selectedLevel) {
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
            if (selectedLevel) {
                levelSelect.value = selectedLevel;
            }
        } else {
            levelSelect.innerHTML = '<option value="">Error loading levels</option>';
        }
    };
    xhr.send();
}

<?php if ($studentFaculty): ?>
loadDepts(<?= $studentFaculty ?>, <?= $studentDept ?>);
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
