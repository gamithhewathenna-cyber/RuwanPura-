<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';
maybe_show_maintenance_page();
require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $row = db()->prepare("SELECT password_hash FROM customers WHERE id = ?");
        $row->execute([current_customer_id()]);
        $hash = $row->fetchColumn();

        if (!password_verify($current, $hash)) {
            set_flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            set_flash('error', 'New password and confirmation do not match.');
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            db()->prepare("UPDATE customers SET password_hash = ? WHERE id = ?")->execute([$newHash, current_customer_id()]);
            set_flash('success', 'Password changed successfully.');
        }
    }
    header('Location: ' . BASE_URL . 'account/change-password.php');
    exit;
}

$flash = get_flash();

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">My Account</h1>

        <div class="account-tabs">
            <a href="<?= BASE_URL ?>account/index.php" class="account-tab">Profile</a>
            <a href="<?= BASE_URL ?>account/orders.php" class="account-tab">Order History</a>
            <a href="<?= BASE_URL ?>account/change-password.php" class="account-tab active">Change Password</a>
            <a href="<?= BASE_URL ?>account/logout.php" class="account-tab" style="margin-left:auto;">Logout</a>
        </div>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:16px;"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <div class="account-card" style="max-width:640px;">
            <h2>Change Password</h2>
            <p class="account-card-sub">Choose a strong password you don't use elsewhere.</p>

            <form method="post" class="contact-form" autocomplete="off">
                <?= csrf_field() ?>
                <div class="form-field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-row-2">
                    <div class="form-field">
                        <label>New Password</label>
                        <input type="password" name="new_password" minlength="6" required autocomplete="new-password">
                    </div>
                    <div class="form-field">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="6" required autocomplete="new-password">
                    </div>
                </div>
                <button type="submit" class="btn-dark">Change Password</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
