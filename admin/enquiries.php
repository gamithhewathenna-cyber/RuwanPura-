<?php
$page_title = 'Gemstones: Enquiries';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('gemstones');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'mark_read') {
            db()->prepare("UPDATE enquiries SET is_read=1 WHERE id=?")->execute([$id]);
        } elseif ($action === 'mark_unread') {
            db()->prepare("UPDATE enquiries SET is_read=0 WHERE id=?")->execute([$id]);
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM enquiry_items WHERE enquiry_id=?")->execute([$id]);
            db()->prepare("DELETE FROM enquiries WHERE id=?")->execute([$id]);
            set_flash('success', 'Enquiry deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$enquiries = db()->query("SELECT * FROM enquiries ORDER BY created_at DESC")->fetchAll();
$itemsStmt = db()->prepare("SELECT * FROM enquiry_items WHERE enquiry_id=?");

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row"><h2>Gemstone Enquiries</h2></div>
    <p class="card-sub">Cart / enquiry submissions from the gemstone catalogue, newest first.</p>

    <?php if (!$enquiries): ?>
        <p style="color:var(--muted);font-size:14px;">No enquiries yet.</p>
    <?php endif; ?>

    <?php foreach ($enquiries as $en): $itemsStmt->execute([$en['id']]); $items = $itemsStmt->fetchAll(); ?>
        <div style="border:1px solid var(--line);border-radius:10px;padding:18px 20px;margin-bottom:14px;<?= $en['is_read'] ? '' : 'background:#fdf8ef;border-color:#eadfc7;' ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                <div>
                    <div style="font-weight:600;color:var(--dark);">
                        <?= e($en['full_name']) ?>
                        <?php if (!$en['is_read']): ?><span class="badge on" style="margin-left:8px;">New</span><?php endif; ?>
                    </div>
                    <div style="font-size:13px;color:var(--muted);margin-top:2px;">
                        <?= e($en['email']) ?><?php if ($en['phone']): ?> · <?= e($en['phone']) ?><?php endif; ?><?php if ($en['country']): ?> · <?= e($en['country']) ?><?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;"><?= e(date('M j, Y g:i A', strtotime($en['created_at']))) ?></div>
                </div>
                <div class="row-actions">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$en['id'] ?>">
                        <input type="hidden" name="action" value="<?= $en['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                        <button class="btn btn-sm"><?= $en['is_read'] ? 'Mark Unread' : 'Mark Read' ?></button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete this enquiry?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$en['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>

            <?php if ($items): ?>
                <table class="items-table" style="margin-top:14px;">
                    <thead><tr><th>Gemstone</th><th>Weight</th><th>Shape</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td><?php if ($it['product_id']): ?><a href="<?= BASE_URL ?>admin/gemstones-edit.php?id=<?= (int)$it['product_id'] ?>"><?= e($it['product_name']) ?></a><?php else: ?><?= e($it['product_name']) ?><?php endif; ?></td>
                                <td><?= $it['weight'] !== null ? e($it['weight']) . ' ct' : '—' ?></td>
                                <td><?= e($it['shape'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if ($en['message']): ?>
                <p style="margin-top:12px;font-size:14px;color:var(--text);white-space:pre-wrap;"><?= e($en['message']) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
