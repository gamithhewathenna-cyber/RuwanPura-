<?php
$page_title = 'Insights: Categories';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('blog');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                $ord  = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM blog_categories")->fetchColumn();
                $slug = unique_slug('blog_categories', $name);
                db()->prepare("INSERT INTO blog_categories (name, slug, sort_order, is_active) VALUES (?,?,?,1)")
                    ->execute([$name, $slug, $ord]);
                set_flash('success', 'Category added.');
            }
        }
        elseif ($action === 'update') {
            $id     = (int)($_POST['id'] ?? 0);
            $name   = trim($_POST['name'] ?? '');
            $active = isset($_POST['is_active']) ? 1 : 0;
            $slug   = unique_slug('blog_categories', $name, $id);
            db()->prepare("UPDATE blog_categories SET name=?, slug=?, is_active=? WHERE id=?")
                ->execute([$name, $slug, $active, $id]);
            set_flash('success', 'Category updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $chk = db()->prepare("SELECT COUNT(*) FROM blog_posts WHERE category_id = ?");
            $chk->execute([$id]);
            if ((int) $chk->fetchColumn() > 0) {
                set_flash('error', 'Cannot delete — one or more posts still use this category. Reassign them first.');
            } else {
                db()->prepare("DELETE FROM blog_categories WHERE id=?")->execute([$id]);
                set_flash('success', 'Category deleted.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$items = db()->query("SELECT * FROM blog_categories ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2>Blog Categories</h2>
    </div>
    <p class="card-sub">These group posts on the Insights page and appear as filters for visitors.</p>

    <table class="items-table">
        <thead><tr><th>Name</th><th>Active</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <td><input type="text" name="name" value="<?= e($it['name']) ?>" class="form-control" style="min-width:220px;"></td>
                    <td><label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $it['is_active'] ? 'checked' : '' ?>> Active</label></td>
                    <td class="row-actions">
                        <button class="btn btn-sm btn-primary">Save</button>
                </form>
                        <form method="post" onsubmit="return confirm('Delete this category?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$items): ?>
            <tr><td colspan="3" style="color:var(--muted);text-align:center;padding:20px;">None yet — add one below.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="section-divider"></div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Category</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Category</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
