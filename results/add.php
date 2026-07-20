<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';
$error = '';

$students = $pdo->query('SELECT * FROM students ORDER BY last_name ASC')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY subject_name ASC')->fetchAll();
$institutionType = getInstitutionType();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $entries = $_POST['entries'] ?? [];

    if (!empty($entries)) {
        $term = $_POST['term'];
        $session = $_POST['session'];
        $successCount = 0;

        foreach ($entries as $entry) {
            $subject_code = $entry['subject_code'];
            $ca_score = $entry['ca_score'] ?? 0;
            $exam_score = $entry['exam_score'] ?? 0;
            $total_score = $ca_score + $exam_score;
            $grade = computeGrade($total_score);

            try {
                $stmt = $pdo->prepare('
                    INSERT INTO results (student_id, subject_code, term, session, ca_score, exam_score, total_score, grade)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON CONFLICT (student_id, subject_code, term, session)
                    DO UPDATE SET ca_score = EXCLUDED.ca_score, exam_score = EXCLUDED.exam_score,
                                  total_score = EXCLUDED.total_score, grade = EXCLUDED.grade
                ');
                $stmt->execute([$student_id, $subject_code, $term, $session, $ca_score, $exam_score, $total_score, $grade]);
                $successCount++;
            } catch (PDOException $e) {
                $error = 'Error saving result for ' . $subject_code . ': ' . $e->getMessage();
            }
        }

        if (!$error) {
            $message = "Results saved successfully for $successCount subject(s).";
        }
    } else {
        $error = 'Please add at least one subject score.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-wrapper" style="max-width: 900px;">
    <h2>Enter Student Results</h2>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" id="resultForm">
        <div class="form-row">
            <div class="form-group">
                <label>Student *</label>
                <select name="student_id" id="studentSelect" required>
                    <option value="">Select Student</option>
                    <?php foreach ($students as $s): ?>
                    <option value="<?= htmlspecialchars($s['student_id']) ?>">
                        <?= htmlspecialchars($s['student_id'] . ' - ' . $s['last_name'] . ', ' . $s['first_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Level</label>
                <input type="text" id="studentClass" readonly style="background:#f0f2f5;">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Semester / Term *</label>
                <select name="term" required>
                    <option value="">Select</option>
                    <option value="First Semester">First Semester</option>
                    <option value="Second Semester">Second Semester</option>
                </select>
            </div>
            <div class="form-group">
                <label>Session *</label>
                <select name="session" required>
                    <option value="">Select Session</option>
                    <option value="2023/2024">2023/2024</option>
                    <option value="2024/2025">2024/2025</option>
                    <option value="2025/2026">2025/2026</option>
                    <option value="2026/2027">2026/2027</option>
                </select>
            </div>
        </div>

        <div class="table-container" style="margin-top:20px;">
            <div class="table-header">
                <h3>Subject Scores</h3>
            </div>
            <table id="scoresTable">
                <thead>
                    <tr>
                        <th style="width:40%;">Subject</th>
                        <th style="width:20%;">CA Score (40)</th>
                        <th style="width:20%;">Exam Score (60)</th>
                        <th style="width:20%;">Total</th>
                    </tr>
                </thead>
                <tbody id="scoresBody">
                    <?php foreach ($subjects as $subj): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($subj['subject_name']) ?>
                            <input type="hidden" name="entries[<?= $subj['id'] ?>][subject_code]" value="<?= htmlspecialchars($subj['subject_code']) ?>">
                        </td>
                        <td>
                            <input type="number" name="entries[<?= $subj['id'] ?>][ca_score]" class="ca-score" min="0" max="40" step="0.01" style="width:80px;">
                        </td>
                        <td>
                            <input type="number" name="entries[<?= $subj['id'] ?>][exam_score]" class="exam-score" min="0" max="60" step="0.01" style="width:80px;">
                        </td>
                        <td class="total-display">-</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-primary mt-20">Save Results</button>
        <a href="../dashboard.php" class="btn" style="background:#e0e0e0;margin-left:10px;">Cancel</a>
    </form>
</div>

<script>
const students = <?= json_encode($students) ?>;

document.getElementById('studentSelect').addEventListener('change', function() {
    const selected = students.find(s => s.student_id === this.value);
    document.getElementById('studentClass').value = selected ? selected.class : '';
});

document.querySelectorAll('.ca-score, .exam-score').forEach(function(input) {
    input.addEventListener('input', function() {
        const row = this.closest('tr');
        const ca = parseFloat(row.querySelector('.ca-score').value) || 0;
        const exam = parseFloat(row.querySelector('.exam-score').value) || 0;
        const total = ca + exam;
        row.querySelector('.total-display').textContent = total.toFixed(2);
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
