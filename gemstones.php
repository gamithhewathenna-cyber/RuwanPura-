<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$filters = product_filters_from_get();
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = get_products($filters, $page, 12);

$categories    = get_categories_tree();
$shapes        = get_shapes();
$treatments    = get_treatments();
$origins       = get_origins();
$weightRanges  = weight_ranges();
$priceRangeOpts = price_ranges();
$statusLabels  = product_status_labels();
$sortOptions   = sort_options();
$activeFilterCount = active_filter_count($filters);

include __DIR__ . '/includes/header.php';
?>

<!-- ================= CATALOGUE HERO ================= -->
<section class="catalogue-hero">
    <div class="container reveal-left">
        <div class="eyebrow">OUR COLLECTION</div>
        <h1 class="about-hero-title catalogue-hero-title">Gemstones</h1>
        <p class="catalogue-hero-desc">Discover our collection of natural, ethically sourced gemstones.</p>
    </div>
</section>

<!-- ================= GEMSTONE SHOP ================= -->
<section class="catalogue catalogue-shop">
    <div class="container">
        <div class="catalogue-layout">
            <aside class="catalogue-filters" id="catalogueFilters">
                <div class="filters-drawer-header">
                    <h3>Categories &amp; Filters</h3>
                    <button type="button" id="filtersCloseBtn" class="filters-drawer-close" aria-label="Close filters">&times;</button>
                </div>
                <form method="get" id="filterForm">
                    <div class="filters-drawer-body">

                    <!-- Category accordion — click a category/sub-category name to filter instantly;
                         the chevron only expands/collapses, it never selects anything. -->
                    <input type="hidden" name="category" id="categoryFilterInput" value="<?= !empty($filters['category']) ? (int) $filters['category'][0] : '' ?>">
                    <div class="cat-accordion">
                        <?php
                            $selectedCategory = !empty($filters['category']) ? (int) $filters['category'][0] : 0;
                            $catI = 0;
                            $catN = count($categories);
                            while ($catI < $catN):
                                $topCat = $categories[$catI];
                                $children = [];
                                $catJ = $catI + 1;
                                while ($catJ < $catN && !empty($categories[$catJ]['depth'])) {
                                    $children[] = $categories[$catJ];
                                    $catJ++;
                                }
                                $hasChildren  = !empty($children);
                                $childChecked = false;
                                foreach ($children as $ch) {
                                    if ((int) $ch['id'] === $selectedCategory) { $childChecked = true; break; }
                                }
                        ?>
                            <div class="cat-accordion-item<?= $childChecked ? ' is-open' : '' ?>">
                                <div class="cat-accordion-row">
                                    <button type="button" class="cat-accordion-select<?= (int) $topCat['id'] === $selectedCategory ? ' is-active' : '' ?>" data-id="<?= (int) $topCat['id'] ?>">
                                        <?= e($topCat['name']) ?>
                                    </button>
                                    <?php if ($hasChildren): ?>
                                        <button type="button" class="cat-accordion-toggle" aria-label="Show sub-categories" aria-expanded="<?= $childChecked ? 'true' : 'false' ?>">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php if ($hasChildren): ?>
                                    <div class="cat-accordion-panel<?= $childChecked ? ' open' : '' ?>">
                                        <?php foreach ($children as $ch): ?>
                                            <button type="button" class="subcat-select<?= (int) $ch['id'] === $selectedCategory ? ' is-active' : '' ?>" data-id="<?= (int) $ch['id'] ?>">
                                                <?= e($ch['name']) ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php
                                $catI = $catJ;
                            endwhile;
                        ?>
                    </div>

                    <!-- Filter: accordion -->
                    <div class="filter-acc-section">
                        <h3 class="filter-acc-heading">Filter:</h3>

                        <div class="filter-acc-item<?= !empty($filters['status']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['status']) ? 'true' : 'false' ?>">
                                <span>Availability</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($statusLabels as $key => $label): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="status[]" value="<?= e($key) ?>" <?= in_array($key, $filters['status']) ? 'checked' : '' ?>>
                                        <?= e($label) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-acc-item<?= !empty($filters['weight']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['weight']) ? 'true' : 'false' ?>">
                                <span>Carat Weight</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($weightRanges as $key => $r): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="weight[]" value="<?= e($key) ?>" <?= in_array($key, $filters['weight']) ? 'checked' : '' ?>>
                                        <?= e($r['label']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-acc-item<?= !empty($filters['shape']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['shape']) ? 'true' : 'false' ?>">
                                <span>Shape &amp; Cut</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($shapes as $s): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="shape[]" value="<?= (int) $s['id'] ?>" <?= in_array($s['id'], $filters['shape']) ? 'checked' : '' ?>>
                                        <?= e($s['name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-acc-item<?= !empty($filters['origin']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['origin']) ? 'true' : 'false' ?>">
                                <span>Origin</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($origins as $o): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="origin[]" value="<?= (int) $o['id'] ?>" <?= in_array($o['id'], $filters['origin']) ? 'checked' : '' ?>>
                                        <?= e($o['name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-acc-item<?= !empty($filters['treatment']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['treatment']) ? 'true' : 'false' ?>">
                                <span>Treatment</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($treatments as $t): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="treatment[]" value="<?= (int) $t['id'] ?>" <?= in_array($t['id'], $filters['treatment']) ? 'checked' : '' ?>>
                                        <?= e($t['name']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-acc-item<?= !empty($filters['price']) ? ' is-open' : '' ?>">
                            <button type="button" class="filter-acc-header" aria-expanded="<?= !empty($filters['price']) ? 'true' : 'false' ?>">
                                <span>Price</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                            </button>
                            <div class="filter-acc-panel">
                                <?php foreach ($priceRangeOpts as $key => $r): ?>
                                    <label class="filter-check">
                                        <input type="checkbox" name="price[]" value="<?= e($key) ?>" <?= in_array($key, $filters['price']) ? 'checked' : '' ?>>
                                        <?= e($r['label']) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-dark catalogue-apply-btn">Apply Filters</button>
                    <a href="<?= BASE_URL ?>gemstones.php" class="catalogue-clear">Clear all filters</a>
                    </div>

                    <div class="filters-drawer-footer">
                        <a href="<?= BASE_URL ?>gemstones.php" class="btn-outline filters-clear-mobile">Clear All</a>
                        <button type="submit" class="btn-dark filters-apply-mobile">Show Products</button>
                    </div>
                </form>
            </aside>

            <div class="catalogue-main">
                <div class="catalogue-toolbar-row">
                    <div class="catalogue-search-wrap reveal">
                        <div class="catalogue-search">
                            <svg class="catalogue-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" id="gemSearchInput" placeholder="Search gemstones by name…" autocomplete="off" aria-label="Search gemstones">
                            <div id="gemSearchResults" class="catalogue-search-results"></div>
                        </div>
                    </div>

                    <button type="button" id="filtersToggleBtn" class="catalogue-filter-toggle" aria-label="Open categories and filters">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        <span class="filters-btn-label">Categories &amp; Filters</span>
                        <?php if ($activeFilterCount > 0): ?><span class="filter-count-badge"><?= (int) $activeFilterCount ?></span><?php endif; ?>
                    </button>
                </div>
                <div id="filtersBackdrop" class="filters-backdrop"></div>

                <div id="catalogueResults">
                    <?php include __DIR__ . '/includes/gemstone-results.php'; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
