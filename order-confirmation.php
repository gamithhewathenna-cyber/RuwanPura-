<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/customer-auth.php';
require_once __DIR__ . '/includes/order-functions.php';
maybe_show_maintenance_page();
require_customer();

$orderId = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$orderId, current_customer_id()]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . 'account/orders.php');
    exit;
}

$items = get_order_items($orderId);

include __DIR__ . '/includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Order Received</h1>

        <div class="account-card" style="max-width:720px;">
            <div class="flash success" style="margin-bottom:20px;">
                Your order has been received and is currently being processed. We will confirm your final total, including shipping charges, before payment.
            </div>

            <p><strong>Order Number:</strong> <?= e($order['order_number']) ?></p>
            <p style="margin-top:6px;"><strong>Status:</strong>
                <span class="order-status-pill st-<?= e($order['order_status']) ?>"><?= e(order_status_labels()[$order['order_status']] ?? $order['order_status']) ?></span>
            </p>

            <table class="order-items-table">
                <thead><tr><th>Gemstone</th><th>Details</th><th style="text-align:right;">Price</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= e($it['product_name']) ?></td>
                        <td><?= e(($it['weight'] !== null ? $it['weight'] . ' ct' : '') . ($it['shape'] ? ' · ' . $it['shape'] : '')) ?></td>
                        <td style="text-align:right;"><?= format_money($it['line_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="order-totals-row grand">
                <span>Gemstone Total</span>
                <span><?= format_money($order['gemstone_total']) ?></span>
            </div>

            <div class="shipping-note" style="margin-top:18px;">The displayed price is for the gemstone only. Shipping charges will be calculated separately and confirmed before payment.</div>

            <a href="<?= BASE_URL ?>account/order.php?id=<?= (int) $orderId ?>" class="btn-dark" style="display:inline-block;margin-top:18px;">View Order Details</a>
            <a href="<?= BASE_URL ?>gemstones.php" class="btn-outline" style="display:inline-block;margin-top:18px;margin-left:10px;">Continue Browsing</a>
        </div>
    </div>
</section>

<script>try { localStorage.removeItem('ruwanpura_cart'); } catch (e) {}</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
