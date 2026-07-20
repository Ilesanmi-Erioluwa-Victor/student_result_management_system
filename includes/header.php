<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='6' fill='%23667eea'/%3E%3Ctext x='16' y='22' font-size='18' text-anchor='middle' fill='white' font-family='sans-serif'%3ES%3C/text%3E%3C/svg%3E">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php if (isLoggedIn()): ?>
<div class="header">
    <div class="header-inner">
        <h1><a href="<?= $_SESSION['role'] === 'student' ? '/student/dashboard.php' : 'dashboard.php' ?>">SRMS</a></h1>
        <div class="nav">
            <span class="username">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <?php if ($_SESSION['role'] === 'student'): ?>
                <a href="/student/dashboard.php">Dashboard</a>
                <a href="/student/courses.php">My Courses</a>
                <a href="/student/results.php">My Results</a>
            <?php else: ?>
                <a href="/dashboard.php">Dashboard</a>
                <a href="/students/list.php">Students</a>
                <a href="/subjects/list.php">Subjects</a>
                <a href="/faculties/list.php">Faculties</a>
                <a href="/departments/list.php">Departments</a>
                <a href="/results/add.php">Enter Results</a>
                <a href="/settings.php">Settings</a>
            <?php endif; ?>
            <a href="/logout.php" style="color:#f56565;">Logout</a>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="container">
