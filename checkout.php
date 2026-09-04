<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/customer-auth.php';
require_once __DIR__ . '/includes/order-functions.php';
require_once __DIR__ . '/includes/emails.php';
maybe_show_maintenance_page();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        header('Location: ' . BASE_URL . 'checkout.php');
        exit;
    }
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (attempt_customer_login($email, $pass)) {
            set_flash('success', 'Welcome back, ' . $_SESSION['customer_name'] . '.');
        } else {
            set_flash('error', 'Invalid email or password.');
        }
        header('Location: ' . BASE_URL . 'checkout.php');
        exit;
    }

    if ($action === 'register') {
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        if ($password !== $confirm) {
            set_flash('error', 'Password and confirmation do not match.');
            header('Location: ' . BASE_URL . 'checkout.php');
            exit;
        }
        $result = register_customer($_POST);
        if (!empty($result['errors'])) {
            set_flash('error', implode(' ', $result['errors']));
            header('Location: ' . BASE_URL . 'checkout.php');
            exit;
        }
        $row = db()->prepare("SELECT * FROM customers WHERE id = ?");
        $row->execute([$result['id']]);
        $newCustomer = $row->fetch();
        $_SESSION['customer_id']    = $newCustomer['id'];
        $_SESSION['customer_name']  = $newCustomer['full_name'];
        $_SESSION['customer_email'] = $newCustomer['email'];
        header('Location: ' . BASE_URL . 'checkout.php');
        exit;
    }

    if ($action === 'place_order') {
        if (empty($_SESSION['customer_id'])) {
            set_flash('error', 'Please log in or create an account to place your order.');
            header('Location: ' . BASE_URL . 'checkout.php');
            exit;
        }
        $customerId = current_customer_id();
        save_customer_profile($customerId, $_POST);

        $cartItems = json_decode($_POST['cart_data'] ?? '[]', true);
        if (!is_array($cartItems)) $cartItems = [];

        $result = place_order($customerId, $cartItems, $_POST, 'bank_transfer');
        if (!empty($result['errors'])) {
            set_flash('error', implode(' ', $result['errors']));
            header('Location: ' . BASE_URL . 'checkout.php');
            exit;
        }

        $orderRow = db()->prepare("SELECT * FROM orders WHERE id = ?");
        $orderRow->execute([$result['id']]);
        $order = $orderRow->fetch();
        $items = get_order_items($result['id']);
        send_order_placed_email($order, $items);

        header('Location: ' . BASE_URL . 'order-confirmation.php?id=' . $result['id']);
        exit;
    }
}

$flash    = get_flash();
$customer = current_customer();

include __DIR__ . '/includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Checkout</h1>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:20px;"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <div id="checkoutEmpty" class="cart-empty" style="display:none;">
            Your cart is empty. <a href="<?= BASE_URL ?>gemstones.php">Browse the catalogue</a> to add gemstones.
        </div>

        <div id="checkoutWrap" class="cart-wrap" style="display:none;">
            <!-- Order Summary -->
            <div class="account-card">
                <h2>Order Summary</h2>
                <div class="shipping-note">
                    <ul>
                        <li>The displayed price is for the <strong>gemstone only</strong>. Shipping charges will be calculated separately.</li>
                        <li>After you place your order, <strong>our team will contact you within a few hours</strong> to confirm the shipping charges and final total.</li>
                        <li>The <strong>final amount, including shipping</strong>, will be confirmed with you before payment.</li>
                    </ul>
                </div>
                <div class="cart-items" id="cartItems"></div>
                <div class="cart-subtotal-row" style="margin-top:16px;">
                    <span>Gemstone Total</span>
                    <span id="cartSubtotal">$0.00</span>
                </div>
            </div>

            <!-- Account + Details -->
            <div class="account-card">
                <?php if ($customer): ?>
                    <h2>Your Details</h2>
                    <p class="account-card-sub">Logged in as <strong><?= e($customer['full_name']) ?></strong> (<?= e($customer['email']) ?>)</p>

                    <form method="post" id="placeOrderForm" class="contact-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="place_order">
                        <input type="hidden" name="cart_data" id="cartDataInput" value="[]">

                        <?php $old = $customer; $showPassword = false; include __DIR__ . '/includes/customer-register-form.php'; ?>

                        <h4 style="margin:18px 0 10px;font-size:15px;">Payment Method</h4>
                        <label class="filter-check" style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
                            <input type="radio" name="payment_method" value="bank_transfer" checked disabled>
                            Bank Transfer
                        </label>

                        <button type="submit" class="btn-dark" style="width:100%;">Place Order</button>
                    </form>
                <?php else: ?>
                    <div class="account-tabs">
                        <button type="button" class="account-tab active" data-tab="login">Login</button>
                        <button type="button" class="account-tab" data-tab="register">Register &amp; Continue</button>
                    </div>

                    <div class="account-tab-panel active" id="tab-login">
                        <p class="account-card-sub">Already have an account? Sign in to continue.</p>
                        <form method="post" class="contact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="login">
                            <div class="form-field">
                                <label>Email Address</label>
                                <input type="email" name="email" required>
                            </div>
                            <div class="form-field">
                                <label>Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <button type="submit" class="btn-dark">Log In</button>
                        </form>
                    </div>

                    <div class="account-tab-panel" id="tab-register">
                        <p class="account-card-sub">Create an account to place your order and track it afterwards.</p>
                        <form method="post" class="contact-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="register">
                            <?php $old = $_POST ?? []; $showPassword = true; include __DIR__ . '/includes/customer-register-form.php'; ?>
                            <button type="submit" class="btn-dark">Create Account &amp; Continue</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/checkout.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
