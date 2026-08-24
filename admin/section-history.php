<?php
$page_title = 'About: Our Global Journey';
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
            $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM history_milestones")->fetchColumn();
            $stmt = db()->prepare("INSERT INTO history_milestones (year_label, description, sort_order, is_active) VALUES (?,?,?,1)");
            $stmt->execute([trim($_POST['year_label'] ?? ''), trim($_POST['description'] ?? ''), $ord]);
            set_flash('success', 'Milestone added.');
        }
        elseif ($action === 'update') {
            $stmt = db()->prepare("UPDATE history_milestones SET year_label=?, description=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['year_label'] ?? ''), trim($_POST['description'] ?? ''), isset($_POST['is_active']) ? 1 : 0, (int)$_POST['id']]);
            set_flash('success', 'Milestone updated.');
        }
        elseif ($action === 'delete') {
            db()->prepare("DELETE FROM history_milestones WHERE id=?")->execute([(int)$_POST['id']]);
            set_flash('success', 'Milestone deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$blocks = content_group('history');
$items  = db()->query("SELECT * FROM history_milestones ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_content">
    <div class="card">
        <div class="card-head-row">
            <div><h2>Section Content</h2>
            <p class="card-sub" style="margin:4px 0 0;">The eyebrow and heading above the timeline.</p></div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
        <?php foreach ($blocks as $block) render_content_field($block); ?>
    </div>
</form>

<div class="card">
    <h2>Timeline Milestones</h2>
    <p class="card-sub">Each entry appears as one step in the "Our Global Journey" timeline, in order from top to bottom.</p>

    <?php foreach ($items as $m): ?>
        <form method="post" style="border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
            <div class="form-row">
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Year / Period</label>
                    <input type="text" name="year_label" class="form-control" value="<?= e($m['year_label']) ?>">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= e($m['description']) ?></textarea>
                </div>
            </div>
            <div style="display:flex;gap:12px;align-items:center;">
                <label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $m['is_active'] ? 'checked' : '' ?>> Show</label>
                <button class="btn btn-sm btn-primary">Save</button>
                <button class="btn btn-sm btn-danger" onclick="this.form.querySelector('[name=action]').value='delete';return confirm('Delete this milestone?')">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>

    <div class="section-divider"></div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Milestone</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Year / Period</label>
                <input type="text" name="year_label" class="form-control" placeholder="e.g. 2027" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="What happened in this period..."></textarea>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Milestone</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
