<?php
$page_title = 'Messages';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'mark_read') {
            db()->prepare("UPDATE contact_messages SET is_read=1 WHERE id=?")->execute([$id]);
        } elseif ($action === 'mark_unread') {
            db()->prepare("UPDATE contact_messages SET is_read=0 WHERE id=?")->execute([$id]);
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM contact_messages WHERE id=?")->execute([$id]);
            set_flash('success', 'Message deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$messages = db()->query("SELECT * FROM contact_messages ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2>Contact Form Submissions</h2>
    </div>
    <p class="card-sub">Messages sent through the Contact Us page form, newest first.</p>

    <?php if (!$messages): ?>
        <p style="color:var(--muted);font-size:14px;">No messages yet.</p>
    <?php endif; ?>

    <?php foreach ($messages as $m): ?>
        <div style="border:1px solid var(--line);border-radius:10px;padding:18px 20px;margin-bottom:14px;<?= $m['is_read'] ? '' : 'background:#fdf8ef;border-color:#eadfc7;' ?>">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
                <div>
                    <div style="font-weight:600;color:var(--dark);">
                        <?= e($m['full_name']) ?>
                        <?php if (!$m['is_read']): ?><span class="badge on" style="margin-left:8px;">New</span><?php endif; ?>
                    </div>
                    <div style="font-size:13px;color:var(--muted);margin-top:2px;">
                        <?= e($m['email']) ?>
                        <?php if ($m['phone']): ?> · <?= e($m['phone']) ?><?php endif; ?>
                        <?php if ($m['company']): ?> · <?= e($m['company']) ?><?php endif; ?>
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:2px;"><?= e(date('M j, Y g:i A', strtotime($m['created_at']))) ?></div>
                </div>
                <div class="row-actions">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <input type="hidden" name="action" value="<?= $m['is_read'] ? 'mark_unread' : 'mark_read' ?>">
                        <button class="btn btn-sm"><?= $m['is_read'] ? 'Mark Unread' : 'Mark Read' ?></button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete this message?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </div>
            </div>
            <p style="margin-top:12px;font-size:14px;color:var(--text);white-space:pre-wrap;"><?= e($m['message']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
