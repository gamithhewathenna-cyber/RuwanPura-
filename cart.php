<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

include __DIR__ . '/includes/header.php';
?>

<!-- ================= CART ================= -->
<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Your Cart</h1>

        <div id="cartEmpty" class="cart-empty" style="display:none;">
            Your cart is empty. <a href="<?= BASE_URL ?>gemstones.php">Browse the catalogue</a> to add gemstones.
        </div>

        <div id="cartWrap" class="cart-wrap" style="display:none;">
            <div>
                <div class="cart-items" id="cartItems"></div>
            </div>

            <div class="cart-checkout">
                <h2>Order Summary</h2>
                <div class="shipping-note">
                    <ul>
                        <li>The displayed price is for the <strong>gemstone only</strong>. Shipping charges will be calculated separately.</li>
                        <li>After you place your order, <strong>our team will contact you within a few hours</strong> to confirm the shipping charges and final total.</li>
                        <li>The <strong>final amount, including shipping</strong>, will be confirmed with you before payment.</li>
                    </ul>
                </div>
                <div class="cart-subtotal-row">
                    <span>Subtotal</span>
                    <span id="cartSubtotal">$0.00</span>
                </div>
                <a href="<?= BASE_URL ?>checkout.php" class="btn-dark" style="display:block;text-align:center;margin-top:18px;">Proceed to Checkout</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
