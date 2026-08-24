<?php
$page_title = 'Exhibitions & Logos';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('home');

$section_group = 'events';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        if ($action === 'save_content') {
            save_content_group();
            set_flash('success', 'Section content saved.');
        }
        elseif ($action === 'add') {
            $img = handle_upload('image', '');
            $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM partners")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO partners (name, image, sort_order, is_active) VALUES (?,?,?,1)");
            $stmt->execute([trim($_POST['name']??''), $img, $ord]);
            set_flash('success', 'Logo added.');
        }
        elseif ($action === 'update') {
            $id = (int)$_POST['id'];
            $row = db()->prepare("SELECT image FROM partners WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('image', $old);
            $stmt = db()->prepare("UPDATE partners SET name=?, image=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['name']??''), $img, isset($_POST['is_active'])?1:0, $id]);
            set_flash('success', 'Logo updated.');
        }
        elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $row = db()->prepare("SELECT image FROM partners WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR.'/'.$old)) @unlink(UPLOAD_DIR.'/'.$old);
            db()->prepare("DELETE FROM partners WHERE id=?")->execute([$id]);
            set_flash('success', 'Logo deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group($section_group);
$items  = db()->query("SELECT * FROM partners ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Section Content</h2>
            <p class="card-sub" style="margin:4px 0 0;">Heading and description for the exhibitions section.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<div class="card">
    <h2>Exhibition / Partner Logos</h2>
    <p class="card-sub">Logos shown in the row. If no image is uploaded, the name is shown as text. Recommended image size: 300 × 150px, transparent PNG.</p>

    <table class="items-table">
        <thead><tr><th>Logo</th><th>Name</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($items as $p): ?>
            <tr>
                <form method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                    <td>
                        <label style="position:relative;display:inline-block;cursor:pointer;">
                            <?php if ($p['image']): ?>
                                <img class="thumb" style="object-fit:contain;background:#f8f8f8;" src="<?= UPLOAD_URL . e($p['image']) ?>">
                            <?php else: ?>
                                <span class="thumb" style="display:flex;align-items:center;justify-content:center;font-size:10px;color:#999;">Add</span>
                            <?php endif; ?>
                            <input type="file" name="image" accept="image/*" style="position:absolute;inset:0;opacity:0;cursor:pointer;" onchange="this.form.submit()">
                        </label>
                    </td>
                    <td><input type="text" name="name" value="<?= e($p['name']) ?>" class="form-control" style="min-width:180px;"></td>
                    <td><label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $p['is_active']?'checked':'' ?>> Show</label></td>
                    <td class="row-actions">
                        <button class="btn btn-sm btn-primary">Save</button>
                </form>
                        <form method="post" onsubmit="return confirm('Delete this logo?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
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
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Logo</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Name <span class="hint">(shown if no image)</span></label>
                <input type="text" name="name" class="form-control" placeholder="e.g. JCK Las Vegas">
            </div>
            <div class="form-group">
                <label>Logo Image <span class="hint">(300 × 150px, transparent PNG)</span></label>
                <div class="img-field">
                    <div class="img-preview" id="newLogoPrev">No image</div>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-sm">Choose</button>
                        <input type="file" name="image" accept="image/*" data-preview="newLogoPrev">
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Logo</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
