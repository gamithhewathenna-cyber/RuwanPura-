<?php
$page_title = 'My Account';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

$adminId = current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_email') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_flash('error', 'Please enter a valid email address.');
            } else {
                // ensure uniqueness
                $chk = db()->prepare("SELECT COUNT(*) FROM admins WHERE email=? AND id<>?");
                $chk->execute([$email, $adminId]);
                if ($chk->fetchColumn() > 0) {
                    set_flash('error', 'That email is already in use.');
                } else {
                    $stmt = db()->prepare("UPDATE admins SET name=?, email=? WHERE id=?");
                    $stmt->execute([$name, $email, $adminId]);
                    $_SESSION['admin_name']  = $name;
                    $_SESSION['admin_email'] = $email;
                    set_flash('success', 'Account details updated.');
                }
            }
        }
        elseif ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $row = db()->prepare("SELECT password_hash FROM admins WHERE id=?");
            $row->execute([$adminId]);
            $hash = $row->fetchColumn();

            if (!password_verify($current, $hash)) {
                set_flash('error', 'Your current password is incorrect.');
            } elseif (strlen($new) < 6) {
                set_flash('error', 'New password must be at least 6 characters.');
            } elseif ($new !== $confirm) {
                set_flash('error', 'New password and confirmation do not match.');
            } else {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                db()->prepare("UPDATE admins SET password_hash=? WHERE id=?")->execute([$newHash, $adminId]);
                set_flash('success', 'Password changed successfully.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$row = db()->prepare("SELECT * FROM admins WHERE id=?");
$row->execute([$adminId]);
$admin = $row->fetch();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <h2>Account Details</h2>
    <p class="card-sub">Your name and login email address.</p>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_email">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="<?= e($admin['name']) ?>" required>
        </div>
        <div class="form-group">
            <label>Login Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($admin['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Details</button>
    </form>
</div>

<div class="card">
    <h2>Change Password</h2>
    <p class="card-sub">Choose a strong password you don't use elsewhere.</p>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label>Current Password</label>
            <div class="password-field">
                <input type="password" name="current_password" class="form-control" required>
                <button type="button" class="password-toggle" aria-label="Show password" tabindex="-1">
                    <svg class="eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a19.7 19.7 0 0 1 5.06-5.94M9.9 4.24A10.4 10.4 0 0 1 12 4c7 0 11 8 11 8a19.7 19.7 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                </button>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>New Password</label>
                <div class="password-field">
                    <input type="password" name="new_password" class="form-control" required>
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
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
