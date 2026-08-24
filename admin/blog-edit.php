<?php
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('blog');

$id   = (int)($_GET['id'] ?? 0);
$post = null;
if ($id) {
    $stmt = db()->prepare("SELECT * FROM blog_posts WHERE id=?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) {
        header('Location: ' . BASE_URL . 'admin/blog-list.php');
        exit;
    }
}
$page_title = $post ? 'Edit Post' : 'Add Post';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'save') {
            $title       = trim($_POST['title'] ?? '');
            $customSlug  = trim($_POST['slug'] ?? '');
            $categoryId  = (int)($_POST['category_id'] ?? 0) ?: null;
            $excerpt     = trim($_POST['excerpt'] ?? '');
            $content     = trim($_POST['content'] ?? '');
            $authorName  = trim($_POST['author_name'] ?? '');
            $status      = $_POST['status'] ?? 'draft';
            if (!array_key_exists($status, blog_status_labels())) $status = 'draft';

            $publishedAtRaw = trim($_POST['published_at'] ?? '');
            $publishedAt    = $publishedAtRaw !== '' ? str_replace('T', ' ', $publishedAtRaw) . ':00' : null;
            if ($status === 'published' && !$publishedAt) $publishedAt = date('Y-m-d H:i:s');

            $seoTitle       = trim($_POST['seo_title'] ?? '');
            $seoDescription = trim($_POST['seo_description'] ?? '');
            $seoKeyphrase   = trim($_POST['seo_keyphrase'] ?? '');
            $canonicalUrl   = trim($_POST['canonical_url'] ?? '');
            $ogTitle        = trim($_POST['og_title'] ?? '');
            $ogDescription  = trim($_POST['og_description'] ?? '');

            if ($title === '') {
                set_flash('error', 'Please enter a post title.');
                header('Location: ' . BASE_URL . 'admin/blog-edit.php' . ($id ? '?id=' . $id : ''));
                exit;
            }

            $slugSource = $customSlug !== '' ? $customSlug : $title;

            if ($id) {
                $slug          = unique_slug('blog_posts', $slugSource, $id);
                $featuredImage = handle_upload('featured_image', $post['featured_image']);
                $ogImage       = handle_upload('og_image', $post['og_image']);
                $stmt = db()->prepare("UPDATE blog_posts SET title=?, slug=?, category_id=?, excerpt=?, content=?, featured_image=?, author_name=?, status=?, published_at=?, seo_title=?, seo_description=?, seo_keyphrase=?, canonical_url=?, og_title=?, og_description=?, og_image=? WHERE id=?");
                $stmt->execute([$title, $slug, $categoryId, $excerpt, $content, $featuredImage, $authorName, $status, $publishedAt, $seoTitle, $seoDescription, $seoKeyphrase, $canonicalUrl, $ogTitle, $ogDescription, $ogImage, $id]);
            } else {
                $slug          = unique_slug('blog_posts', $slugSource);
                $featuredImage = handle_upload('featured_image', '');
                $ogImage       = handle_upload('og_image', '');
                $stmt = db()->prepare("INSERT INTO blog_posts (title, slug, category_id, excerpt, content, featured_image, author_name, status, published_at, seo_title, seo_description, seo_keyphrase, canonical_url, og_title, og_description, og_image) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$title, $slug, $categoryId, $excerpt, $content, $featuredImage, $authorName, $status, $publishedAt, $seoTitle, $seoDescription, $seoKeyphrase, $canonicalUrl, $ogTitle, $ogDescription, $ogImage]);
                $id = (int) db()->lastInsertId();
            }

            set_flash('success', 'Post saved.');
            header('Location: ' . BASE_URL . 'admin/blog-edit.php?id=' . $id);
            exit;
        }
    }
    header('Location: ' . BASE_URL . 'admin/blog-edit.php' . ($id ? '?id=' . $id : ''));
    exit;
}

$categories   = get_blog_categories(false);
$statusLabels = blog_status_labels();

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">

    <div class="card">
        <div class="card-head-row">
            <div><h2><?= $post ? 'Edit Post' : 'Add Post' ?></h2>
            <p class="card-sub" style="margin:4px 0 0;">Core article content shown on Insights.</p></div>
            <button type="submit" class="btn btn-primary">Save Post</button>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" value="<?= e($post['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>URL Slug <span class="hint">(optional — auto-generated from the title if left blank)</span></label>
                <input type="text" name="slug" class="form-control" value="<?= e($post['slug'] ?? '') ?>" placeholder="e.g. how-to-choose-a-sapphire">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— Select —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (isset($post['category_id']) && (int)$post['category_id'] === (int)$c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Author</label>
                <input type="text" name="author_name" class="form-control" value="<?= e($post['author_name'] ?? '') ?>" placeholder="e.g. Ruwanpura Gems Team">
            </div>
        </div>

        <div class="form-group">
            <label>Excerpt <span class="hint">(short summary shown on listing cards and in search results)</span></label>
            <textarea name="excerpt" class="form-control" rows="2"><?= e($post['excerpt'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>Full Content</label>
            <textarea name="content" class="form-control" rows="14"><?= e($post['content'] ?? '') ?></textarea>
            <div class="hint" style="margin-top:6px;">Leave a blank line between paragraphs — each will become its own paragraph on the page.</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= (isset($post['status']) ? $post['status'] === $key : $key === 'draft') ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Published Date <span class="hint">(leave blank to use "now" when you publish)</span></label>
                <input type="datetime-local" name="published_at" class="form-control"
                       value="<?= !empty($post['published_at']) ? e(date('Y-m-d\TH:i', strtotime($post['published_at']))) : '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Featured Image</label>
            <?php if (!empty($post['featured_image'])): ?>
                <div class="img-preview" style="margin-bottom:10px;"><img src="<?= UPLOAD_URL . e($post['featured_image']) ?>"></div>
            <?php endif; ?>
            <input type="file" name="featured_image" accept="image/*">
            <div class="hint" style="margin-top:6px;">Recommended size: 1200 × 800px.</div>
        </div>
    </div>

    <div class="card">
        <h2>SEO &amp; Social Sharing</h2>
        <p class="card-sub">Controls how this post appears in Google search results and when shared on social media.</p>

        <div class="form-group">
            <label>SEO Meta Title <span class="hint">(falls back to the post title if left blank)</span></label>
            <input type="text" name="seo_title" class="form-control" value="<?= e($post['seo_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Meta Description <span class="hint">(falls back to the excerpt if left blank)</span></label>
            <textarea name="seo_description" class="form-control" rows="2"><?= e($post['seo_description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Focus Keyphrase</label>
                <input type="text" name="seo_keyphrase" class="form-control" value="<?= e($post['seo_keyphrase'] ?? '') ?>" placeholder="e.g. blue sapphire buying guide">
            </div>
            <div class="form-group">
                <label>Canonical URL <span class="hint">(optional — only set this if the article is republished elsewhere)</span></label>
                <input type="text" name="canonical_url" class="form-control" value="<?= e($post['canonical_url'] ?? '') ?>" placeholder="https://...">
            </div>
        </div>

        <div class="section-divider"></div>

        <div class="form-group">
            <label>Open Graph / Social Sharing Title <span class="hint">(falls back to SEO Meta Title, then post title)</span></label>
            <input type="text" name="og_title" class="form-control" value="<?= e($post['og_title'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Open Graph Description <span class="hint">(falls back to Meta Description, then excerpt)</span></label>
            <textarea name="og_description" class="form-control" rows="2"><?= e($post['og_description'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label>Social Sharing Image <span class="hint">(falls back to the Featured Image if left blank)</span></label>
            <?php if (!empty($post['og_image'])): ?>
                <div class="img-preview" style="margin-bottom:10px;"><img src="<?= UPLOAD_URL . e($post['og_image']) ?>"></div>
            <?php endif; ?>
            <input type="file" name="og_image" accept="image/*">
            <div class="hint" style="margin-top:6px;">Recommended size: 1200 × 630px.</div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
