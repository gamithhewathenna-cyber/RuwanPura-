<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$slug = $_GET['slug'] ?? '';
$post = $slug !== '' ? get_post_by_slug($slug) : null;

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="container" style="padding:120px 0;text-align:center;">
        <h1 class="section-title">Article Not Found</h1>
        <p style="color:var(--muted);margin-top:14px;">This article may have been unpublished or removed.</p>
        <a href="<?= BASE_URL ?>blog.php" class="btn-dark" style="margin-top:24px;display:inline-block;">Back to Gemstone Insights</a>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$relatedPosts = get_related_posts($post, 3);

$postUrl   = site_url('blog-post.php?slug=' . urlencode($post['slug']));
$ogImgFile = $post['og_image'] ?: $post['featured_image'];

$pageTitle       = $post['seo_title'] ?: $post['title'];
$pageDescription = $post['seo_description'] ?: $post['excerpt'];
$canonicalUrl    = $post['canonical_url'] ?: $postUrl;
$ogType          = 'article';
$ogTitle         = $post['og_title'] ?: $pageTitle;
$ogDescription   = $post['og_description'] ?: $pageDescription;
$ogImage         = $ogImgFile ? UPLOAD_URL . $ogImgFile : '';
if ($ogImage && strpos($ogImage, 'http') !== 0) {
    $ogImage = site_url(ltrim($ogImage, '/'));
}

include __DIR__ . '/includes/header.php';
?>

<script type="application/ld+json">
<?= json_encode(array_filter([
    '@context'      => 'https://schema.org',
    '@type'         => 'Article',
    'headline'      => $post['title'],
    'description'   => $pageDescription,
    'image'         => $ogImage ?: null,
    'author'        => ['@type' => 'Organization', 'name' => $post['author_name'] ?: setting('site_name', 'Ruwanpura Gems')],
    'publisher'     => ['@type' => 'Organization', 'name' => setting('site_name', 'Ruwanpura Gems')],
    'datePublished' => $post['published_at'] ? date('c', strtotime($post['published_at'])) : null,
    'dateModified'  => $post['updated_at'] ? date('c', strtotime($post['updated_at'])) : null,
    'mainEntityOfPage' => $postUrl,
]), JSON_UNESCAPED_SLASHES) ?>
</script>

<!-- ================= ARTICLE HEADER ================= -->
<section class="about-hero">
    <div class="container reveal">
        <?php if ($post['category_name']): ?><div class="eyebrow"><?= e(strtoupper($post['category_name'])) ?></div><?php endif; ?>
        <h1 class="about-hero-title" style="font-size:44px;text-transform:none;"><?= e($post['title']) ?></h1>
        <div class="blog-post-meta">
            <?php if ($post['author_name']): ?><span><?= e($post['author_name']) ?></span><span class="dot"></span><?php endif; ?>
            <span><?= e(date('F j, Y', strtotime($post['published_at']))) ?></span>
        </div>
    </div>
</section>

<!-- ================= ARTICLE BODY ================= -->
<section class="blog-post">
    <div class="container">
        <?php if ($post['featured_image']): ?>
            <div class="blog-post-cover reveal-fade">
                <img src="<?= UPLOAD_URL . e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>">
            </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>blog.php" class="back-link" style="display:block;max-width:760px;margin:0 auto 24px;">&larr; Back to Gemstone Insights</a>

        <div class="blog-post-body reveal">
            <?= render_blog_content($post['content']) ?>
        </div>
    </div>
</section>

<?php if ($relatedPosts): ?>
<!-- ================= RELATED ARTICLES ================= -->
<section class="blog-related">
    <div class="container">
        <h2 class="blog-related-title">Related Articles</h2>
        <div class="blog-grid reveal">
            <?php foreach ($relatedPosts as $rp): ?>
                <a href="<?= BASE_URL ?>blog-post.php?slug=<?= urlencode($rp['slug']) ?>" class="blog-card">
                    <div class="blog-card-img">
                        <?php if ($rp['featured_image']): ?>
                            <img src="<?= UPLOAD_URL . e($rp['featured_image']) ?>" alt="<?= e($rp['title']) ?>">
                        <?php else: ?>
                            <div class="blog-card-noimg">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="blog-card-body">
                        <h3 class="blog-card-title"><?= e($rp['title']) ?></h3>
                        <div class="blog-card-meta">
                            <span><?= e(date('M j, Y', strtotime($rp['published_at']))) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
