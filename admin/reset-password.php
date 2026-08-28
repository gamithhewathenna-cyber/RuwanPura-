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
                    <div class="password-field">
                        <input type="password" name="new_password" class="form-control" required autofocus>
                        <button type="button" class="password-toggle" aria-label="Show password" tabindex="-1">
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.7 19.7 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" class="form-control" required>
                        <button type="button" class="password-toggle" aria-label="Show password" tabindex="-1">
                            <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.7 19.7 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.password-toggle');
    if (!btn) return;
    var wrap = btn.closest('.password-field');
    var input = wrap ? wrap.querySelector('input') : null;
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    btn.classList.toggle('is-visible', show);
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});
</script>
</body>
</html>
