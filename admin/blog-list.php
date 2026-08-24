<?php
$page_title = 'Insights: Posts';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('blog');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($action === 'delete') {
            $row = db()->prepare("SELECT featured_image, og_image FROM blog_posts WHERE id=?");
            $row->execute([$id]);
            $p = $row->fetch();
            if ($p) {
                if ($p['featured_image'] && file_exists(UPLOAD_DIR . '/' . $p['featured_image'])) @unlink(UPLOAD_DIR . '/' . $p['featured_image']);
                if ($p['og_image'] && file_exists(UPLOAD_DIR . '/' . $p['og_image'])) @unlink(UPLOAD_DIR . '/' . $p['og_image']);
            }
            db()->prepare("DELETE FROM blog_posts WHERE id=?")->execute([$id]);
            set_flash('success', 'Post deleted.');
        }
        elseif ($action === 'set_status') {
            $status = $_POST['status'] ?? 'draft';
            if (array_key_exists($status, blog_status_labels())) {
                if ($status === 'published') {
                    // Stamp published_at the first time a post goes live, so it
                    // doesn't silently get today's date reset on every save later.
                    $cur = db()->prepare("SELECT published_at FROM blog_posts WHERE id=?");
                    $cur->execute([$id]);
                    if (!$cur->fetchColumn()) {
                        db()->prepare("UPDATE blog_posts SET status=?, published_at=NOW() WHERE id=?")->execute([$status, $id]);
                    } else {
                        db()->prepare("UPDATE blog_posts SET status=? WHERE id=?")->execute([$status, $id]);
                    }
                } else {
                    db()->prepare("UPDATE blog_posts SET status=? WHERE id=?")->execute([$status, $id]);
                }
                set_flash('success', 'Status updated.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$posts = db()->query("
    SELECT p.*, c.name AS category_name
    FROM blog_posts p
    LEFT JOIN blog_categories c ON c.id = p.category_id
    ORDER BY p.created_at DESC, p.id DESC
")->fetchAll();

$statusLabels = blog_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2>Blog Posts</h2>
        <a href="<?= BASE_URL ?>admin/blog-edit.php" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Add Post
        </a>
    </div>
    <p class="card-sub">Every article on the Insights page. Click a title to edit it.</p>

    <table class="items-table">
        <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Published</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($posts as $p): ?>
            <tr>
                <td>
                    <?php if ($p['featured_image']): ?>
                        <img class="thumb" src="<?= UPLOAD_URL . e($p['featured_image']) ?>">
                    <?php else: ?>
                        <span class="badge">No image</span>
                    <?php endif; ?>
                </td>
                <td><a href="<?= BASE_URL ?>admin/blog-edit.php?id=<?= (int)$p['id'] ?>" style="font-weight:600;color:var(--dark);"><?= e($p['title']) ?></a></td>
                <td><?= e($p['category_name'] ?: '—') ?></td>
                <td><?= e($p['author_name'] ?: '—') ?></td>
                <td><?= $p['published_at'] ? e(date('M j, Y', strtotime($p['published_at']))) : '—' ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <select name="status" class="form-control" style="padding:6px 10px;font-size:12.5px;width:auto;" onchange="this.form.submit()">
                            <?php foreach ($statusLabels as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $p['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td class="row-actions">
                    <a href="<?= BASE_URL ?>admin/blog-edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this post?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$posts): ?>
            <tr><td colspan="7" style="color:var(--muted);text-align:center;padding:24px;">No posts yet. Click "Add Post" to write one.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
