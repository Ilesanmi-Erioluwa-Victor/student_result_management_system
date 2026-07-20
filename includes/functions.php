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
