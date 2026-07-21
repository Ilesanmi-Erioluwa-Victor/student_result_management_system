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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_level'])) {
    $newLevel = $_POST['update_level'];
    if (in_array($newLevel, $levels)) {
        $stmt = $pdo->prepare('UPDATE students SET class = ? WHERE student_id = ?');
        $stmt->execute([$newLevel, $student_id]);
        $student['class'] = $newLevel;
        $levels = getDepartmentLevels($pdo, $student['department_id']);
        $nextLevel = getNextLevel($newLevel, $levels);
        $upgraded = true;
        $_SESSION['upgrade_dismissed'] = time();
    }
}

$hasLevelOptions = count($levels) > 0;

$showUpgradeModal = $hasLevelOptions && !$upgraded && (!isset($_SESSION['upgrade_dismissed']) || $_SESSION['upgrade_dismissed'] < time() - 86400);

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
        <p style="margin-top:5px;font-size:13px;color:#4a5568;">
            <?php if ($nextLevel): ?>
            Next: <strong><?= htmlspecialchars($nextLevel) ?></strong> &middot;
            <?php endif; ?>
            <a href="#" onclick="document.getElementById('levelModal').style.display='flex';return false;" style="color:#2b6cb0;">Update Level</a>
        </p>
    </div>
</div>

<?php if ($hasLevelOptions): ?>
<div id="levelModal" class="modal-overlay"<?= $showUpgradeModal ? '' : ' style="display:none;"' ?>>
    <div class="modal-box">
        <h2>Update Your Level Now</h2>
        <p style="margin-bottom:15px;">Select your current level from the options below.</p>
        <p style="font-size:13px;color:#888;margin-bottom:15px;">
            Course registrations and results from previous levels will be preserved.
        </p>
        <form method="POST" style="margin-top:10px;">
            <?php foreach ($levels as $level): ?>
            <label class="level-option<?= $level === $student['class'] ? ' selected' : '' ?>">
                <input type="radio" name="update_level" value="<?= htmlspecialchars($level) ?>" <?= $level === $student['class'] ? 'checked' : '' ?>>
                <?= htmlspecialchars($level) ?>
            </label>
            <?php endforeach; ?>
            <div style="margin-top:15px;display:flex;gap:8px;justify-content:center;">
                <button type="submit" class="btn" style="background:#667eea;color:#fff;">Update Level</button>
                <button type="button" class="btn" style="background:#e0e0e0;color:#555;" onclick="closeModal()">Maybe Later</button>
            </div>
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
.level-option {
    display: block;
    padding: 10px 15px;
    margin: 6px 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
    color: #4a5568;
    text-align: left;
    transition: all .15s;
}
.level-option:hover {
    background: #f7fafc;
    border-color: #667eea;
}
.level-option.selected {
    background: #ebf4ff;
    border-color: #667eea;
    color: #2b6cb0;
    font-weight: 600;
}
.level-option input[type="radio"] {
    margin-right: 10px;
}
</style>

<script>
function closeModal() {
    document.getElementById('levelModal').style.display = 'none';
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
