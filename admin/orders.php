<?php
$page_title = 'Orders';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('orders');

$orders = db()->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();
$statusLabels = order_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row"><h2>Orders</h2></div>
    <p class="card-sub">Bank Transfer orders placed through the website. Click a row to review, quote shipping, and manage its status.</p>

    <table class="items-table">
        <thead>
            <tr>
                <th>Order</th><th>Customer</th><th>Date</th><th>Gemstone Total</th><th>Discount</th>
                <th>Shipping</th><th>Final Total</th><th>Payment</th><th>Status</th><th style="text-align:right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= e($o['order_number']) ?></td>
                <td><?= e($o['customer_name']) ?></td>
                <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                <td><?= format_money($o['gemstone_total']) ?></td>
                <td><?= $o['items_discount_total'] > 0 ? format_money($o['items_discount_total']) : '—' ?></td>
                <td><?= $o['shipping_charge'] !== null ? format_money($o['shipping_charge']) : '—' ?></td>
                <td><?= $o['final_total'] !== null ? format_money($o['final_total']) : '—' ?></td>
                <td><?= e(order_payment_status_label($o['order_status'])) ?></td>
                <td><span class="status-pill st-<?= e($o['order_status']) ?>"><?= e($statusLabels[$o['order_status']] ?? $o['order_status']) ?></span></td>
                <td class="row-actions"><a href="<?= BASE_URL ?>admin/orders-edit.php?id=<?= (int)$o['id'] ?>" class="btn btn-sm">Manage</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$orders): ?>
            <tr><td colspan="10" style="color:var(--muted);text-align:center;padding:24px;">No orders yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
