<?php
$page_title = 'About: Memberships';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('about');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'save_content') {
            save_content_group();
            set_flash('success', 'Section content saved.');
        }
        elseif ($action === 'add') {
            $img = handle_upload('logo', '');
            $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM memberships")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO memberships (name, description, logo, sort_order, is_active) VALUES (?,?,?,?,1)");
            $stmt->execute([trim($_POST['name'] ?? ''), trim($_POST['description'] ?? ''), $img, $ord]);
            set_flash('success', 'Membership added.');
        }
        elseif ($action === 'update') {
            $id  = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT logo FROM memberships WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('logo', $old);
            $stmt = db()->prepare("UPDATE memberships SET name=?, description=?, logo=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['name'] ?? ''), trim($_POST['description'] ?? ''), $img, isset($_POST['is_active']) ? 1 : 0, $id]);
            set_flash('success', 'Membership updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $row = db()->prepare("SELECT logo FROM memberships WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR . '/' . $old)) @unlink(UPLOAD_DIR . '/' . $old);
            db()->prepare("DELETE FROM memberships WHERE id=?")->execute([$id]);
            set_flash('success', 'Membership deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group('membership');
$items  = db()->query("SELECT * FROM memberships ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Section Content</h2>
            <p class="card-sub" style="margin:4px 0 0;">Heading and the two paragraphs above/below the logo row.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<div class="card">
    <h2>Memberships &amp; Trade Organizations</h2>
    <p class="card-sub">Each entry shows a logo (or the name, if no logo is uploaded) with a short description. Recommended logo size: 300 × 150px, transparent PNG.</p>

    <?php foreach ($items as $m): ?>
        <form method="post" enctype="multipart/form-data" style="border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <div class="form-group" style="margin-bottom:10px;">
                <label>Organization Name</label>
                <input type="text" name="name" class="form-control" value="<?= e($m['name']) ?>">
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2"><?= e($m['description']) ?></textarea>
            </div>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                <div class="img-field">
                    <div class="img-preview" style="width:90px;height:60px;">
                        <?php if ($m['logo']): ?>
                            <img src="<?= UPLOAD_URL . e($m['logo']) ?>">
                        <?php else: ?>
                            Logo
                        <?php endif; ?>
                    </div>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-sm">Logo</button>
                        <input type="file" name="logo" accept="image/*">
                    </div>
                </div>
                <label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $m['is_active'] ? 'checked' : '' ?>> Show</label>
                <button class="btn btn-sm btn-primary">Save</button>
                <button class="btn btn-sm btn-danger" onclick="this.form.querySelector('[name=action]').value='delete';return confirm('Delete this membership?')">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>

    <div class="section-divider"></div>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Membership</h2>
        <div class="form-group">
            <label>Organization Name</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. International Colored Gemstone Association (ICA)" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="2"></textarea>
        </div>
        <div class="img-field" style="margin-bottom:14px;">
            <div class="img-preview" id="newMembershipLogoPrev" style="width:90px;height:60px;">Logo</div>
            <div class="upload-btn-wrap">
                <button type="button" class="btn btn-sm">Choose Logo</button>
                <input type="file" name="logo" accept="image/*" data-preview="newMembershipLogoPrev">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Membership</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
