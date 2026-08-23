<?php
$page_title = 'Gemstones Collection';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

$section_group = 'collection';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_content') {
            save_content_group();
            set_flash('success', 'Section heading saved.');
        }
        elseif ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            $img  = handle_upload('image', '');
            $ord  = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM gemstones")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO gemstones (name, image, sort_order, is_active) VALUES (?,?,?,1)");
            $stmt->execute([$name, $img, $ord]);
            set_flash('success', 'Gemstone added.');
        }
        elseif ($action === 'update') {
            $id  = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT image FROM gemstones WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('image', $old);
            $stmt = db()->prepare("UPDATE gemstones SET name=?, image=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['name'] ?? ''), $img, isset($_POST['is_active'])?1:0, $id]);
            set_flash('success', 'Gemstone updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT image FROM gemstones WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR.'/'.$old)) @unlink(UPLOAD_DIR.'/'.$old);
            db()->prepare("DELETE FROM gemstones WHERE id=?")->execute([$id]);
            set_flash('success', 'Gemstone deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group($section_group);
$items  = db()->query("SELECT * FROM gemstones ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Section Heading</h2>
            <p class="card-sub" style="margin:4px 0 0;">The small label and title above the gemstones.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<div class="card">
    <h2>Gemstones</h2>
    <p class="card-sub">Each gemstone appears as a card in the collection carousel.</p>

    <table class="items-table">
        <thead><tr><th>Image</th><th>Name</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($items as $g): ?>
            <tr>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
                    <td>
                        <label style="position:relative;display:inline-block;cursor:pointer;">
                            <?php if ($g['image']): ?>
                                <img class="thumb" src="<?= UPLOAD_URL . e($g['image']) ?>">
                            <?php else: ?>
                                <span class="thumb" style="display:flex;align-items:center;justify-content:center;font-size:10px;color:#999;">Add</span>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;" onchange="this.form.submit()">
                        </label>
                    </td>
                    <td><input type="text" name="name" value="<?= e($g['name']) ?>" class="form-control" style="min-width:200px;"></td>
                    <td><label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $g['is_active']?'checked':'' ?>> Show</label></td>
                    <td class="row-actions">
                        <button class="btn btn-sm btn-primary">Save</button>
                </form>
                        <form method="post" onsubmit="return confirm('Delete this gemstone?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
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
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Gemstone</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Ruby" required>
            </div>
            <div class="form-group">
                <label>Image</label>
                <div class="img-field">
                    <div class="img-preview" id="newGemPrev">No image</div>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-sm">Choose</button>
                        <input type="file" name="image" accept="image/*" data-preview="newGemPrev">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Gemstone</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
