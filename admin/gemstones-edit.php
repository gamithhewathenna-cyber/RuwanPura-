<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

$id      = (int)($_GET['id'] ?? 0);
$product = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) {
        header('Location: ' . BASE_URL . 'admin/gemstones-list.php');
        exit;
    }
}
$page_title = $product ? 'Edit Gemstone' : 'Add Gemstone';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'save') {
            $name        = trim($_POST['name'] ?? '');
            $sku         = trim($_POST['sku'] ?? '');
            $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
            $shapeId     = (int)($_POST['shape_id'] ?? 0) ?: null;
            $treatmentId = (int)($_POST['treatment_id'] ?? 0) ?: null;
            $originId    = (int)($_POST['origin_id'] ?? 0) ?: null;
            $weight      = $_POST['weight'] !== '' ? (float)$_POST['weight'] : null;
            $description = trim($_POST['description'] ?? '');
            $certInfo    = trim($_POST['certificate_info'] ?? '');
            $status      = $_POST['status'] ?? 'available';
            if (!array_key_exists($status, product_status_labels())) $status = 'available';
            $isActive    = isset($_POST['is_active']) ? 1 : 0;

            if ($name === '') {
                set_flash('error', 'Please enter a gemstone name.');
                header('Location: ' . BASE_URL . 'admin/gemstones-edit.php' . ($id ? '?id=' . $id : ''));
                exit;
            }

            if ($id) {
                $slug = unique_slug('products', $name, $id);
                $stmt = db()->prepare("UPDATE products SET name=?, slug=?, sku=?, category_id=?, shape_id=?, treatment_id=?, origin_id=?, weight=?, description=?, certificate_info=?, status=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, $sku, $categoryId, $shapeId, $treatmentId, $originId, $weight, $description, $certInfo, $status, $isActive, $id]);
            } else {
                $slug = unique_slug('products', $name);
                $ord  = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM products")->fetchColumn();
                $stmt = db()->prepare("INSERT INTO products (name, slug, sku, category_id, shape_id, treatment_id, origin_id, weight, description, certificate_info, status, is_active, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$name, $slug, $sku, $categoryId, $shapeId, $treatmentId, $originId, $weight, $description, $certInfo, $status, $isActive, $ord]);
                $id = (int) db()->lastInsertId();
            }

            set_flash('success', 'Gemstone saved.');
            header('Location: ' . BASE_URL . 'admin/gemstones-edit.php?id=' . $id);
            exit;
        }
        elseif ($action === 'upload_images' && $id) {
            if (!empty($_FILES['images']['name'][0])) {
                $hasPrimary = (bool) db()->query("SELECT COUNT(*) FROM product_images WHERE product_id=$id AND is_primary=1")->fetchColumn();
                $maxOrd     = (int) db()->query("SELECT COALESCE(MAX(sort_order),0) FROM product_images WHERE product_id=$id")->fetchColumn();

                foreach ($_FILES['images']['name'] as $i => $fname) {
                    if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                    $_FILES['__single'] = [
                        'name'     => $fname,
                        'type'     => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i],
                    ];
                    $saved = handle_upload('__single', '');
                    if ($saved) {
                        $maxOrd++;
                        $primary = !$hasPrimary ? 1 : 0;
                        if ($primary) $hasPrimary = true;
                        db()->prepare("INSERT INTO product_images (product_id, image, is_primary, sort_order) VALUES (?,?,?,?)")
                            ->execute([$id, $saved, $primary, $maxOrd]);
                    }
                }
                set_flash('success', 'Images uploaded.');
            }
            header('Location: ' . BASE_URL . 'admin/gemstones-edit.php?id=' . $id);
            exit;
        }
        elseif ($action === 'delete_image') {
            $imgId = (int)($_POST['image_id'] ?? 0);
            $row   = db()->prepare("SELECT * FROM product_images WHERE id=? AND product_id=?");
            $row->execute([$imgId, $id]);
            $img = $row->fetch();
            if ($img) {
                if ($img['image'] && file_exists(UPLOAD_DIR . '/' . $img['image'])) @unlink(UPLOAD_DIR . '/' . $img['image']);
                db()->prepare("DELETE FROM product_images WHERE id=?")->execute([$imgId]);
                if ($img['is_primary']) {
                    $next = db()->prepare("SELECT id FROM product_images WHERE product_id=? ORDER BY sort_order, id LIMIT 1");
                    $next->execute([$id]);
                    $nextId = $next->fetchColumn();
                    if ($nextId) db()->prepare("UPDATE product_images SET is_primary=1 WHERE id=?")->execute([$nextId]);
                }
            }
            header('Location: ' . BASE_URL . 'admin/gemstones-edit.php?id=' . $id);
            exit;
        }
        elseif ($action === 'set_primary_image') {
            $imgId = (int)($_POST['image_id'] ?? 0);
            db()->prepare("UPDATE product_images SET is_primary=0 WHERE product_id=?")->execute([$id]);
            db()->prepare("UPDATE product_images SET is_primary=1 WHERE id=? AND product_id=?")->execute([$imgId, $id]);
            header('Location: ' . BASE_URL . 'admin/gemstones-edit.php?id=' . $id);
            exit;
        }
    }
    header('Location: ' . BASE_URL . 'admin/gemstones-edit.php' . ($id ? '?id=' . $id : ''));
    exit;
}

$categories   = get_categories(false);
$shapes       = get_shapes(false);
$treatments   = get_treatments(false);
$origins      = get_origins(false);
$statusLabels = product_status_labels();
$images       = $id ? get_product_images($id) : [];

require_once __DIR__ . '/layout-top.php';
?>

<form method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">

    <div class="card">
        <div class="card-head-row">
            <div><h2><?= $product ? 'Edit Gemstone' : 'Add Gemstone' ?></h2>
            <p class="card-sub" style="margin:4px 0 0;">Core details shown on the catalogue and product page.</p></div>
            <button type="submit" class="btn btn-primary">Save Gemstone</button>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?= e($product['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>SKU <span class="hint">(optional)</span></label>
                <input type="text" name="sku" class="form-control" value="<?= e($product['sku'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (isset($product['category_id']) && (int)$product['category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Weight (carat)</label>
                <input type="number" step="0.01" min="0" name="weight" class="form-control" value="<?= e($product['weight'] ?? '') ?>">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Shape</label>
                <select name="shape_id" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach ($shapes as $s): ?>
                        <option value="<?= (int)$s['id'] ?>" <?= (isset($product['shape_id']) && (int)$product['shape_id'] === (int)$s['id']) ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Treatment</label>
                <select name="treatment_id" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach ($treatments as $t): ?>
                        <option value="<?= (int)$t['id'] ?>" <?= (isset($product['treatment_id']) && (int)$product['treatment_id'] === (int)$t['id']) ? 'selected' : '' ?>><?= e($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Origin</label>
                <select name="origin_id" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach ($origins as $o): ?>
                        <option value="<?= (int)$o['id'] ?>" <?= (isset($product['origin_id']) && (int)$product['origin_id'] === (int)$o['id']) ? 'selected' : '' ?>><?= e($o['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Availability</label>
                <select name="status" class="form-control">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= (isset($product['status']) ? $product['status'] === $key : $key === 'available') ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4"><?= e($product['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Certificate Information <span class="hint">(lab name, certificate number, etc. — optional)</span></label>
            <textarea name="certificate_info" class="form-control" rows="2"><?= e($product['certificate_info'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="switch-label">
                <input type="checkbox" name="is_active" value="1" <?= (!$product || $product['is_active']) ? 'checked' : '' ?>>
                Show this gemstone on the website
            </label>
        </div>
    </div>
</form>

<div class="card">
    <h2>Product Images</h2>
    <p class="card-sub">The Primary image is used as the catalogue thumbnail. Recommended size: 1000 × 1000px, square.</p>

    <?php if ($images): ?>
        <div class="gem-image-grid">
            <?php foreach ($images as $img): ?>
                <div class="gem-image-item">
                    <img src="<?= UPLOAD_URL . e($img['image']) ?>">
                    <?php if ($img['is_primary']): ?><span class="badge on gem-image-badge">Primary</span><?php endif; ?>
                    <div class="gem-image-actions">
                        <?php if (!$img['is_primary']): ?>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="set_primary_image">
                                <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                                <button class="btn btn-sm" type="submit">Make Primary</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" onsubmit="return confirm('Delete this image?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="image_id" value="<?= (int)$img['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="section-divider"></div>
    <?php endif; ?>

    <?php if ($product): ?>
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload_images">
            <div class="form-group">
                <label>Add More Images</label>
                <input type="file" name="images[]" accept="image/*" multiple>
            </div>
            <button type="submit" class="btn btn-primary">Upload Images</button>
        </form>
    <?php else: ?>
        <p class="card-sub">Save the gemstone first, then come back here to upload images.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
