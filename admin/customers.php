<?php
$page_title = 'Customers';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('orders');

$customers = db()->query("
    SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id = c.id) AS order_count
    FROM customers c
    ORDER BY c.created_at DESC
")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row"><h2>Customers</h2></div>
    <p class="card-sub">Every registered customer account. Click a name to see their profile and order history.</p>

    <table class="items-table">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Country</th><th>Orders</th><th>Joined</th></tr></thead>
        <tbody>
        <?php foreach ($customers as $c): ?>
            <tr>
                <td><a href="<?= BASE_URL ?>admin/customers-edit.php?id=<?= (int)$c['id'] ?>" style="font-weight:600;color:var(--dark);"><?= e($c['full_name']) ?></a></td>
                <td><?= e($c['email']) ?></td>
                <td><?= e($c['phone'] ?: '—') ?></td>
                <td><?= e($c['country'] ?: '—') ?></td>
                <td><?= (int) $c['order_count'] ?></td>
                <td><?= e(date('M j, Y', strtotime($c['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$customers): ?>
            <tr><td colspan="6" style="color:var(--muted);text-align:center;padding:24px;">No customers have registered yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
