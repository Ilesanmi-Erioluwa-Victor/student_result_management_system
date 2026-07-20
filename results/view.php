<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$student_id = $_GET['student_id'] ?? '';
$term = $_GET['term'] ?? '';
$session = $_GET['session'] ?? '';

if (!$student_id || !$term || !$session) {
    header('Location: ../dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: ../dashboard.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT r.*, sub.subject_name
    FROM results r
    JOIN subjects sub ON r.subject_code = sub.subject_code
    WHERE r.student_id = ? AND r.term = ? AND r.session = ?
    ORDER BY sub.subject_name ASC
');
$stmt->execute([$student_id, $term, $session]);
$results = $stmt->fetchAll();

$totalObtained = 0;
$totalPossible = 0;
foreach ($results as $r) {
    $totalObtained += $r['total_score'];
    $totalPossible += 100;
}
$average = count($results) > 0 ? round($totalObtained / count($results), 2) : 0;
$institutionName = getSetting('institution_name');

require_once __DIR__ . '/../includes/header.php';
?>

<div class="result-card" id="resultCard">
    <h2><?= htmlspecialchars($institutionName) ?></h2>
    <p class="subtitle">STUDENT RESULT SHEET</p>
    <p class="subtitle"><?= htmlspecialchars($student['student_id']) ?> - <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></p>
    <p class="subtitle">Level: <?= htmlspecialchars($student['class']) ?> | <?= htmlspecialchars($term) ?> | Session: <?= htmlspecialchars($session) ?></p>

    <table class="result-table">
        <thead>
            <tr>
                <th>S/N</th>
                <th>Subject</th>
                <th>CA Score</th>
                <th>Exam Score</th>
                <th>Total</th>
                <th>Grade</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; foreach ($results as $r): ?>
            <tr>
                <td><?= $sn++ ?></td>
                <td style="text-align:left;"><?= htmlspecialchars($r['subject_name']) ?></td>
                <td><?= $r['ca_score'] ?></td>
                <td><?= $r['exam_score'] ?></td>
                <td><strong><?= $r['total_score'] ?></strong></td>
                <td><strong><?= htmlspecialchars($r['grade']) ?></strong></td>
                <td><?= htmlspecialchars(getGradeMeaning($r['grade'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($results)): ?>
            <tr><td colspan="7" style="color:#888;">No results found for this selection.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($results)): ?>
    <div class="result-summary">
        <p><strong>Total Score:</strong> <?= $totalObtained ?> / <?= $totalPossible ?></p>
        <p><strong>Average:</strong> <?= $average ?>%</p>
        <p><strong>Level:</strong> <?= htmlspecialchars($student['class']) ?></p>
    </div>
    <?php endif; ?>

    <div class="text-center mt-20 no-print">
        <button id="print-btn" class="btn btn-info">Print Result</button>
        <a href="../dashboard.php" class="btn" style="background:#e0e0e0;">Back to Dashboard</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
