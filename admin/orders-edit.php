<?php
$page_title = 'Order Details';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('orders');
require_once __DIR__ . '/../includes/order-functions.php';
require_once __DIR__ . '/../includes/emails.php';

$id = (int) ($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'set_shipping_charge') {
            set_order_shipping_charge($id, $_POST['shipping_charge'] ?? 0);
            set_flash('success', 'Shipping charge saved.');
        }
        elseif ($action === 'send_shipping_email') {
            $orderRow = db()->prepare("SELECT * FROM orders WHERE id = ?");
            $orderRow->execute([$id]);
            $order = $orderRow->fetch();
            if ($order && $order['shipping_charge'] !== null) {
                $ok = send_shipping_confirmation_email($order, get_order_items($id));
                set_flash($ok ? 'success' : 'error', $ok ? 'Final total email sent to the customer.' : 'Could not send the email. Please try again.');
            } else {
                set_flash('error', 'Set a shipping charge before sending the final total email.');
            }
        }
        elseif ($action === 'update_status') {
            $newStatus = $_POST['order_status'] ?? '';
            if (apply_order_status_transition($id, $newStatus)) {
                set_flash('success', 'Order status updated.');
            } else {
                set_flash('error', 'Could not update the order status.');
            }
        }
        elseif ($action === 'send_payment_confirmation_email') {
            $orderRow = db()->prepare("SELECT * FROM orders WHERE id = ?");
            $orderRow->execute([$id]);
            $order = $orderRow->fetch();
            if ($order && in_array($order['order_status'], ['payment_verified', 'order_confirmed', 'shipped', 'completed'], true)) {
                $ok = send_payment_confirmation_email($order, get_order_items($id));
                set_flash($ok ? 'success' : 'error', $ok ? 'Payment confirmation email sent.' : 'Could not send the email. Please try again.');
            } else {
                set_flash('error', 'Mark the order as Payment Verified before sending this email.');
            }
        }
    }
    header('Location: ' . BASE_URL . 'admin/orders-edit.php?id=' . $id);
    exit;
}

$stmt = db()->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . 'admin/orders.php');
    exit;
}

$items        = get_order_items($id);
$statusLabels = order_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <div>
            <h2>Order <?= e($order['order_number']) ?></h2>
            <p class="card-sub" style="margin:4px 0 0;">Placed <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></p>
        </div>
        <span class="status-pill st-<?= e($order['order_status']) ?>" style="font-size:13px;padding:8px 16px;"><?= e($statusLabels[$order['order_status']] ?? $order['order_status']) ?></span>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Customer</label>
            <p><a href="<?= BASE_URL ?>admin/customers-edit.php?id=<?= (int)$order['customer_id'] ?>"><?= e($order['customer_name']) ?></a><br><?= e($order['customer_email']) ?><?= $order['customer_phone'] ? ' · ' . e($order['customer_phone']) : '' ?></p>
        </div>
        <div class="form-group">
            <label>Payment Method</label>
            <p>Bank Transfer</p>
        </div>
    </div>
</div>

<div class="card">
    <h2>Billing &amp; Shipping Address</h2>
    <div class="form-row">
        <div class="form-group">
            <label>Billing</label>
            <p><?= e($order['billing_address_line1']) ?><?= $order['billing_address_line2'] ? ', ' . e($order['billing_address_line2']) : '' ?><br>
               <?= e(implode(', ', array_filter([$order['billing_city'], $order['billing_state'], $order['billing_postal_code'], $order['billing_country']]))) ?></p>
        </div>
        <div class="form-group">
            <label>Shipping</label>
            <p><?= e($order['shipping_address_line1']) ?><?= $order['shipping_address_line2'] ? ', ' . e($order['shipping_address_line2']) : '' ?><br>
               <?= e(implode(', ', array_filter([$order['shipping_city'], $order['shipping_state'], $order['shipping_postal_code'], $order['shipping_country']]))) ?></p>
        </div>
    </div>
</div>

<div class="card">
    <h2>Order Items</h2>
    <table class="items-table">
        <thead><tr><th>Gemstone</th><th>Details</th><th>Qty</th><th>Unit Price</th><th>Discount</th><th>Line Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <td><?php if ($it['product_id']): ?><a href="<?= BASE_URL ?>admin/gemstones-edit.php?id=<?= (int)$it['product_id'] ?>"><?= e($it['product_name']) ?></a><?php else: ?><?= e($it['product_name']) ?><?php endif; ?></td>
                <td><?= e(($it['weight'] !== null ? $it['weight'] . ' ct' : '') . ($it['shape'] ? ' · ' . $it['shape'] : '')) ?></td>
                <td><?= (int) $it['quantity'] ?></td>
                <td><?= format_money($it['unit_price']) ?></td>
                <td><?= $it['discount_amount'] > 0 ? format_money($it['discount_amount']) : '—' ?></td>
                <td><?= format_money($it['line_total']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-divider"></div>
    <div style="max-width:340px;margin-left:auto;">
        <div class="order-totals-row" style="display:flex;justify-content:space-between;padding:4px 0;"><span>Gemstone Subtotal</span><span><?= format_money($order['items_original_total']) ?></span></div>
        <div class="order-totals-row" style="display:flex;justify-content:space-between;padding:4px 0;"><span>Discount</span><span><?= format_money($order['items_discount_total']) ?></span></div>
        <div class="order-totals-row" style="display:flex;justify-content:space-between;padding:4px 0;font-weight:600;"><span>Gemstone Total</span><span><?= format_money($order['gemstone_total']) ?></span></div>
        <div class="order-totals-row" style="display:flex;justify-content:space-between;padding:4px 0;"><span>Shipping Charge</span><span><?= $order['shipping_charge'] !== null ? format_money($order['shipping_charge']) : '—' ?></span></div>
        <div class="order-totals-row" style="display:flex;justify-content:space-between;padding:8px 0;font-weight:700;font-size:16px;border-top:1px solid var(--line);margin-top:6px;"><span>Final Total</span><span><?= $order['final_total'] !== null ? format_money($order['final_total']) : '—' ?></span></div>
    </div>
</div>

<div class="card">
    <h2>Shipping Charge</h2>
    <p class="card-sub">Enter the shipping charge for this order. The customer's price only ever covered the gemstone(s) — this is added on top to compute the Final Payable Total.</p>
    <form method="post" class="form-row" style="align-items:flex-end;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="set_shipping_charge">
        <div class="form-group">
            <label>Shipping Charge (USD)</label>
            <input type="number" step="0.01" min="0" name="shipping_charge" class="form-control" value="<?= e($order['shipping_charge'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save Shipping Charge</button>
        </div>
    </form>

    <form method="post" style="margin-top:14px;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="send_shipping_email">
        <button type="submit" class="btn btn-primary" <?= $order['shipping_charge'] === null ? 'disabled' : '' ?>>Send Final Total Email to Customer</button>
    </form>
</div>

<div class="card">
    <h2>Order Status</h2>
    <form method="post" class="form-row" style="align-items:flex-end;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_status">
        <div class="form-group">
            <label>Status</label>
            <select name="order_status" class="form-control">
                <?php foreach ($statusLabels as $key => $label): ?>
                    <option value="<?= e($key) ?>" <?= $order['order_status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update Status</button>
        </div>
    </form>
    <p class="hint">Setting status to Payment Verified, Order Confirmed, Shipped, or Completed marks the ordered gemstone(s) as Sold. Setting status to Cancelled reverts them to Available.</p>

    <div class="section-divider"></div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="send_payment_confirmation_email">
        <button type="submit" class="btn btn-primary" <?= !in_array($order['order_status'], ['payment_verified', 'order_confirmed', 'shipped', 'completed'], true) ? 'disabled' : '' ?>>Send Payment Confirmation Email</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
