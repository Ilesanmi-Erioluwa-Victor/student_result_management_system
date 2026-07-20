<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$message = '';
$error = '';

$currentType = getInstitutionType();
$currentName = getSetting('institution_name') ?: 'My University';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institution_type = $_POST['institution_type'] ?? 'university';
    $institution_name = trim($_POST['institution_name'] ?? '');

    if (in_array($institution_type, ['university', 'polytechnic']) && $institution_name) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key = 'institution_type'");
        $stmt->execute([$institution_type]);
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key = 'institution_name'");
        $stmt->execute([$institution_name]);
        $currentType = $institution_type;
        $currentName = $institution_name;
        $message = 'Settings updated successfully.';
    } else {
        $error = 'Please fill in all fields.';
    }
}

require_once 'includes/header.php';
?>

<div class="form-wrapper">
    <h2>System Settings</h2>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label>Institution Name *</label>
            <input type="text" name="institution_name" value="<?= htmlspecialchars($currentName) ?>" required>
        </div>
        <div class="form-group">
            <label>Institution Type *</label>
            <select name="institution_type" required>
                <option value="university" <?= $currentType === 'university' ? 'selected' : '' ?>>University</option>
                <option value="polytechnic" <?= $currentType === 'polytechnic' ? 'selected' : '' ?>>Polytechnic</option>
            </select>
        </div>

        <div style="background:#f7f8fc;padding:15px;border-radius:6px;margin-bottom:20px;">
            <h4 style="margin-bottom:10px;">Current Grading System Preview</h4>
            <?php if ($currentType === 'university'): ?>
            <table style="width:100%;font-size:13px;">
                <tr><td>A</td><td>70 - 100</td><td>Excellent</td></tr>
                <tr><td>B</td><td>60 - 69</td><td>Very Good</td></tr>
                <tr><td>C</td><td>50 - 59</td><td>Good</td></tr>
                <tr><td>D</td><td>45 - 49</td><td>Fair</td></tr>
                <tr><td>E</td><td>40 - 44</td><td>Pass</td></tr>
                <tr><td>F</td><td>0 - 39</td><td>Fail</td></tr>
            </table>
            <?php else: ?>
            <table style="width:100%;font-size:13px;">
                <tr><td>A</td><td>75 - 100</td><td>Distinction</td></tr>
                <tr><td>B</td><td>60 - 74</td><td>Credit</td></tr>
                <tr><td>C</td><td>50 - 59</td><td>Pass</td></tr>
                <tr><td>F</td><td>0 - 49</td><td>Fail</td></tr>
            </table>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
        <a href="/dashboard.php" class="btn" style="background:#e0e0e0;margin-left:10px;">Back</a>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
