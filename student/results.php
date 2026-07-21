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
    SELECT s.*, d.name AS department_name
    FROM students s
    LEFT JOIN departments d ON s.department_id = d.id
    WHERE s.student_id = ?
');
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    header('Location: /logout.php');
    exit;
}

$stmt = $pdo->prepare('SELECT DISTINCT term, session FROM results WHERE student_id = ? ORDER BY session ASC, term ASC');
$stmt->execute([$student_id]);
$semesters = $stmt->fetchAll();

$institutionName = getSetting('institution_name');
$scale = getInstitutionType() === 'polytechnic' ? '4.00' : '5.00';

function fetchStudentResults($pdo, $student_id, $term, $session) {
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
    return $stmt->fetchAll();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="result-card">
    <h2><?= htmlspecialchars($institutionName) ?></h2>
    <p class="subtitle">MY ACADEMIC RECORD</p>
    <p class="subtitle"><?= htmlspecialchars($student['student_id']) ?> — <?= htmlspecialchars($student['last_name'] . ', ' . $student['first_name']) ?></p>
    <p class="subtitle">Level: <?= htmlspecialchars($student['class']) ?> | Dept: <?= htmlspecialchars($student['department_name'] ?? 'N/A') ?></p>

    <?php if (empty($semesters)): ?>
    <p style="text-align:center;color:#888;padding:40px 0;">No results published yet.</p>
    <?php else:
        $cgTotalUnits = 0;
        $cgTotalGradePoints = 0;
    ?>

    <?php foreach ($semesters as $sem):
        $results = fetchStudentResults($pdo, $student_id, $sem['term'], $sem['session']);
        $totalUnits = 0; $totalGradePoints = 0; $totalScore = 0;
        foreach ($results as $r) {
            $unit = (int) ($r['credit_unit'] ?? 3);
            $gp = getGradePoint($r['grade']);
            $totalUnits += $unit;
            $totalGradePoints += $gp * $unit;
            $totalScore += $r['total_score'];
        }
        $gpa = $totalUnits > 0 ? round($totalGradePoints / $totalUnits, 2) : 0;
        $cgTotalUnits += $totalUnits;
        $cgTotalGradePoints += $totalGradePoints;
        $cgpa = $cgTotalUnits > 0 ? round($cgTotalGradePoints / $cgTotalUnits, 2) : 0;
    ?>
    <div style="background:#f7f8fc;padding:10px 15px;border-radius:6px;margin:20px 0 10px;">
        <strong><?= htmlspecialchars($sem['term']) ?> — <?= htmlspecialchars($sem['session']) ?></strong>
        <span style="float:right;">GPA: <strong><?= number_format($gpa, 2) ?></strong> / <?= $scale ?></span>
    </div>
    <table class="result-table" style="margin-top:0;">
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
        </tbody>
    </table>
    <?php endforeach; ?>

    <div class="result-summary">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <p><strong>Total Credit Units:</strong> <?= $cgTotalUnits ?></p>
                <p><strong>Total Grade Points:</strong> <?= number_format($cgTotalGradePoints, 2) ?></p>
            </div>
            <div>
                <p><strong>Semesters:</strong> <?= count($semesters) ?></p>
                <p><strong>CGPA:</strong> <?= number_format($cgpa, 2) ?> / <?= $scale ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-actions mt-20 no-print">
        <button id="print-btn" class="btn btn-info">Print</button>
        <a href="/student/dashboard.php" class="btn" style="background:#e0e0e0;color:#555;">Back</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
