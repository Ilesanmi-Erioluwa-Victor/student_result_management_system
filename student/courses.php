<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
redirectIfNotLoggedIn();

if ($_SESSION['role'] !== 'student') {
    header('Location: /dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: /logout.php');
    exit;
}

$message = '';
$error = '';

if (isset($_GET['drop']) && isset($_GET['subject_code']) && isset($_GET['term']) && isset($_GET['session'])) {
    $stmt = $pdo->prepare('DELETE FROM course_registrations WHERE student_id = ? AND subject_code = ? AND term = ? AND session = ?');
    $stmt->execute([$student_id, $_GET['subject_code'], $_GET['term'], $_GET['session']]);
    $message = 'Course dropped successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $term = $_POST['term'];
    $session = $_POST['session'];
    $subject_codes = $_POST['subject_codes'] ?? [];

    if ($term && $session && !empty($subject_codes)) {
        $count = 0;
        foreach ($subject_codes as $code) {
            try {
                $stmt = $pdo->prepare('INSERT INTO course_registrations (student_id, subject_code, term, session) VALUES (?, ?, ?, ?) ON CONFLICT DO NOTHING');
                $stmt->execute([$student_id, $code, $term, $session]);
                if ($stmt->rowCount() > 0) $count++;
            } catch (PDOException $e) {
                $error = 'Error registering course: ' . htmlspecialchars($code);
            }
        }
        if ($count > 0) $message = "$count course(s) registered successfully.";
        if (!$error && $count === 0) $message = 'Selected courses are already registered.';
    } else {
        $error = 'Please select at least one course.';
    }
}

$sessions = ['2023/2024', '2024/2025', '2025/2026', '2026/2027'];
$currentTerm = $_GET['term'] ?? ($_POST['term'] ?? 'First Semester');
$currentSession = $_GET['session'] ?? ($_POST['session'] ?? end($sessions));

$stmt = $pdo->prepare('
    SELECT sub.* FROM subjects sub
    WHERE (sub.class = ? OR sub.class = \'All\')
    AND sub.semester = ?
    AND (sub.department_id = ? OR sub.department_id IS NULL)
    ORDER BY sub.subject_name ASC
');
$stmt->execute([$student['class'], $currentTerm, $student['department_id']]);
$availableSubjects = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT subject_code FROM course_registrations WHERE student_id = ? AND term = ? AND session = ?');
$stmt->execute([$student_id, $currentTerm, $currentSession]);
$registered = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper" style="max-width:900px;">
    <h2>Course Registration</h2>
    <p style="color:#888;margin-bottom:20px;">
        <?= htmlspecialchars($student['student_id']) ?> — Level: <?= htmlspecialchars($student['class']) ?>
    </p>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="GET" style="margin-bottom:20px;">
        <div class="form-row">
            <div class="form-group">
                <label>Semester</label>
                <select name="term" onchange="this.form.submit()">
                    <option value="First Semester" <?= $currentTerm === 'First Semester' ? 'selected' : '' ?>>First Semester</option>
                    <option value="Second Semester" <?= $currentTerm === 'Second Semester' ? 'selected' : '' ?>>Second Semester</option>
                </select>
            </div>
            <div class="form-group">
                <label>Session</label>
                <select name="session" onchange="this.form.submit()">
                    <?php foreach ($sessions as $s): ?>
                    <option value="<?= $s ?>" <?= $currentSession === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if (!empty($availableSubjects)): ?>
    <form method="POST">
        <input type="hidden" name="term" value="<?= htmlspecialchars($currentTerm) ?>">
        <input type="hidden" name="session" value="<?= htmlspecialchars($currentSession) ?>">

        <div class="table-container">
            <div class="table-header">
                <h3>Available Courses</h3>
                <span style="font-size:13px;color:#888;"><?= count($availableSubjects) ?> course(s)</span>
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">Select</th>
                        <th>Code</th>
                        <th>Course Title</th>
                        <th>CU</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($availableSubjects as $subj):
                        $isRegistered = in_array($subj['subject_code'], $registered);
                    ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="subject_codes[]" value="<?= htmlspecialchars($subj['subject_code']) ?>"
                                <?= $isRegistered ? 'checked disabled' : '' ?>>
                        </td>
                        <td><?= htmlspecialchars($subj['subject_code']) ?></td>
                        <td><?= htmlspecialchars($subj['subject_name']) ?></td>
                        <td><?= (int) $subj['credit_unit'] ?></td>
                        <td>
                            <?php if ($isRegistered): ?>
                                <span style="color:#48bb78;">Registered</span>
                                <a href="?drop=1&subject_code=<?= urlencode($subj['subject_code']) ?>&term=<?= urlencode($currentTerm) ?>&session=<?= urlencode($currentSession) ?>"
                                   class="btn btn-danger btn-sm delete-confirm" style="margin-left:5px;">Drop</a>
                            <?php else: ?>
                                <span style="color:#888;">Available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-20">Register Selected Courses</button>
        <a href="/student/dashboard.php" class="btn" style="background:#e0e0e0;color:#555;">Back to Dashboard</a>
    </form>
    <?php else: ?>
    <p style="text-align:center;color:#888;padding:40px 0;">No courses available for your level and department this semester.</p>
    <a href="/student/dashboard.php" class="btn" style="background:#e0e0e0;color:#555;">Back to Dashboard</a>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
