<?php
function getInstitutionType() {
    return getSetting('institution_type') ?: 'university';
}

function getLevels() {
    $type = getInstitutionType();
    if ($type === 'polytechnic') {
        return ['ND1', 'ND2', 'HND1', 'HND2'];
    }
    return ['100L', '200L', '300L', '400L', '500L'];
}

function computeGrade($total) {
    $type = getInstitutionType();
    if ($type === 'polytechnic') {
        if ($total >= 75) return 'A';
        if ($total >= 60) return 'B';
        if ($total >= 50) return 'C';
        return 'F';
    }
    if ($total >= 70) return 'A';
    if ($total >= 60) return 'B';
    if ($total >= 50) return 'C';
    if ($total >= 45) return 'D';
    if ($total >= 40) return 'E';
    return 'F';
}

function getGradeMeaning($grade) {
    $type = getInstitutionType();
    $meanings = [
        'university' => ['A' => 'Excellent', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Fair', 'E' => 'Pass', 'F' => 'Fail'],
        'polytechnic' => ['A' => 'Distinction', 'B' => 'Credit', 'C' => 'Pass', 'F' => 'Fail'],
    ];
    return $meanings[$type][$grade] ?? $grade;
}

function getDepartmentLevels($pdo, $department_id) {
    $institution_type = getInstitutionType();
    $defaultLevels = $institution_type === 'polytechnic'
        ? ['ND1', 'ND2', 'HND1', 'HND2']
        : ['100L', '200L', '300L', '400L', '500L'];

    if (!$department_id) return $defaultLevels;

    $stmt = $pdo->prepare('SELECT level FROM department_levels WHERE department_id = ? ORDER BY id');
    $stmt->execute([$department_id]);
    $levels = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $levels ?: $defaultLevels;
}

function getGradePoint($grade) {
    $type = getInstitutionType();
    if ($type === 'polytechnic') {
        $points = ['A' => 4, 'B' => 3, 'C' => 2, 'F' => 0];
    } else {
        $points = ['A' => 5, 'B' => 4, 'C' => 3, 'D' => 2, 'E' => 1, 'F' => 0];
    }
    return $points[$grade] ?? 0;
}

function calculateGPA($results) {
    $totalPoints = 0;
    $totalUnits = 0;
    foreach ($results as $r) {
        $unit = (int) ($r['credit_unit'] ?? 3);
        $gp = getGradePoint($r['grade']);
        $totalPoints += $gp * $unit;
        $totalUnits += $unit;
    }
    return $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0;
}
