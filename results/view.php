<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$student_id = $_GET['student_id'] ?? '';
$term = $_GET['term'] ?? '';
$session = $_GET['session'] ?? '';

if (!$student_id || !$term || !$session) {
    header('Location: /dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM students WHERE student_id = ?');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: /dashboard.php');
    exit;
}

try {
    $stmt = $pdo->prepare('
        SELECT r.*, sub.subject_name, sub.credit_unit
        FROM results r
        JOIN subjects sub ON r.subject_code = sub.subject_code
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ? AND r.term = ? AND r.session = ?
        AND (sub.class = s.class OR sub.class = \'All\')
        AND (sub.department_id IS NULL OR sub.department_id = s.department_id)
        ORDER BY sub.subject_name ASC
    ');
    $stmt->execute([$student_id, $term, $session]);
} catch (PDOException $e) {
    $stmt = $pdo->prepare('
        SELECT r.*, sub.subject_name
        FROM results r
        JOIN subjects sub ON r.subject_code = sub.subject_code
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ? AND r.term = ? AND r.session = ?
        AND (sub.class = s.class OR sub.class = \'All\')
        AND (sub.department_id IS NULL OR sub.department_id = s.department_id)
        ORDER BY sub.subject_name ASC
    ');
    $stmt->execute([$student_id, $term, $session]);
}
$results = $stmt->fetchAll();

$totalUnits = 0;
$totalGradePoints = 0;
$totalScore = 0;
foreach ($results as $r) {
    $unit = (int) ($r['credit_unit'] ?? 3);
    $gp = getGradePoint($r['grade']);
    $totalUnits += $unit;
    $totalGradePoints += $gp * $unit;
    $totalScore += $r['total_score'];
}
$gpa = $totalUnits > 0 ? round($totalGradePoints / $totalUnits, 2) : 0;

try {
    $stmt = $pdo->prepare('
        SELECT r.*, sub.credit_unit
        FROM results r
        JOIN subjects sub ON r.subject_code = sub.subject_code
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ?
        AND (sub.class = s.class OR sub.class = \'All\')
        AND (sub.department_id IS NULL OR sub.department_id = s.department_id)
    ');
    $stmt->execute([$student_id]);
} catch (PDOException $e) {
    $stmt = $pdo->prepare('
        SELECT r.*
        FROM results r
        JOIN subjects sub ON r.subject_code = sub.subject_code
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ?
        AND (sub.class = s.class OR sub.class = \'All\')
        AND (sub.department_id IS NULL OR sub.department_id = s.department_id)
    ');
    $stmt->execute([$student_id]);
}
$allResults = $stmt->fetchAll();
$cgTotalUnits = 0;
$cgTotalGradePoints = 0;
foreach ($allResults as $r) {
    $unit = (int) ($r['credit_unit'] ?? 3);
    $gp = getGradePoint($r['grade']);
    $cgTotalUnits += $unit;
    $cgTotalGradePoints += $gp * $unit;
}
$cgpa = $cgTotalUnits > 0 ? round($cgTotalGradePoints / $cgTotalUnits, 2) : 0;

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
                <th>CA</th>
                <th>Exam</th>
                <th>Total</th>
                <th>Grade</th>
                <th>CU</th>
                <th>GP</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            <?php $sn = 1; foreach ($results as $r):
                $unit = (int) ($r['credit_unit'] ?? 3);
                $gp = getGradePoint($r['grade']);
            ?>
            <tr>
                <td><?= $sn++ ?></td>
                <td style="text-align:left;"><?= htmlspecialchars($r['subject_name']) ?></td>
                <td><?= $r['ca_score'] ?></td>
                <td><?= $r['exam_score'] ?></td>
                <td><strong><?= $r['total_score'] ?></strong></td>
                <td><strong><?= htmlspecialchars($r['grade']) ?></strong></td>
                <td><?= $unit ?></td>
                <td><?= number_format($gp, 1) ?></td>
                <td><?= htmlspecialchars(getGradeMeaning($r['grade'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($results)): ?>
            <tr><td colspan="9" style="color:#888;">No results found for this selection.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($results)): ?>
    <div class="result-summary">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <p><strong>Total Score:</strong> <?= $totalScore ?> / <?= count($results) * 100 ?></p>
                <p><strong>Average:</strong> <?= count($results) > 0 ? round($totalScore / count($results), 2) : 0 ?>%</p>
            </div>
            <div>
                <p><strong>Total Credit Units:</strong> <?= $totalUnits ?></p>
                <p><strong>GPA:</strong> <?= number_format($gpa, 2) ?> / <?= getInstitutionType() === 'polytechnic' ? '4.00' : '5.00' ?></p>
                <p><strong>CGPA:</strong> <?= number_format($cgpa, 2) ?> / <?= getInstitutionType() === 'polytechnic' ? '4.00' : '5.00' ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-actions mt-20 no-print">
        <button id="print-btn" class="btn btn-info">Print Result</button>
        <a href="/results/student.php?student_id=<?= urlencode($student_id) ?>" class="btn btn-success">View All Results</a>
        <a href="/dashboard.php" class="btn" style="background:#e0e0e0;color:#555;">Back</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
