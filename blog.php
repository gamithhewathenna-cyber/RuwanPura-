<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$categories  = get_blog_categories();
$activeCatId = (int) ($_GET['category'] ?? 0);
$activeCat   = null;
foreach ($categories as $cat) {
    if ((int) $cat['id'] === $activeCatId) { $activeCat = $cat; break; }
}

$filters = $activeCatId ? ['category' => $activeCatId] : [];
$page    = max(1, (int) ($_GET['page'] ?? 1));
$result  = get_blog_posts($filters, $page, 9);

$pageTitle       = $activeCat ? $activeCat['name'] . ' — ' . c('blog_hero_title', 'Insights') : c('blog_hero_title', 'Insights');
$pageDescription = c('blog_hero_desc');
$canonicalUrl    = site_url('blog.php' . ($activeCatId ? '?category=' . $activeCatId : ''));

include __DIR__ . '/includes/header.php';
?>

<!-- ================= BLOG HERO ================= -->
<section class="catalogue-hero">
    <div class="container reveal-left">
        <div class="eyebrow"><?= e(c('blog_hero_eyebrow')) ?></div>
        <h1 class="about-hero-title catalogue-hero-title"><?= e(c('blog_hero_title', 'Insights')) ?></h1>
        <p class="catalogue-hero-desc"><?= e(c('blog_hero_desc')) ?></p>
    </div>
</section>

<!-- ================= BLOG LISTING ================= -->
<section class="blog">
    <div class="container">
        <?php if ($categories): ?>
            <div class="blog-filter-pills reveal">
                <a href="<?= BASE_URL ?>blog.php" class="blog-filter-pill <?= !$activeCatId ? 'active' : '' ?>">All Articles</a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= BASE_URL ?>blog.php?category=<?= (int) $cat['id'] ?>" class="blog-filter-pill <?= $activeCatId === (int) $cat['id'] ? 'active' : '' ?>"><?= e($cat['name']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="blog-toolbar">
            <span><?= (int) $result['total'] ?> article<?= $result['total'] == 1 ? '' : 's' ?><?= $activeCat ? ' in ' . e($activeCat['name']) : '' ?></span>
        </div>

        <?php if ($result['items']): ?>
            <div class="blog-grid reveal">
                <?php foreach ($result['items'] as $post): ?>
                    <a href="<?= BASE_URL ?>blog-post.php?slug=<?= urlencode($post['slug']) ?>" class="blog-card">
                        <div class="blog-card-img">
                            <?php if ($post['featured_image']): ?>
                                <img src="<?= UPLOAD_URL . e($post['featured_image']) ?>" alt="<?= e($post['title']) ?>">
                            <?php else: ?>
                                <div class="blog-card-noimg">No Image</div>
                            <?php endif; ?>
                            <?php if ($post['category_name']): ?><span class="blog-card-category"><?= e($post['category_name']) ?></span><?php endif; ?>
                        </div>
                        <div class="blog-card-body">
                            <h3 class="blog-card-title"><?= e($post['title']) ?></h3>
                            <?php if ($post['excerpt']): ?><p class="blog-card-excerpt"><?= e($post['excerpt']) ?></p><?php endif; ?>
                            <div class="blog-card-meta">
                                <?php if ($post['author_name']): ?><span><?= e($post['author_name']) ?></span><span class="dot"></span><?php endif; ?>
                                <span><?= e(date('M j, Y', strtotime($post['published_at']))) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($result['pages'] > 1): ?>
                <div class="blog-pagination">
                    <?php
                        $qs = $_GET;
                        $curPage = $result['page'];
                    ?>

                    <?php if ($curPage > 1): $qs['page'] = $curPage - 1; ?>
                        <a href="?<?= e(http_build_query($qs)) ?>" class="page-nav">&laquo; Previous</a>
                    <?php else: ?>
                        <span class="page-nav disabled">&laquo; Previous</span>
                    <?php endif; ?>

                    <?php for ($pg = 1; $pg <= $result['pages']; $pg++): $qs['page'] = $pg; ?>
                        <a href="?<?= e(http_build_query($qs)) ?>" class="<?= $pg === $curPage ? 'active' : '' ?>"><?= $pg ?></a>
                    <?php endfor; ?>

                    <?php if ($curPage < $result['pages']): $qs['page'] = $curPage + 1; ?>
                        <a href="?<?= e(http_build_query($qs)) ?>" class="page-nav">Next &raquo;</a>
                    <?php else: ?>
                        <span class="page-nav disabled">Next &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="blog-empty">No articles published yet. Check back soon.</p>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
