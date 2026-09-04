<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/customer-auth.php';
maybe_show_maintenance_page();
require_customer();

$stmt = db()->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->execute([current_customer_id()]);
$orders = $stmt->fetchAll();

$statusLabels = order_status_labels();

include __DIR__ . '/../includes/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">My Account</h1>

        <div class="account-tabs">
            <a href="<?= BASE_URL ?>account/index.php" class="account-tab">Profile</a>
            <a href="<?= BASE_URL ?>account/orders.php" class="account-tab active">Order History</a>
        </div>

        <div class="account-card">
            <h2>Order History</h2>

            <?php if (!$orders): ?>
                <p class="account-card-sub">You haven't placed any orders yet. <a href="<?= BASE_URL ?>gemstones.php" style="color:var(--gold);">Browse the catalogue</a>.</p>
            <?php else: ?>
                <table class="order-items-table">
                    <thead><tr><th>Order</th><th>Date</th><th>Status</th><th style="text-align:right;">Total</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= e($o['order_number']) ?></td>
                            <td><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                            <td><span class="order-status-pill st-<?= e($o['order_status']) ?>"><?= e($statusLabels[$o['order_status']] ?? $o['order_status']) ?></span></td>
                            <td style="text-align:right;"><?= $o['final_total'] !== null ? format_money($o['final_total']) : format_money($o['gemstone_total']) . ' + shipping' ?></td>
                            <td><a href="<?= BASE_URL ?>account/order.php?id=<?= (int) $o['id'] ?>" class="btn-outline btn-sm">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
