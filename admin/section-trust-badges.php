<?php
$page_title = 'Home: Trust Badges';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('home');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $icon = handle_upload('icon', '');
            $ord  = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM trust_badges")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO trust_badges (icon, title, description, sort_order, is_active) VALUES (?,?,?,?,1)");
            $stmt->execute([$icon, trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''), $ord]);
            set_flash('success', 'Badge added.');
        }
        elseif ($action === 'update') {
            $id  = (int) $_POST['id'];
            $row = db()->prepare("SELECT icon FROM trust_badges WHERE id=?");
            $row->execute([$id]);
            $old  = $row->fetchColumn();
            $icon = handle_upload('icon', $old);
            $stmt = db()->prepare("UPDATE trust_badges SET icon=?, title=?, description=?, is_active=? WHERE id=?");
            $stmt->execute([$icon, trim($_POST['title'] ?? ''), trim($_POST['description'] ?? ''), isset($_POST['is_active']) ? 1 : 0, $id]);
            set_flash('success', 'Badge updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int) $_POST['id'];
            $row = db()->prepare("SELECT icon FROM trust_badges WHERE id=?");
            $row->execute([$id]);
            $old = $row->fetchColumn();
            if ($old && file_exists(UPLOAD_DIR . '/' . $old)) @unlink(UPLOAD_DIR . '/' . $old);
            db()->prepare("DELETE FROM trust_badges WHERE id=?")->execute([$id]);
            set_flash('success', 'Badge deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$items = db()->query("SELECT * FROM trust_badges ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <h2>Trust Badges</h2>
    <p class="card-sub">The small icon strip shown on the home page just below the hero slider. Recommended icon: a simple line-style SVG or PNG, square, transparent background.</p>

    <?php foreach ($items as $b): ?>
        <form method="post" enctype="multipart/form-data" style="border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
            <div class="form-row">
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" value="<?= e($b['title']) ?>">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" value="<?= e($b['description']) ?>">
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                <div class="img-field">
                    <div class="img-preview" style="width:56px;height:56px;">
                        <?php if ($b['icon']): ?><img src="<?= UPLOAD_URL . e($b['icon']) ?>"><?php else: ?><span style="font-size:10px;">Icon</span><?php endif; ?>
                    </div>
                    <div class="upload-btn-wrap">
                        <button type="button" class="btn btn-sm">Icon</button>
                        <input type="file" name="icon" accept="image/*,.svg">
                    </div>
                </div>
                <label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $b['is_active'] ? 'checked' : '' ?>> Show</label>
                <button class="btn btn-sm btn-primary">Save</button>
                <button class="btn btn-sm btn-danger" onclick="this.form.querySelector('[name=action]').value='delete';return confirm('Delete this badge?')">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>
    <?php if (!$items): ?>
        <p class="card-sub">No badges yet — add one below.</p>
    <?php endif; ?>

    <div class="section-divider"></div>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Badge</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" name="description" class="form-control">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
            <label>Icon <span class="hint">(simple line-style SVG or PNG, square)</span></label>
            <div class="img-field">
                <div class="img-preview" id="newIconPrev" style="width:56px;height:56px;">Icon</div>
                <div class="upload-btn-wrap">
                    <button type="button" class="btn btn-sm">Choose Icon</button>
                    <input type="file" name="icon" accept="image/*,.svg" data-preview="newIconPrev">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Badge</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
