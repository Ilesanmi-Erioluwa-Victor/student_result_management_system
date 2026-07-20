<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php if (isLoggedIn()): ?>
<div class="header">
    <div class="header-inner">
        <h1><a href="dashboard.php">SRMS</a></h1>
        <div class="nav">
            <span class="username">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="dashboard.php">Dashboard</a>
            <a href="students/list.php">Students</a>
            <a href="subjects/list.php">Subjects</a>
            <a href="results/add.php">Enter Results</a>
            <a href="settings.php">Settings</a>
            <a href="logout.php" style="color:#f56565;">Logout</a>
        </div>
    </div>
</div>
<?php endif; ?>
<div class="container">
