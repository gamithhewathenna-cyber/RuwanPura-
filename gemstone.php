<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$slug    = $_GET['slug'] ?? '';
$product = $slug !== '' ? get_product_by_slug($slug) : null;

if (!$product) {
    http_response_code(404);
    include __DIR__ . '/includes/header.php';
    ?>
    <section class="container" style="padding:120px 0;text-align:center;">
        <h1 class="section-title">Gemstone Not Found</h1>
        <p style="color:var(--muted);margin-top:14px;">This gemstone may have been sold or removed from the catalogue.</p>
        <a href="<?= BASE_URL ?>gemstones.php" class="btn-dark" style="margin-top:24px;display:inline-block;">Back to Catalogue</a>
    </section>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$images          = get_product_images($product['id']);
$hasVideo        = !empty($product['video']);
$statusLabels    = product_status_labels();
$categoryName    = lookup_name('gem_categories', $product['category_id']);
$shapeName       = lookup_name('gem_shapes', $product['shape_id']);
$treatmentName   = lookup_name('gem_treatments', $product['treatment_id']);
$originName      = lookup_name('gem_origins', $product['origin_id']);
$relatedProducts = get_related_products($product, 4);

include __DIR__ . '/includes/header.php';
?>

<!-- ================= PRODUCT HEADER ================= -->
<section class="about-hero">
    <div class="container reveal-left">
        <h1 class="about-hero-title"><?= e($categoryName ?: 'Gemstone') ?></h1>
        <p class="product-header-subtitle"><?= e($product['name']) ?></p>
    </div>
</section>

<!-- ================= PRODUCT DETAIL ================= -->
<section class="product-detail">
    <div class="container">
        <a href="<?= BASE_URL ?>gemstones.php" class="back-link">&larr; Back to Catalogue</a>

        <div class="product-detail-grid">
            <div class="product-gallery reveal-fade">
                <div class="product-gallery-main">
                    <?php if ($images): ?>
                        <img id="mainProductImage" src="<?= UPLOAD_URL . e($images[0]['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php endif; ?>
                    <?php if ($hasVideo): ?>
                        <video id="mainProductVideo" src="<?= UPLOAD_URL . e($product['video']) ?>" controls playsinline <?= $images ? 'style="display:none;"' : '' ?>></video>
                    <?php endif; ?>
                    <?php if (!$images && !$hasVideo): ?>
                        <div class="product-card-noimg" style="height:100%;">No Image</div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1 || $hasVideo): ?>
                    <div class="product-gallery-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <img src="<?= UPLOAD_URL . e($img['image']) ?>" class="<?= $i === 0 ? 'active' : '' ?>"
                                 onclick="showProductImage(this, '<?= UPLOAD_URL . e($img['image']) ?>')">
                        <?php endforeach; ?>
                        <?php if ($hasVideo): ?>
                            <div class="product-gallery-thumb-video <?= !$images ? 'active' : '' ?>" onclick="showProductVideo(this)">
                                <video src="<?= UPLOAD_URL . e($product['video']) ?>" muted playsinline preload="metadata"></video>
                                <span class="thumb-play-icon">&#9658;</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if (count($images) > 1 || $hasVideo): ?>
            <script>
                function showProductImage(el, src) {
                    var img = document.getElementById('mainProductImage');
                    if (img) { img.src = src; img.style.display = ''; }
                    var vid = document.getElementById('mainProductVideo');
                    if (vid) { vid.pause(); vid.style.display = 'none'; }
                    document.querySelectorAll('.product-gallery-thumbs img, .product-gallery-thumbs .product-gallery-thumb-video').forEach(function (t) { t.classList.remove('active'); });
                    el.classList.add('active');
                }
                function showProductVideo(el) {
                    var img = document.getElementById('mainProductImage');
                    if (img) img.style.display = 'none';
                    var vid = document.getElementById('mainProductVideo');
                    if (vid) vid.style.display = '';
                    document.querySelectorAll('.product-gallery-thumbs img, .product-gallery-thumbs .product-gallery-thumb-video').forEach(function (t) { t.classList.remove('active'); });
                    el.classList.add('active');
                }
            </script>
            <?php endif; ?>

            <div class="product-info reveal-right">
                <span class="product-status-badge status-<?= e($product['status']) ?>"><?= e($statusLabels[$product['status']]) ?></span>
                <h1 class="product-title"><?= e($product['name']) ?></h1>
                <?php if ($product['sku']): ?><p class="product-sku">SKU: <?= e($product['sku']) ?></p><?php endif; ?>

                <div class="product-specs">
                    <?php if ($product['weight'] !== null): ?><div><span>Weight</span><strong><?= e($product['weight']) ?> ct</strong></div><?php endif; ?>
                    <?php if ($shapeName): ?><div><span>Shape</span><strong><?= e($shapeName) ?></strong></div><?php endif; ?>
                    <?php if ($treatmentName): ?><div><span>Treatment</span><strong><?= e($treatmentName) ?></strong></div><?php endif; ?>
                    <?php if ($originName): ?><div><span>Origin</span><strong><?= e($originName) ?></strong></div><?php endif; ?>
                    <?php if ($categoryName): ?><div><span>Category</span><strong><?= e($categoryName) ?></strong></div><?php endif; ?>
                </div>

                <?php if ($product['description']): ?>
                    <p class="product-desc"><?= nl2br(e($product['description'])) ?></p>
                <?php endif; ?>

                <?php if ($product['certificate_info']): ?>
                    <div class="product-cert">
                        <h4>Certificate Information</h4>
                        <p><?= nl2br(e($product['certificate_info'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php $pricing = product_pricing($product); if ($pricing['original'] !== null): ?>
                    <p style="margin-bottom:6px;">
                        <?php if ($pricing['has_discount']): ?>
                            <span class="price-was" style="font-size:16px;"><?= format_money($pricing['original']) ?></span>
                            <span class="price-now" style="font-size:24px;"><?= format_money($pricing['final']) ?></span>
                        <?php else: ?>
                            <span class="price-now" style="font-size:24px;"><?= format_money($pricing['final']) ?></span>
                        <?php endif; ?>
                    </p>
                    <div class="shipping-note">The displayed price is for the gemstone only. Shipping charges will be calculated separately and confirmed before payment.</div>
                <?php endif; ?>

                <?php
                    $stock   = (int) ($product['quantity'] ?? 1);
                    $inStock = $product['status'] === 'available' && $stock > 0;
                ?>
                <?php if ($inStock && $stock > 1): ?>
                    <p class="hint" style="margin-bottom:8px;"><?= (int) $stock ?> in stock</p>
                    <div class="qty-field">
                        <label for="addQty">Quantity</label>
                        <input type="number" id="addQty" min="1" max="<?= (int) $stock ?>" value="1">
                    </div>
                <?php endif; ?>

                <button type="button" class="btn-dark add-to-cart-btn"
                    data-id="<?= (int) $product['id'] ?>"
                    data-name="<?= e($product['name']) ?>"
                    data-weight="<?= e($product['weight']) ?>"
                    data-shape="<?= e($shapeName) ?>"
                    data-image="<?= $images ? UPLOAD_URL . e($images[0]['image']) : '' ?>"
                    data-max-qty="<?= (int) $stock ?>"
                    data-qty-input="addQty"
                    <?= !$inStock ? 'disabled' : '' ?>>
                    <?= $inStock ? 'Add to Cart' : ($stock <= 0 ? 'Out of Stock' : e($statusLabels[$product['status']])) ?>
                </button>
            </div>
        </div>
    </div>
</section>

<?php if ($relatedProducts): ?>
<!-- ================= YOU MAY ALSO LIKE ================= -->
<section class="related-products">
    <div class="container">
        <h2 class="related-title">You May Also Like</h2>
        <div class="product-grid reveal">
            <?php foreach ($relatedProducts as $rp): ?>
                <a href="<?= BASE_URL ?>gemstone.php?slug=<?= urlencode($rp['slug']) ?>" class="product-card">
                    <div class="product-card-img">
                        <?php if ($rp['thumb']): ?>
                            <img src="<?= UPLOAD_URL . e($rp['thumb']) ?>" alt="<?= e($rp['name']) ?>">
                        <?php else: ?>
                            <div class="product-card-noimg">No Image</div>
                        <?php endif; ?>
                        <span class="product-status-badge status-<?= e($rp['status']) ?>"><?= e($statusLabels[$rp['status']]) ?></span>
                    </div>
                    <div class="product-card-body">
                        <h3><?= e($rp['name']) ?></h3>
                        <p class="product-card-meta">
                            <?php
                                $relMeta = [];
                                if ($rp['weight'] !== null) $relMeta[] = $rp['weight'] . ' ct';
                                $relShape = lookup_name('gem_shapes', $rp['shape_id']);
                                if ($relShape) $relMeta[] = $relShape;
                                echo e(implode(' · ', $relMeta));
                            ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
