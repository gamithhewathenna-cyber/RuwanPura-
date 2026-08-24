<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$filters = [
    'category'  => array_map('intval', $_GET['category'] ?? []),
    'shape'     => array_map('intval', $_GET['shape'] ?? []),
    'treatment' => array_map('intval', $_GET['treatment'] ?? []),
    'origin'    => array_map('intval', $_GET['origin'] ?? []),
    'weight'    => array_values(array_intersect((array) ($_GET['weight'] ?? []), array_keys(weight_ranges()))),
    'status'    => array_values(array_intersect((array) ($_GET['status'] ?? []), array_keys(product_status_labels()))),
];
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = get_products($filters, $page, 12);

$categories   = get_categories();
$shapes       = get_shapes();
$treatments   = get_treatments();
$origins      = get_origins();
$weightRanges = weight_ranges();
$statusLabels = product_status_labels();

include __DIR__ . '/includes/header.php';
?>

<!-- ================= CATALOGUE HERO ================= -->
<section class="catalogue-hero">
    <div class="container reveal">
        <div class="eyebrow">OUR COLLECTION</div>
        <h1 class="about-hero-title catalogue-hero-title">Gemstone Catalogue</h1>
        <p class="catalogue-hero-desc">Discover a curated collection of exceptional natural gemstones, selected for their beauty, rarity, and timeless character.</p>
    </div>
</section>

<!-- ================= CATALOGUE ================= -->
<section class="catalogue">
    <div class="container">
        <div class="catalogue-search-wrap reveal">
            <div class="catalogue-search">
                <svg class="catalogue-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="text" id="gemSearchInput" placeholder="Search gemstones by name…" autocomplete="off" aria-label="Search gemstones">
                <div id="gemSearchResults" class="catalogue-search-results"></div>
            </div>
        </div>

        <div class="catalogue-layout">
            <aside class="catalogue-filters">
                <form method="get" id="filterForm">
                    <div class="filter-group">
                        <h4>Category</h4>
                        <?php foreach ($categories as $c): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="category[]" value="<?= (int) $c['id'] ?>" <?= in_array($c['id'], $filters['category']) ? 'checked' : '' ?>>
                                <?= e($c['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Weight</h4>
                        <?php foreach ($weightRanges as $key => $r): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="weight[]" value="<?= e($key) ?>" <?= in_array($key, $filters['weight']) ? 'checked' : '' ?>>
                                <?= e($r['label']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Shape</h4>
                        <?php foreach ($shapes as $s): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="shape[]" value="<?= (int) $s['id'] ?>" <?= in_array($s['id'], $filters['shape']) ? 'checked' : '' ?>>
                                <?= e($s['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Treatment</h4>
                        <?php foreach ($treatments as $t): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="treatment[]" value="<?= (int) $t['id'] ?>" <?= in_array($t['id'], $filters['treatment']) ? 'checked' : '' ?>>
                                <?= e($t['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Origin</h4>
                        <?php foreach ($origins as $o): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="origin[]" value="<?= (int) $o['id'] ?>" <?= in_array($o['id'], $filters['origin']) ? 'checked' : '' ?>>
                                <?= e($o['name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group">
                        <h4>Availability</h4>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <label class="filter-check">
                                <input type="checkbox" name="status[]" value="<?= e($key) ?>" <?= in_array($key, $filters['status']) ? 'checked' : '' ?>>
                                <?= e($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" class="btn-dark catalogue-apply-btn">Apply Filters</button>
                    <a href="<?= BASE_URL ?>gemstones.php" class="catalogue-clear">Clear all filters</a>
                </form>
            </aside>

            <div class="catalogue-main">
                <div class="catalogue-toolbar">
                    <span><?= (int) $result['total'] ?> gemstone<?= $result['total'] == 1 ? '' : 's' ?> found</span>
                </div>

                <?php if ($result['items']): ?>
                    <div class="product-grid reveal">
                        <?php foreach ($result['items'] as $p): ?>
                            <a href="<?= BASE_URL ?>gemstone.php?slug=<?= urlencode($p['slug']) ?>" class="product-card">
                                <div class="product-card-img">
                                    <?php if ($p['thumb']): ?>
                                        <img src="<?= UPLOAD_URL . e($p['thumb']) ?>" alt="<?= e($p['name']) ?>">
                                    <?php else: ?>
                                        <div class="product-card-noimg">No Image</div>
                                    <?php endif; ?>
                                    <span class="product-status-badge status-<?= e($p['status']) ?>"><?= e($statusLabels[$p['status']]) ?></span>
                                </div>
                                <div class="product-card-body">
                                    <h3><?= e($p['name']) ?></h3>
                                    <p class="product-card-meta">
                                        <?php
                                            $meta = [];
                                            if ($p['weight'] !== null) $meta[] = $p['weight'] . ' ct';
                                            $shapeName = lookup_name('gem_shapes', $p['shape_id']);
                                            if ($shapeName) $meta[] = $shapeName;
                                            echo e(implode(' · ', $meta));
                                        ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($result['pages'] > 1): ?>
                        <div class="catalogue-pagination">
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
                    <p class="catalogue-empty">No gemstones match your filters. Try clearing some filters.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
