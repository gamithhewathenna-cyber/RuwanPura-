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
            $stmt = db()->prepare("INSERT INTO hero_slides (image, eyebrow, title, description, btn_text, btn_link, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $maxOrder = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM hero_slides")->fetchColumn();
            $stmt->execute([
                $img,
                trim($_POST['eyebrow'] ?? ''),
                trim($_POST['title'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['btn_text'] ?? ''),
                trim($_POST['btn_link'] ?? ''),
                $maxOrder,
            ]);
            set_flash('success', 'Slide added.');
        }
        elseif ($action === 'update_slide') {
            $id = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT image FROM hero_slides WHERE id=?");
            $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('slide_image', $old);
            $stmt = db()->prepare("UPDATE hero_slides SET image=?, eyebrow=?, title=?, description=?, btn_text=?, btn_link=?, is_active=? WHERE id=?");
            $stmt->execute([
                $img,
                trim($_POST['eyebrow'] ?? ''),
                trim($_POST['title'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['btn_text'] ?? ''),
                trim($_POST['btn_link'] ?? ''),
                isset($_POST['is_active']) ? 1 : 0,
                $id,
            ]);
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

<!-- Hero default text content -->
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Hero Text &amp; Button (default)</h2>
            <p class="card-sub" style="margin:4px 0 0;">Used for any slide below that doesn't have its own custom text.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<!-- Slides -->
<div class="card">
    <div class="card-head-row">
        <h2>Slides</h2>
    </div>
    <p class="card-sub">Each slide has its own image and text — when the slider changes, the image and text change together. Leave a text field blank to use the default text above. Click a slide to edit it.</p>

    <?php foreach ($slides as $i => $s):
        $slideLabel = $s['title'] ?: $s['eyebrow'] ?: ('Slide ' . ($i + 1));
    ?>
        <details class="slide-editor">
            <summary class="slide-summary">
                <span class="slide-summary-thumb">
                    <?php if ($s['image']): ?>
                        <img src="<?= UPLOAD_URL . e($s['image']) ?>">
                    <?php endif; ?>
                </span>
                <span class="slide-summary-label"><?= e($slideLabel) ?></span>
                <span class="badge <?= $s['is_active'] ? 'on' : 'off' ?>"><?= $s['is_active'] ? 'Active' : 'Hidden' ?></span>
                <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
            </summary>

            <div class="slide-editor-body">
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_slide">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <div class="slide-editor-grid">
                        <div class="slide-editor-image">
                            <div class="img-field">
                                <div class="img-preview" id="slidePrev<?= (int)$s['id'] ?>">
                                    <?php if ($s['image']): ?>
                                        <img src="<?= UPLOAD_URL . e($s['image']) ?>">
                                    <?php else: ?>
                                        No image
                                    <?php endif; ?>
                                </div>
                                <div class="upload-btn-wrap">
                                    <button type="button" class="btn btn-sm">Replace Image</button>
                                    <input type="file" name="slide_image" accept="image/*" data-preview="slidePrev<?= (int)$s['id'] ?>">
                                </div>
                            </div>
                            <div class="hint" style="margin-top:6px;">Recommended: 1600 × 1100px</div>
                            <label style="display:flex;gap:6px;align-items:center;font-size:13px;margin-top:12px;">
                                <input type="checkbox" name="is_active" <?= $s['is_active'] ? 'checked' : '' ?>> Show this slide
                            </label>
                        </div>
                        <div class="slide-editor-fields">
                            <div class="form-group">
                                <label>Eyebrow <span class="hint">(small label above the title)</span></label>
                                <input type="text" name="eyebrow" class="form-control" value="<?= e($s['eyebrow']) ?>" placeholder="<?= e(c('hero_eyebrow')) ?>">
                            </div>
                            <div class="form-group">
                                <label>Title</label>
                                <textarea name="title" class="form-control" rows="2" placeholder="<?= e(c('hero_title')) ?>"><?= e($s['title']) ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="<?= e(c('hero_desc')) ?>"><?= e($s['description']) ?></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Button Text</label>
                                    <input type="text" name="btn_text" class="form-control" value="<?= e($s['btn_text']) ?>" placeholder="<?= e(c('hero_btn_text')) ?>">
                                </div>
                                <div class="form-group">
                                    <label>Button Link</label>
                                    <input type="text" name="btn_link" class="form-control" value="<?= e($s['btn_link']) ?>" placeholder="<?= e(c('hero_btn_link', '#')) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-actions" style="margin-top:12px;">
                        <button class="btn btn-sm btn-primary">Save Slide</button>
                    </div>
                </form>
                <form method="post" onsubmit="return confirm('Delete this slide?')" style="margin-top:8px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete_slide">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <button class="btn btn-sm btn-danger">Delete Slide</button>
                </form>
            </div>
        </details>
    <?php endforeach; ?>

    <details class="slide-editor slide-add">
        <summary class="slide-summary">
            <span class="slide-summary-thumb slide-summary-add">+</span>
            <span class="slide-summary-label">Add New Slide</span>
            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
        </summary>
        <div class="slide-editor-body">
            <form method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_slide">
                <div class="slide-editor-grid">
                    <div class="slide-editor-image">
                        <div class="img-field">
                            <div class="img-preview" id="newSlidePrev">No image</div>
                            <div class="upload-btn-wrap">
                                <button type="button" class="btn btn-sm">Choose Image</button>
                                <input type="file" name="slide_image" accept="image/*" data-preview="newSlidePrev" required>
                            </div>
                        </div>
                        <div class="hint" style="margin-top:6px;">Recommended: 1600 × 1100px</div>
                    </div>
                    <div class="slide-editor-fields">
                        <div class="form-group">
                            <label>Eyebrow</label>
                            <input type="text" name="eyebrow" class="form-control" placeholder="<?= e(c('hero_eyebrow')) ?>">
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <textarea name="title" class="form-control" rows="2" placeholder="<?= e(c('hero_title')) ?>"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="<?= e(c('hero_desc')) ?>"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Button Text</label>
                                <input type="text" name="btn_text" class="form-control" placeholder="<?= e(c('hero_btn_text')) ?>">
                            </div>
                            <div class="form-group">
                                <label>Button Link</label>
                                <input type="text" name="btn_link" class="form-control" placeholder="<?= e(c('hero_btn_link', '#')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:8px;">Add Slide</button>
            </form>
        </div>
    </details>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
