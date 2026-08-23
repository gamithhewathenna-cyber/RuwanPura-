<?php
require_once __DIR__ . '/auth.php';

// already logged in?
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (attempt_login($email, $pass)) {
            header('Location: ' . BASE_URL . 'admin/index.php');
            exit;
        } else {
            set_flash('error', 'Invalid email or password.');
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
    <title>Admin Login — <?= e(setting('site_name', 'Ruwanpura Gems')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <div class="logo">
            <img src="<?= logo_url() ?>" alt="Logo" onerror="this.style.display='none'">
        </div>
        <h1>Admin Panel</h1>
        <p class="sub">Sign in to manage your website</p>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus
                       value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <p style="text-align:center;margin-top:18px;">
            <a href="<?= BASE_URL ?>admin/forgot-password.php" style="font-size:13px;color:#6b7280;">Forgot password?</a>
        </p>
    </div>
</div>
</body>
</html>
