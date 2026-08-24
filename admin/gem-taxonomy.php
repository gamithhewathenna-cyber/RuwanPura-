<?php
$config = [
    'category'  => ['table' => 'gem_categories', 'label' => 'Categories', 'singular' => 'Category',  'has_slug' => true],
    'shape'     => ['table' => 'gem_shapes',     'label' => 'Shapes',     'singular' => 'Shape',      'has_slug' => false],
    'treatment' => ['table' => 'gem_treatments', 'label' => 'Treatments', 'singular' => 'Treatment',  'has_slug' => false],
    'origin'    => ['table' => 'gem_origins',    'label' => 'Origins',    'singular' => 'Origin',     'has_slug' => false],
];
$type = $_GET['type'] ?? 'category';
if (!isset($config[$type])) $type = 'category';
$cfg   = $config[$type];
$table = $cfg['table'];

$page_title = 'Gemstones: ' . $cfg['label'];

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('gemstones');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name = trim($_POST['name'] ?? '');
            if ($name !== '') {
                $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM `$table`")->fetchColumn();
                if ($cfg['has_slug']) {
                    $slug = unique_slug($table, $name);
                    db()->prepare("INSERT INTO `$table` (name, slug, sort_order, is_active) VALUES (?,?,?,1)")
                        ->execute([$name, $slug, $ord]);
                } else {
                    db()->prepare("INSERT INTO `$table` (name, sort_order, is_active) VALUES (?,?,1)")
                        ->execute([$name, $ord]);
                }
                set_flash('success', $cfg['singular'] . ' added.');
            }
        }
        elseif ($action === 'update') {
            $id     = (int)($_POST['id'] ?? 0);
            $name   = trim($_POST['name'] ?? '');
            $active = isset($_POST['is_active']) ? 1 : 0;
            if ($cfg['has_slug']) {
                $slug = unique_slug($table, $name, $id);
                db()->prepare("UPDATE `$table` SET name=?, slug=?, is_active=? WHERE id=?")
                    ->execute([$name, $slug, $active, $id]);
            } else {
                db()->prepare("UPDATE `$table` SET name=?, is_active=? WHERE id=?")
                    ->execute([$name, $active, $id]);
            }
            set_flash('success', $cfg['singular'] . ' updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $col = $type === 'category' ? 'category_id' : ($type . '_id');
            $chk = db()->prepare("SELECT COUNT(*) FROM products WHERE $col = ?");
            $chk->execute([$id]);
            if ((int) $chk->fetchColumn() > 0) {
                set_flash('error', 'Cannot delete — one or more gemstones still use this ' . strtolower($cfg['singular']) . '. Reassign them first.');
            } else {
                db()->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
                set_flash('success', $cfg['singular'] . ' deleted.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . urlencode($type));
    exit;
}

$items = db()->query("SELECT * FROM `$table` ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2><?= e($cfg['label']) ?></h2>
    </div>
    <p class="card-sub">These options appear as filters on the gemstone catalogue and as dropdowns when adding a gemstone.</p>

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
                        <form method="post" onsubmit="return confirm('Delete this <?= e(strtolower($cfg['singular'])) ?>?')">
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
        <h2 style="font-size:15px;margin-bottom:14px;">Add New <?= e($cfg['singular']) ?></h2>
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add <?= e($cfg['singular']) ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
