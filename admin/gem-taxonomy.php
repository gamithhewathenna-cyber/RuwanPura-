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
$isCategory = $type === 'category';

$page_title = 'Gemstones: ' . $cfg['label'];

require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('gemstones');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name     = trim($_POST['name'] ?? '');
            $parentId = $isCategory ? ((int)($_POST['parent_id'] ?? 0) ?: null) : null;
            if ($name !== '') {
                $ord = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM `$table`")->fetchColumn();
                if ($isCategory) {
                    $slug = unique_slug($table, $name);
                    db()->prepare("INSERT INTO `$table` (name, slug, parent_id, sort_order, is_active) VALUES (?,?,?,?,1)")
                        ->execute([$name, $slug, $parentId, $ord]);
                } elseif ($cfg['has_slug']) {
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
            $id       = (int)($_POST['id'] ?? 0);
            $name     = trim($_POST['name'] ?? '');
            $active   = isset($_POST['is_active']) ? 1 : 0;
            $parentId = $isCategory ? ((int)($_POST['parent_id'] ?? 0) ?: null) : null;
            if ($isCategory) {
                if ($parentId === $id) $parentId = null; // can't be its own parent
                $slug = unique_slug($table, $name, $id);
                db()->prepare("UPDATE `$table` SET name=?, slug=?, parent_id=?, is_active=? WHERE id=?")
                    ->execute([$name, $slug, $parentId, $active, $id]);
            } elseif ($cfg['has_slug']) {
                $slug = unique_slug($table, $name, $id);
                db()->prepare("UPDATE `$table` SET name=?, slug=?, is_active=? WHERE id=?")
                    ->execute([$name, $slug, $active, $id]);
            } else {
                db()->prepare("UPDATE `$table` SET name=?, is_active=? WHERE id=?")
                    ->execute([$name, $active, $id]);
            }
            set_flash('success', $cfg['singular'] . ' updated.');
        }
        elseif ($action === 'bulk_save') {
            $rows  = $_POST['rows'] ?? [];
            $count = 0;
            foreach ($rows as $rid => $rowData) {
                $rid  = (int) $rid;
                $name = trim($rowData['name'] ?? '');
                if ($rid <= 0 || $name === '') continue;
                $active = isset($rowData['is_active']) ? 1 : 0;
                if ($isCategory) {
                    $parentId = (int) ($rowData['parent_id'] ?? 0) ?: null;
                    if ($parentId === $rid) $parentId = null; // can't be its own parent
                    $slug = unique_slug($table, $name, $rid);
                    db()->prepare("UPDATE `$table` SET name=?, slug=?, parent_id=?, is_active=? WHERE id=?")
                        ->execute([$name, $slug, $parentId, $active, $rid]);
                } elseif ($cfg['has_slug']) {
                    $slug = unique_slug($table, $name, $rid);
                    db()->prepare("UPDATE `$table` SET name=?, slug=?, is_active=? WHERE id=?")
                        ->execute([$name, $slug, $active, $rid]);
                } else {
                    db()->prepare("UPDATE `$table` SET name=?, is_active=? WHERE id=?")
                        ->execute([$name, $active, $rid]);
                }
                $count++;
            }
            set_flash('success', $count . ' ' . strtolower($cfg['label']) . ' updated.');
        }
        elseif ($action === 'delete') {
            $id  = (int)($_POST['id'] ?? 0);
            $col = $type === 'category' ? 'category_id' : ($type . '_id');
            $chk = db()->prepare("SELECT COUNT(*) FROM products WHERE $col = ?");
            $chk->execute([$id]);
            $childChk = 0;
            if ($isCategory) {
                $c = db()->prepare("SELECT COUNT(*) FROM gem_categories WHERE parent_id = ?");
                $c->execute([$id]);
                $childChk = (int) $c->fetchColumn();
            }
            if ((int) $chk->fetchColumn() > 0) {
                set_flash('error', 'Cannot delete — one or more gemstones still use this ' . strtolower($cfg['singular']) . '. Reassign them first.');
            } elseif ($childChk > 0) {
                set_flash('error', 'Cannot delete — this category still has sub-categories. Reassign or delete them first.');
            } else {
                db()->prepare("DELETE FROM `$table` WHERE id=?")->execute([$id]);
                set_flash('success', $cfg['singular'] . ' deleted.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF'] . '?type=' . urlencode($type));
    exit;
}

$items = $isCategory
    ? get_categories_tree(false)
    : db()->query("SELECT * FROM `$table` ORDER BY sort_order, id")->fetchAll();

// Only top-level categories can be chosen as a parent — keeps the hierarchy to 2 levels
$topLevelCategories = $isCategory ? array_values(array_filter($items, function ($i) { return empty($i['parent_id']); })) : [];

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2><?= e($cfg['label']) ?></h2>
    </div>
    <p class="card-sub">These options appear as filters on the gemstone catalogue and as dropdowns when adding a gemstone.</p>

    <table class="items-table">
        <thead><tr><th>Name</th><?php if ($isCategory): ?><th>Parent Category</th><?php endif; ?><th>Active</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
            <tr>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$it['id'] ?>">
                    <td>
                        <?php if ($isCategory && !empty($it['depth'])): ?><span class="hint" style="margin-right:4px;">&#8627;</span><?php endif; ?>
                        <input type="text" name="name" value="<?= e($it['name']) ?>" class="form-control taxonomy-name-input" data-row-id="<?= (int)$it['id'] ?>" style="min-width:220px;<?= ($isCategory && !empty($it['depth'])) ? 'display:inline-block;width:calc(100% - 20px);' : '' ?>">
                    </td>
                    <?php if ($isCategory): ?>
                        <td>
                            <select name="parent_id" class="form-control taxonomy-parent-input" data-row-id="<?= (int)$it['id'] ?>" style="padding:6px 10px;font-size:12.5px;width:auto;">
                                <option value="">— None (top-level) —</option>
                                <?php foreach ($topLevelCategories as $top): if ((int)$top['id'] === (int)$it['id']) continue; ?>
                                    <option value="<?= (int)$top['id'] ?>" <?= (int)($it['parent_id'] ?? 0) === (int)$top['id'] ? 'selected' : '' ?>><?= e($top['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    <?php endif; ?>
                    <td><label style="font-size:13px;"><input type="checkbox" name="is_active" class="taxonomy-active-input" data-row-id="<?= (int)$it['id'] ?>" <?= $it['is_active'] ? 'checked' : '' ?>> Active</label></td>
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
            <tr><td colspan="<?= $isCategory ? 4 : 3 ?>" style="color:var(--muted);text-align:center;padding:20px;">None yet — add one below.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ($items): ?>
        <div style="text-align:right;margin-top:14px;">
            <button type="button" class="btn btn-primary" id="bulkSaveBtn">Save All</button>
        </div>
        <form method="post" id="bulkSaveForm" style="display:none;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="bulk_save">
            <div id="bulkSaveFields"></div>
        </form>
    <?php endif; ?>

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
            <?php if ($isCategory): ?>
                <div class="form-group">
                    <label>Parent Category <span class="hint">(optional — leave blank for a top-level category)</span></label>
                    <select name="parent_id" class="form-control">
                        <option value="">— None (top-level) —</option>
                        <?php foreach ($topLevelCategories as $top): ?>
                            <option value="<?= (int)$top['id'] ?>"><?= e($top['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Add <?= e($cfg['singular']) ?></button>
    </form>
</div>

<script>
(function () {
    var btn = document.getElementById('bulkSaveBtn');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var container = document.getElementById('bulkSaveFields');
        container.innerHTML = '';

        document.querySelectorAll('.taxonomy-name-input').forEach(function (input) {
            var id = input.getAttribute('data-row-id');
            var field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'rows[' + id + '][name]';
            field.value = input.value;
            container.appendChild(field);
        });
        document.querySelectorAll('.taxonomy-active-input').forEach(function (chk) {
            if (!chk.checked) return;
            var id = chk.getAttribute('data-row-id');
            var field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'rows[' + id + '][is_active]';
            field.value = '1';
            container.appendChild(field);
        });
        document.querySelectorAll('.taxonomy-parent-input').forEach(function (select) {
            var id = select.getAttribute('data-row-id');
            var field = document.createElement('input');
            field.type = 'hidden';
            field.name = 'rows[' + id + '][parent_id]';
            field.value = select.value;
            container.appendChild(field);
        });

        document.getElementById('bulkSaveForm').submit();
    });
})();
</script>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
