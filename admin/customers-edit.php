<?php
$page_title = 'Customer Details';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('orders');
require_once __DIR__ . '/../includes/order-functions.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: ' . BASE_URL . 'admin/customers.php');
    exit;
}

$ordersStmt = db()->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$ordersStmt->execute([$id]);
$orders = $ordersStmt->fetchAll();
$statusLabels = order_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2><?= e($customer['full_name']) ?></h2>
        <a href="<?= BASE_URL ?>admin/customers.php" class="btn btn-sm">&larr; Back to Customers</a>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Email</label><p><?= e($customer['email']) ?></p></div>
        <div class="form-group"><label>Phone</label><p><?= e($customer['phone'] ?: '—') ?></p></div>
    </div>
    <div class="form-row">
        <div class="form-group"><label>Country</label><p><?= e($customer['country'] ?: '—') ?></p></div>
        <div class="form-group"><label>Joined</label><p><?= e(date('M j, Y', strtotime($customer['created_at']))) ?></p></div>
    </div>
</div>

<div class="card">
    <h2>Billing Address</h2>
    <p class="card-sub">
        <?= e($customer['billing_address_line1']) ?><?= $customer['billing_address_line2'] ? ', ' . e($customer['billing_address_line2']) : '' ?><br>
        <?= e(implode(', ', array_filter([$customer['billing_city'], $customer['billing_state'], $customer['billing_postal_code'], $customer['billing_country']]))) ?>
    </p>
</div>

<div class="card">
    <h2>Shipping Address</h2>
    <p class="card-sub">
        <?php if ($customer['shipping_same_as_billing']): ?>
            Same as billing address.
        <?php else: ?>
            <?= e($customer['shipping_address_line1']) ?><?= $customer['shipping_address_line2'] ? ', ' . e($customer['shipping_address_line2']) : '' ?><br>
            <?= e(implode(', ', array_filter([$customer['shipping_city'], $customer['shipping_state'], $customer['shipping_postal_code'], $customer['shipping_country']]))) ?>
        <?php endif; ?>
    </p>
</div>

<div class="card">
    <h2>Order History</h2>
    <table class="items-table">
        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Final Total</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                <td><span class="status-pill st-<?= e($o['order_status']) ?>"><?= e($statusLabels[$o['order_status']] ?? $o['order_status']) ?></span></td>
                <td><?= $o['final_total'] !== null ? format_money($o['final_total']) : '—' ?></td>
                <td><a href="<?= BASE_URL ?>admin/orders-edit.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm">View</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
            <tr><td colspan="5" style="color:var(--muted);text-align:center;padding:24px;">No orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
