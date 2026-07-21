<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
redirectIfNotLoggedIn();

if ($_SESSION['role'] !== 'student') {
    header('Location: /dashboard.php');
    exit;
}

$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare('
    SELECT s.*, d.name AS department_name, f.name AS faculty_name
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    LEFT JOIN faculties f ON d.faculty_id = f.id
    WHERE s.student_id = ?
');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM course_registrations WHERE student_id = ?');
$stmt->execute([$student_id]);
$registeredCount = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM results WHERE student_id = ?');
$stmt->execute([$student_id]);
$resultCount = $stmt->fetchColumn();

$institutionName = getSetting('institution_name');

$levels = getDepartmentLevels($pdo, $student['department_id']);
$nextLevel = getNextLevel($student['class'], $levels);

$upgraded = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_level'])) {
    $next = $_POST['upgrade_level'];
    $stmt = $pdo->prepare('SELECT class FROM students WHERE student_id = ?');
    $stmt->execute([$student_id]);
    $currentClass = $stmt->fetchColumn();
    $expectedNext = getNextLevel($currentClass, $levels);
    if ($next === $expectedNext) {
        $stmt = $pdo->prepare('UPDATE students SET class = ? WHERE student_id = ?');
        $stmt->execute([$next, $student_id]);
        $student['class'] = $next;
        $levels = getDepartmentLevels($pdo, $student['department_id']);
        $nextLevel = getNextLevel($next, $levels);
        $upgraded = true;
        $_SESSION['upgrade_dismissed'] = time();
    }
}

$showUpgradeModal = $nextLevel && !$upgraded && (!isset($_SESSION['upgrade_dismissed']) || $_SESSION['upgrade_dismissed'] < time() - 86400);

require_once __DIR__ . '/../includes/header.php';
?>

<h2 style="margin-top:30px;">Student Dashboard</h2>
<p style="color:#888;margin-bottom:10px;"><?= htmlspecialchars($institutionName) ?></p>

<?php if ($upgraded): ?>
<div class="alert alert-success">Level upgraded to <strong><?= htmlspecialchars($nextLevel ?: $student['class']) ?></strong> successfully!</div>
<?php endif; ?>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h3>
        <p><?= htmlspecialchars($student['student_id']) ?></p>
        <p style="margin-top:5px;font-size:12px;color:#888;">
            Level: <?= htmlspecialchars($student['class']) ?> |
            Dept: <?= htmlspecialchars($student['department_name'] ?? 'N/A') ?>
        </p>
    </div>
    <div class="stat-card">
        <h3><?= $registeredCount ?></h3>
        <p>Registered Courses</p>
        <a href="/student/courses.php" class="btn btn-info btn-sm" style="margin-top:10px;">Manage Courses</a>
    </div>
    <div class="stat-card">
        <h3><?= $resultCount ?></h3>
        <p>Results Available</p>
        <a href="/student/results.php" class="btn btn-info btn-sm" style="margin-top:10px;">View Results</a>
    </div>
</div>

<div class="dashboard-stats" style="margin-top:20px;">
    <div class="stat-card" style="background:#ebf8ff;border:1px solid #bee3f8;">
        <h3 style="color:#2b6cb0;">Current Level: <?= htmlspecialchars($student['class']) ?></h3>
        <?php if ($nextLevel): ?>
        <p style="margin-top:5px;font-size:13px;color:#4a5568;">
            Next: <strong><?= htmlspecialchars($nextLevel) ?></strong>
            &middot; <a href="#" onclick="document.getElementById('levelModal').style.display='flex';return false;" style="color:#2b6cb0;">Advance Now</a>
        </p>
        <?php else: ?>
        <p style="margin-top:5px;font-size:13px;color:#4a5568;">This is the highest level available.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ($nextLevel): ?>
<div id="levelModal" class="modal-overlay"<?= $showUpgradeModal ? '' : ' style="display:none;"' ?>>
    <div class="modal-box">
        <h2>Level Upgrade Available</h2>
        <p>Your current level is <strong><?= htmlspecialchars($student['class']) ?></strong>.</p>
        <p>Would you like to advance to <strong><?= htmlspecialchars($nextLevel) ?></strong>?</p>
        <p style="font-size:13px;color:#888;margin-top:10px;">
            Your course registrations and results from previous levels will be preserved.
        </p>
        <form method="POST" style="display:inline-block;margin-top:15px;">
            <input type="hidden" name="upgrade_level" value="<?= htmlspecialchars($nextLevel) ?>">
            <button type="submit" class="btn btn-primary">Upgrade to <?= htmlspecialchars($nextLevel) ?></button>
            <button type="button" class="btn" style="background:#e0e0e0;color:#555;margin-left:8px;" onclick="closeModal()">Maybe Later</button>
        </form>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-box {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}
.modal-box h2 {
    margin: 0 0 10px;
    font-size: 20px;
    color: #1a202c;
}
.modal-box p {
    margin: 6px 0;
    font-size: 15px;
    color: #4a5568;
}
</style>

<script>
function closeModal() {
    document.getElementById('levelModal').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
