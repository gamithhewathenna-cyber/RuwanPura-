<?php
$page_title = 'Gemstones: Products';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('gemstones');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'delete') {
            $imgs = db()->prepare("SELECT image FROM product_images WHERE product_id=?");
            $imgs->execute([$id]);
            foreach ($imgs->fetchAll() as $row) {
                if ($row['image'] && file_exists(UPLOAD_DIR . '/' . $row['image'])) @unlink(UPLOAD_DIR . '/' . $row['image']);
            }
            db()->prepare("DELETE FROM product_images WHERE product_id=?")->execute([$id]);
            db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
            set_flash('success', 'Gemstone deleted.');
        }
        elseif ($action === 'set_status') {
            $status = $_POST['status'] ?? 'available';
            if (array_key_exists($status, product_status_labels())) {
                db()->prepare("UPDATE products SET status=? WHERE id=?")->execute([$status, $id]);
                set_flash('success', 'Status updated.');
            }
        }
        elseif ($action === 'toggle_active') {
            db()->prepare("UPDATE products SET is_active = IF(is_active=1,0,1) WHERE id=?")->execute([$id]);
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$products = db()->query("
    SELECT p.*, c.name AS category_name,
           (SELECT image FROM product_images WHERE product_id=p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS thumb
    FROM products p
    LEFT JOIN gem_categories c ON c.id = p.category_id
    ORDER BY p.sort_order, p.id DESC
")->fetchAll();

$statusLabels = product_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2>Gemstones</h2>
        <a href="<?= BASE_URL ?>admin/gemstones-edit.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Add Gemstone
        </a>
    </div>
    <p class="card-sub">Every gemstone in the online catalogue. Click a name to edit it.</p>

    <table class="items-table">
        <thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Weight</th><th>Price</th><th>Stock</th><th>Status</th><th>Shown</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <?php if ($p['thumb']): ?>
                        <img class="thumb" src="<?= UPLOAD_URL . e($p['thumb']) ?>">
                    <?php else: ?>
                        <span class="badge">No image</span>
                    <?php endif; ?>
                </td>
                <td><a href="<?= BASE_URL ?>admin/gemstones-edit.php?id=<?= (int)$p['id'] ?>" style="font-weight:600;color:var(--dark);"><?= e($p['name']) ?></a></td>
                <td><?= e($p['category_name'] ?: '—') ?></td>
                <td><?= $p['weight'] !== null ? e($p['weight']) . ' ct' : '—' ?></td>
                <td>
                    <?php $listPricing = product_pricing($p); ?>
                    <?php if ($listPricing['original'] !== null): ?>
                        <?php if ($listPricing['has_discount']): ?>
                            <span class="price-was" style="font-size:11px;"><?= format_money($listPricing['original']) ?></span><br>
                            <span class="price-now" style="font-size:13px;"><?= format_money($listPricing['final']) ?></span>
                        <?php else: ?>
                            <?= format_money($listPricing['final']) ?>
                        <?php endif; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= (int) ($p['quantity'] ?? 1) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <select name="status" class="form-control" style="padding:6px 10px;font-size:12.5px;width:auto;" onchange="this.form.submit()">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $p['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" class="badge <?= $p['is_active'] ? 'on' : 'off' ?>" style="border:none;cursor:pointer;"><?= $p['is_active'] ? 'Shown' : 'Hidden' ?></button>
                    </form>
                </td>
                <td class="row-actions">
                    <a href="<?= BASE_URL ?>admin/gemstones-edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this gemstone? This also removes its images.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$products): ?>
            <tr><td colspan="9" style="color:var(--muted);text-align:center;padding:24px;">No gemstones yet. Click "Add Gemstone" to create one.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
