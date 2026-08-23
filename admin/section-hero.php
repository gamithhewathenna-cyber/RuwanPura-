<?php
$page_title = 'Hero Slider';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

$section_group = 'hero';

/* ---- Handle actions ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? 'save_content';

        if ($action === 'save_content') {
            save_content_group();
            set_flash('success', 'Hero content saved.');
        }
        elseif ($action === 'add_slide') {
            $img = handle_upload('slide_image', '');
            $stmt = db()->prepare("INSERT INTO hero_slides (image, sort_order, is_active) VALUES (?, ?, 1)");
            $maxOrder = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM hero_slides")->fetchColumn();
            $stmt->execute([$img, $maxOrder]);
            set_flash('success', 'Slide added.');
        }
        elseif ($action === 'update_slide') {
            $id = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT image FROM hero_slides WHERE id=?");
            $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('slide_image', $old);
            $stmt = db()->prepare("UPDATE hero_slides SET image=?, is_active=? WHERE id=?");
            $stmt->execute([$img, isset($_POST['is_active']) ? 1 : 0, $id]);
            set_flash('success', 'Slide updated.');
        }
        elseif ($action === 'delete_slide') {
            $id = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT image FROM hero_slides WHERE id=?");
            $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR . '/' . $old)) @unlink(UPLOAD_DIR . '/' . $old);
            db()->prepare("DELETE FROM hero_slides WHERE id=?")->execute([$id]);
            set_flash('success', 'Slide deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group($section_group);
$slides = db()->query("SELECT * FROM hero_slides ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<!-- Hero text content -->
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Hero Text &amp; Button</h2>
            <p class="card-sub" style="margin:4px 0 0;">The headline area on the top-left of the home page.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<!-- Slides -->
<div class="card">
    <div class="card-head-row">
        <h2>Slider Images</h2>
    </div>
    <p class="card-sub">These images rotate in the hero slider on the right side.</p>

    <table class="items-table">
        <thead><tr><th>Image</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($slides as $s): ?>
            <tr>
                <td>
                    <?php if ($s['image']): ?>
                        <img class="thumb" src="<?= UPLOAD_URL . e($s['image']) ?>">
                    <?php else: ?>
                        <span class="badge">No image</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $s['is_active'] ? 'on' : 'off' ?>"><?= $s['is_active'] ? 'Active' : 'Hidden' ?></span>
                </td>
                <td class="row-actions">
                    <!-- edit form (replace image / toggle) -->
                    <form method="post" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_slide">
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <label class="btn btn-sm" style="position:relative;overflow:hidden;">
                            Replace
                            <input type="file" name="slide_image" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;" onchange="this.form.submit()">
                        </label>
                        <label style="display:flex;gap:5px;align-items:center;font-size:13px;">
                            <input type="checkbox" name="is_active" <?= $s['is_active'] ? 'checked' : '' ?> onchange="this.form.submit()"> Show
                        </label>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete this slide?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_slide">
                        <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-divider"></div>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_slide">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Slide</h2>
        <div class="img-field">
            <div class="img-preview" id="newSlidePrev">No image</div>
            <div class="upload-btn-wrap">
                <button type="button" class="btn btn-sm">Choose Image</button>
                <input type="file" name="slide_image" accept="image/*" data-preview="newSlidePrev" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Slide</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
