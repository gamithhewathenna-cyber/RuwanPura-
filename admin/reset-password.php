<?php
require_once __DIR__ . '/auth.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$valid = false;
$admin = null;

if ($token) {
    $stmt = db()->prepare("SELECT * FROM admins WHERE reset_token=? AND reset_expires > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $admin = $stmt->fetch();
    $valid = (bool)$admin;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    if (verify_csrf()) {
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($new) < 6) {
            set_flash('error', 'Password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            set_flash('error', 'Passwords do not match.');
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            db()->prepare("UPDATE admins SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE id=?")
                ->execute([$hash, $admin['id']]);
            set_flash('success', 'Password reset successfully. You can now log in.');
            header('Location: ' . BASE_URL . 'admin/login.php');
            exit;
        }
    }
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h1>Set New Password</h1>
        <p class="sub">Choose a new password for your account</p>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <?php if (!$valid): ?>
            <div class="flash error">This reset link is invalid or has expired.</div>
            <p style="text-align:center;margin-top:18px;">
                <a href="<?= BASE_URL ?>admin/forgot-password.php" style="font-size:13px;color:#6b7280;">Request a new link</a>
            </p>
        <?php else: ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
