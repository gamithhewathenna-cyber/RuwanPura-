<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$filters = product_filters_from_get();
$page = max(1, (int) ($_GET['page'] ?? 1));

$result = get_products($filters, $page, 12);

$categories   = get_categories();
$shapes       = get_shapes();
$treatments   = get_treatments();
$origins      = get_origins();
$weightRanges = weight_ranges();
$statusLabels = product_status_labels();
$activeFilterCount = array_sum(array_map('count', $filters));

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
        <div class="catalogue-toolbar-row">
            <div class="catalogue-search-wrap reveal">
                <div class="catalogue-search">
                    <svg class="catalogue-search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input type="text" id="gemSearchInput" placeholder="Search gemstones by name…" autocomplete="off" aria-label="Search gemstones">
                    <div id="gemSearchResults" class="catalogue-search-results"></div>
                </div>
            </div>

            <button type="button" id="filtersToggleBtn" class="catalogue-filter-toggle" aria-label="Open filters">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                <span class="filters-btn-label">Filters</span>
                <?php if ($activeFilterCount > 0): ?><span class="filter-count-badge"><?= (int) $activeFilterCount ?></span><?php endif; ?>
            </button>
        </div>
        <div id="filtersBackdrop" class="filters-backdrop"></div>

        <div class="catalogue-layout">
            <aside class="catalogue-filters" id="catalogueFilters">
                <div class="filters-drawer-header">
                    <h3>Filters</h3>
                    <button type="button" id="filtersCloseBtn" class="filters-drawer-close" aria-label="Close filters">&times;</button>
                </div>
                <form method="get" id="filterForm">
                    <div class="filters-drawer-body">
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
                    </div>

                    <div class="filters-drawer-footer">
                        <a href="<?= BASE_URL ?>gemstones.php" class="btn-outline filters-clear-mobile">Clear All</a>
                        <button type="submit" class="btn-dark filters-apply-mobile">Show Products</button>
                    </div>
                </form>
            </aside>

            <div class="catalogue-main" id="catalogueResults">
                <?php include __DIR__ . '/includes/gemstone-results.php'; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
