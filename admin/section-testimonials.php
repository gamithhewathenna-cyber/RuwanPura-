<?php
$page_title = 'Testimonials';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('home');

$section_group = 'testimonials';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        if ($action === 'save_content') {
            save_content_group();
            set_flash('success', 'Section content saved.');
        }
        elseif ($action === 'add') {
            $img = handle_upload('avatar', '');
            $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM testimonials")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO testimonials (quote, author_name, author_role, avatar, sort_order, is_active) VALUES (?,?,?,?,?,1)");
            $stmt->execute([trim($_POST['quote']??''), trim($_POST['author_name']??''), trim($_POST['author_role']??''), $img, $ord]);
            set_flash('success', 'Testimonial added.');
        }
        elseif ($action === 'update') {
            $id = (int)$_POST['id'];
            $row = db()->prepare("SELECT avatar FROM testimonials WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            $img = handle_upload('avatar', $old);
            $stmt = db()->prepare("UPDATE testimonials SET quote=?, author_name=?, author_role=?, avatar=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['quote']??''), trim($_POST['author_name']??''), trim($_POST['author_role']??''), $img, isset($_POST['is_active'])?1:0, $id]);
            set_flash('success', 'Testimonial updated.');
        }
        elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $row = db()->prepare("SELECT avatar FROM testimonials WHERE id=?"); $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR.'/'.$old)) @unlink(UPLOAD_DIR.'/'.$old);
            db()->prepare("DELETE FROM testimonials WHERE id=?")->execute([$id]);
            set_flash('success', 'Testimonial deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group($section_group);
$items  = db()->query("SELECT * FROM testimonials ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Section Content</h2>
            <p class="card-sub" style="margin:4px 0 0;">Heading and description above the testimonials.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<div class="card">
    <h2>Client Reviews</h2>
    <p class="card-sub">Each review is a card in the testimonials slider. Recommended avatar size: 200 × 200px, square.</p>

    <?php foreach ($items as $t): ?>
        <form method="post" enctype="multipart/form-data" style="border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <div class="form-group" style="margin-bottom:12px;">
                <label>Quote</label>
                <textarea name="quote" class="form-control" rows="2"><?= e($t['quote']) ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Author Name</label>
                    <input type="text" name="author_name" class="form-control" value="<?= e($t['author_name']) ?>">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Author Role</label>
                    <input type="text" name="author_role" class="form-control" value="<?= e($t['author_role']) ?>">
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                <div class="img-field">
                    <div class="img-preview" style="width:56px;height:56px;border-radius:50%;">
                        <?php if ($t['avatar']): ?><img src="<?= UPLOAD_URL . e($t['avatar']) ?>"><?php else: ?><span style="font-size:10px;">Avatar</span><?php endif; ?>
                    </div>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-sm">Avatar</button>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $t['is_active']?'checked':'' ?>> Show</label>
                <button class="btn btn-sm btn-primary">Save</button>
                <button class="btn btn-sm btn-danger" onclick="this.form.querySelector('[name=action]').value='delete';return confirm('Delete this testimonial?')">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>

    <div class="section-divider"></div>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Testimonial</h2>
        <div class="form-group">
            <label>Quote</label>
            <textarea name="quote" class="form-control" rows="2" required></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Author Name</label>
                <input type="text" name="author_name" class="form-control">
            </div>
            <div class="form-group">
                <label>Author Role</label>
                <input type="text" name="author_role" class="form-control">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
            <label>Avatar <span class="hint">(200 × 200px, square)</span></label>
            <div class="img-field">
                <div class="img-preview" id="newAvatarPrev" style="width:56px;height:56px;border-radius:50%;">Avatar</div>
                <div class="upload-btn-wrap">
                    <button type="button" class="btn btn-sm">Choose Avatar</button>
                    <input type="file" name="avatar" accept="image/*" data-preview="newAvatarPrev">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Testimonial</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
