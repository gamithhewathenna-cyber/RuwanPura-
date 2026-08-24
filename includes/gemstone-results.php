<?php
/**
 * Catalogue results fragment — the product count, grid, and pagination.
 * Expects $result (from get_products()) and $statusLabels in scope.
 * Included by gemstones.php (full page) and filter-products.php (AJAX partial).
 */
?>
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
