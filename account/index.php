<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';
maybe_show_maintenance_page();
require_customer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        save_customer_profile(current_customer_id(), $_POST);
        set_flash('success', 'Your details have been updated.');
    }
    header('Location: ' . BASE_URL . 'account/index.php');
    exit;
}

$flash = get_flash();
$old   = current_customer();
$showPassword = false;

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">My Account</h1>

        <div class="account-tabs">
            <a href="<?= BASE_URL ?>account/index.php" class="account-tab active">Profile</a>
            <a href="<?= BASE_URL ?>account/orders.php" class="account-tab">Order History</a>
        </div>

        <div class="account-card" style="max-width:640px;">
            <h2>Profile &amp; Addresses</h2>
            <p class="account-card-sub">Login email: <?= e($old['email']) ?> (not editable)</p>

            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:16px;"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <form method="post" class="contact-form">
                <?= csrf_field() ?>
                <?php include __DIR__ . '/../includes/customer-register-form.php'; ?>
                <button type="submit" class="btn-dark">Save Changes</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
