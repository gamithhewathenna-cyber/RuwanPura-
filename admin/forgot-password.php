<?php
require_once __DIR__ . '/auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . BASE_URL . 'admin/index.php');
    exit;
}

$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $email = trim($_POST['email'] ?? '');
        $stmt = db()->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            db()->prepare("UPDATE admins SET reset_token=?, reset_expires=? WHERE id=?")
                ->execute([$token, $expires, $admin['id']]);

            // Build absolute reset URL
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'];
            $resetLink = $scheme . '://' . $host . BASE_URL . 'admin/reset-password.php?token=' . $token;

            // Attempt to email
            $subject = 'Password Reset — ' . setting('site_name', 'Ruwanpura Gems');
            $message = "You requested a password reset.\n\nClick the link below to set a new password (valid for 1 hour):\n\n$resetLink\n\nIf you did not request this, you can ignore this email.";
            $headers = 'From: ' . setting('admin_email', 'no-reply@' . $host);
            @mail($email, $subject, $message, $headers);

            set_flash('success', 'A reset link has been generated. Check your email, or use the link shown below.');
        } else {
            set_flash('error', 'No account found with that email address.');
        }
    }
    // keep resetLink to display; do not redirect so we can show it
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-card">
        <h1>Reset Password</h1>
        <p class="sub">Enter your email to receive a reset link</p>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <?php if ($resetLink): ?>
            <div class="flash success" style="word-break:break-all;font-size:12px;">
                <div>
                    <strong>Reset link:</strong><br>
                    <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
                </div>
            </div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>

        <p style="text-align:center;margin-top:18px;">
            <a href="<?= BASE_URL ?>admin/login.php" style="font-size:13px;color:#6b7280;">← Back to login</a>
        </p>
    </div>
</div>
</body>
</html>
