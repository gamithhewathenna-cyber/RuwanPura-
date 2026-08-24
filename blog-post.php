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
        <a href="<?= BASE_URL ?>blog.php" class="btn-dark" style="margin-top:24px;display:inline-block;">Back to Insights</a>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$relatedPosts = get_related_posts($post, 3);
$recentPosts  = get_recent_posts($post['id'], 5);

$postUrl   = site_url('blog-post.php?slug=' . urlencode($post['slug']));
$ogImgFile = $post['og_image'] ?: $post['featured_image'];

$shareUrlEnc   = urlencode($postUrl);
$shareTitleEnc = urlencode($post['title']);
$shareLinks = [
    'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $shareUrlEnc,
    'x'        => 'https://twitter.com/intent/tweet?url=' . $shareUrlEnc . '&text=' . $shareTitleEnc,
    'whatsapp' => 'https://api.whatsapp.com/send?text=' . $shareTitleEnc . '%20' . $shareUrlEnc,
    'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $shareUrlEnc,
    'email'    => 'mailto:?subject=' . $shareTitleEnc . '&body=' . $shareUrlEnc,
];

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

        <a href="<?= BASE_URL ?>blog.php" class="back-link">&larr; Back to Insights</a>

        <div class="blog-post-layout">
            <div class="blog-post-main">
                <div class="blog-post-body reveal">
                    <?= render_blog_content($post['content']) ?>
                </div>
            </div>

            <aside class="blog-post-sidebar">
                <div class="blog-sidebar-widget">
                    <h3 class="blog-sidebar-title">Share This Article</h3>
                    <div class="blog-share-buttons">
                        <a href="<?= e($shareLinks['facebook']) ?>" class="blog-share-btn" target="_blank" rel="noopener" aria-label="Share on Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13 22v-8h3l.5-3.5H13V8.3c0-1 .3-1.7 1.8-1.7H17V3.5c-.3 0-1.4-.1-2.6-.1-2.6 0-4.4 1.6-4.4 4.5v2.6H7V14h3v8z"/></svg>
                        </a>
                        <a href="<?= e($shareLinks['x']) ?>" class="blog-share-btn" target="_blank" rel="noopener" aria-label="Share on X (Twitter)">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 5.9c-.7.3-1.5.5-2.3.6.8-.5 1.5-1.3 1.8-2.3-.8.5-1.7.8-2.6 1a4 4 0 0 0-6.9 3.7A11.4 11.4 0 0 1 3.8 4.6a4 4 0 0 0 1.2 5.4c-.6 0-1.2-.2-1.8-.5a4 4 0 0 0 3.2 4c-.5.1-1.1.2-1.7.1a4 4 0 0 0 3.7 2.8A8 8 0 0 1 2 18.1 11.3 11.3 0 0 0 8.1 20c7.4 0 11.5-6.2 11.5-11.5v-.5c.8-.6 1.5-1.3 2-2.1z"/></svg>
                        </a>
                        <a href="<?= e($shareLinks['whatsapp']) ?>" class="blog-share-btn" target="_blank" rel="noopener" aria-label="Share on WhatsApp">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 6.3A8 8 0 0 0 4.7 16L4 20l4.1-.7A8 8 0 1 0 17.6 6.3zM12 18.5a6.4 6.4 0 0 1-3.3-.9l-.2-.1-2.4.4.4-2.3-.2-.2A6.5 6.5 0 1 1 12 18.5zm3.6-4.9c-.2-.1-1.2-.6-1.4-.7-.2-.1-.3-.1-.4.1-.1.2-.5.7-.6.8-.1.1-.2.1-.4 0a5.3 5.3 0 0 1-1.6-1 5.9 5.9 0 0 1-1.1-1.3c-.1-.2 0-.3.1-.4l.3-.3.2-.3c.1-.1 0-.2 0-.3l-.6-1.5c-.2-.4-.3-.3-.4-.3h-.4a.7.7 0 0 0-.5.2 2.2 2.2 0 0 0-.7 1.6c0 .9.7 1.9.8 2 .1.1 1.4 2.1 3.3 3 .5.2.8.3 1.1.4a2.6 2.6 0 0 0 1.2.1c.4-.1 1.2-.5 1.3-1 .2-.4.2-.8.1-.9-.1-.1-.2-.1-.4-.2z"/></svg>
                        </a>
                        <a href="<?= e($shareLinks['linkedin']) ?>" class="blog-share-btn" target="_blank" rel="noopener" aria-label="Share on LinkedIn">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9h4v12H3zM9 9h3.8v1.7h.1c.5-1 1.8-2 3.7-2 4 0 4.7 2.6 4.7 6V21h-4v-5.5c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9V21H9z"/></svg>
                        </a>
                        <a href="<?= e($shareLinks['email']) ?>" class="blog-share-btn" aria-label="Share by Email">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
                        </a>
                    </div>
                </div>

                <?php if ($recentPosts): ?>
                <div class="blog-sidebar-widget">
                    <h3 class="blog-sidebar-title">Recent Articles</h3>
                    <div class="blog-recent-list">
                        <?php foreach ($recentPosts as $rp): ?>
                            <a href="<?= BASE_URL ?>blog-post.php?slug=<?= urlencode($rp['slug']) ?>" class="blog-recent-item">
                                <div class="blog-recent-thumb">
                                    <?php if ($rp['featured_image']): ?>
                                        <img src="<?= UPLOAD_URL . e($rp['featured_image']) ?>" alt="<?= e($rp['title']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="blog-recent-info">
                                    <h4><?= e($rp['title']) ?></h4>
                                    <span><?= e(date('M j, Y', strtotime($rp['published_at']))) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
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
