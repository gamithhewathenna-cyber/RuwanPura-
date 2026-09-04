<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';
require_once __DIR__ . '/../includes/order-functions.php';
maybe_show_maintenance_page();
require_customer();

$orderId = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        if ($action === 'mark_payment_sent') {
            $check = db()->prepare("SELECT order_status FROM orders WHERE id = ? AND customer_id = ?");
            $check->execute([$orderId, current_customer_id()]);
            $status = $check->fetchColumn();
            if ($status === 'shipping_quoted') {
                $ref = trim($_POST['payment_reference'] ?? '');
                db()->prepare("UPDATE orders SET order_status = 'payment_pending', payment_reference = ? WHERE id = ?")
                    ->execute([$ref, $orderId]);
                set_flash('success', 'Thanks — we\'ll verify your payment and update your order status shortly.');
            }
        }
    }
    header('Location: ' . BASE_URL . 'account/order.php?id=' . $orderId);
    exit;
}

$stmt = db()->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$orderId, current_customer_id()]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . 'account/orders.php');
    exit;
}

$items        = get_order_items($orderId);
$statusLabels = order_status_labels();
$flash        = get_flash();

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Order <?= e($order['order_number']) ?></h1>

        <?php if ($flash): ?>
            <div class="flash <?= e($flash['type']) ?>" style="margin-bottom:16px;"><?= e($flash['msg']) ?></div>
        <?php endif; ?>

        <div class="account-card" style="max-width:720px;">
            <p><strong>Status:</strong>
                <span class="order-status-pill st-<?= e($order['order_status']) ?>"><?= e($statusLabels[$order['order_status']] ?? $order['order_status']) ?></span>
            </p>
            <p style="margin-top:6px;color:var(--muted);font-size:13.5px;">Placed on <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>

            <table class="order-items-table">
                <thead><tr><th>Gemstone</th><th>Details</th><th>Qty</th><th style="text-align:right;">Price</th></tr></thead>
                <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= e($it['product_name']) ?></td>
                        <td><?= e(($it['weight'] !== null ? $it['weight'] . ' ct' : '') . ($it['shape'] ? ' · ' . $it['shape'] : '')) ?></td>
                        <td><?= (int) $it['quantity'] ?></td>
                        <td style="text-align:right;"><?= format_money($it['line_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="order-totals-row"><span>Gemstone Total</span><span><?= format_money($order['gemstone_total']) ?></span></div>
            <?php if ($order['shipping_charge'] !== null): ?>
                <div class="order-totals-row"><span>Shipping Charge</span><span><?= format_money($order['shipping_charge']) ?></span></div>
                <div class="order-totals-row grand"><span>Final Total</span><span><?= format_money($order['final_total']) ?></span></div>
            <?php else: ?>
                <div class="shipping-note" style="margin-top:14px;">The displayed price is for the gemstone only. Shipping charges will be calculated separately and confirmed before payment.</div>
            <?php endif; ?>

            <?php if ($order['order_status'] === 'shipping_quoted' && $order['shipping_charge'] !== null):
                $bankLines = [];
                if (c('bank_name')) $bankLines[] = 'Bank: ' . c('bank_name');
                if (c('bank_account_name')) $bankLines[] = 'Account Name: ' . c('bank_account_name');
                if (c('bank_account_number')) $bankLines[] = 'Account Number: ' . c('bank_account_number');
                if (c('bank_branch')) $bankLines[] = 'Branch: ' . c('bank_branch');
                if (c('bank_swift')) $bankLines[] = 'SWIFT / BIC: ' . c('bank_swift');
            ?>
                <div class="section-divider" style="border-top:1px solid var(--line);margin:22px 0;"></div>
                <h4 style="margin-bottom:10px;font-size:15px;">Bank Transfer Instructions</h4>
                <p style="font-size:13.5px;color:var(--muted);white-space:pre-line;"><?= e_nl(implode("\n", $bankLines)) ?></p>

                <form method="post" class="contact-form" style="margin-top:18px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="mark_payment_sent">
                    <div class="form-field">
                        <label>Payment Reference <span class="hint">(optional)</span></label>
                        <input type="text" name="payment_reference" placeholder="e.g. bank transaction reference">
                    </div>
                    <button type="submit" class="btn-dark">I've Sent the Transfer</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
