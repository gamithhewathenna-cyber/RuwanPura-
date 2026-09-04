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
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if ($password !== $confirm) {
            set_flash('error', 'Password and confirmation do not match.');
        } else {
            $result = register_customer($_POST);
            if (!empty($result['errors'])) {
                set_flash('error', implode(' ', $result['errors']));
            } else {
                $row = db()->prepare("SELECT * FROM customers WHERE id = ?");
                $row->execute([$result['id']]);
                $c = $row->fetch();
                $_SESSION['customer_id']    = $c['id'];
                $_SESSION['customer_name']  = $c['full_name'];
                $_SESSION['customer_email'] = $c['email'];
                header('Location: ' . BASE_URL . 'account/index.php');
                exit;
            }
        }
    }
}
$flash = get_flash();
$old   = $_POST ?? [];
$showPassword = true;

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Create Account</h1>

        <div class="account-card" style="max-width:640px;">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:16px;"><?= e($flash['msg']) ?></div>
            <?php endif; ?>

            <form method="post" class="contact-form">
                <?= csrf_field() ?>
                <?php include __DIR__ . '/../includes/customer-register-form.php'; ?>
                <button type="submit" class="btn-dark" style="width:100%;">Create Account</button>
            </form>

            <p style="margin-top:18px;font-size:13.5px;color:var(--muted);">
                Already have an account? <a href="<?= BASE_URL ?>account/login.php" style="color:var(--gold);">Log in</a>
            </p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
