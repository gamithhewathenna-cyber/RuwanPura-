<?php
/**
 * Catalogue results fragment — the count/sort toolbar, product grid, and pagination.
 * Expects $result (from get_products()), $statusLabels, $sortOptions, and $filters
 * in scope. Included by gemstones.php (full page) and filter-products.php (AJAX partial).
 */
$currentSort = $filters['sort'] ?? 'featured';
$start = $result['total'] > 0 ? (($result['page'] - 1) * $result['per_page']) + 1 : 0;
$end   = min($result['total'], $result['page'] * $result['per_page']);
?>
<div class="catalogue-toolbar">
    <span class="catalogue-count">
        <?php if ($result['total'] > 0): ?>
            Showing <?= (int) $start ?> – <?= (int) $end ?> of <?= (int) $result['total'] ?> gemstone<?= $result['total'] == 1 ? '' : 's' ?>
        <?php else: ?>
            No gemstones found
        <?php endif; ?>
    </span>
    <div class="catalogue-sort">
        <label for="sortSelect">Sort by:</label>
        <select id="sortSelect" name="sort" form="filterForm">
            <?php foreach ($sortOptions as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $currentSort === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
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
                    <span class="product-card-wishlist" role="button" tabindex="0" data-id="<?= (int) $p['id'] ?>" aria-label="Save to wishlist" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7.5-4.6-10-9.1C.5 8.6 2.3 5 6 5c2 0 3.5 1 6 3.5C14.5 6 16 5 18 5c3.7 0 5.5 3.6 4 6.9-2.5 4.5-10 9.1-10 9.1z"/></svg>
                    </span>
                </div>
                <div class="product-card-body">
                    <h3><?= e($p['name']) ?></h3>
                    <p class="product-card-meta">
                        <?php
                            $meta = [];
                            if ($p['weight'] !== null) $meta[] = $p['weight'] . ' ct';
                            $shapeName = lookup_name('gem_shapes', $p['shape_id']);
                            if ($shapeName) $meta[] = $shapeName;
                            $originName = lookup_name('gem_origins', $p['origin_id']);
                            if ($originName) $meta[] = $originName;
                            echo e(implode(' · ', $meta));
                        ?>
                    </p>
                    <?php $cardPricing = product_pricing($p); if ($cardPricing['original'] !== null): ?>
                        <p class="cart-item-price" style="margin-top:8px;">
                            <?php if ($cardPricing['has_discount']): ?>
                                <span class="price-was"><?= format_money($cardPricing['original']) ?></span>
                                <span class="price-now"><?= format_money($cardPricing['final']) ?></span>
                            <?php else: ?>
                                <span class="price-now"><?= format_money($cardPricing['final']) ?></span>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
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
                <a href="?<?= e(http_build_query($qs)) ?>" class="page-nav" aria-label="Previous page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            <?php else: ?>
                <span class="page-nav disabled" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </span>
            <?php endif; ?>

            <?php for ($pg = 1; $pg <= $result['pages']; $pg++): $qs['page'] = $pg; ?>
                <a href="?<?= e(http_build_query($qs)) ?>" class="<?= $pg === $curPage ? 'active' : '' ?>"><?= $pg ?></a>
            <?php endfor; ?>

            <?php if ($curPage < $result['pages']): $qs['page'] = $curPage + 1; ?>
                <a href="?<?= e(http_build_query($qs)) ?>" class="page-nav" aria-label="Next page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            <?php else: ?>
                <span class="page-nav disabled" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php else: ?>
    <p class="catalogue-empty">No gemstones match your filters. Try clearing some filters.</p>
<?php endif; ?>
