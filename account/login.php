<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';
maybe_show_maintenance_page();

if (!empty($_SESSION['customer_id'])) {
    header('Location: ' . BASE_URL . 'account/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (attempt_customer_login($email, $pass)) {
            header('Location: ' . BASE_URL . 'account/index.php');
            exit;
        } else {
            set_flash('error', 'Invalid email or password.');
        }
    }
}
$flash = get_flash();

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Login</h1>

        <div class="account-card" style="max-width:480px;">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:16px;"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <form method="post" class="contact-form">
                <?= csrf_field() ?>
                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="email" required autofocus>
                </div>
                <div class="form-field">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-dark" style="width:100%;">Log In</button>
            </form>

            <p style="margin-top:18px;font-size:13.5px;color:var(--muted);">
                Don't have an account? <a href="<?= BASE_URL ?>account/register.php" style="color:var(--gold);">Create one</a>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
