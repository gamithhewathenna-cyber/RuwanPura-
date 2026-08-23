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

$images        = get_product_images($product['id']);
$statusLabels  = product_status_labels();
$categoryName  = lookup_name('gem_categories', $product['category_id']);
$shapeName     = lookup_name('gem_shapes', $product['shape_id']);
$treatmentName = lookup_name('gem_treatments', $product['treatment_id']);
$originName    = lookup_name('gem_origins', $product['origin_id']);

include __DIR__ . '/includes/header.php';
?>

<!-- ================= PRODUCT DETAIL ================= -->
<section class="product-detail">
    <div class="container">
        <a href="<?= BASE_URL ?>gemstones.php" class="back-link">&larr; Back to Catalogue</a>

        <div class="product-detail-grid">
            <div class="product-gallery">
                <div class="product-gallery-main">
                    <?php if ($images): ?>
                        <img id="mainProductImage" src="<?= UPLOAD_URL . e($images[0]['image']) ?>" alt="<?= e($product['name']) ?>">
                    <?php else: ?>
                        <div class="product-card-noimg" style="height:100%;">No Image</div>
                    <?php endif; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <div class="product-gallery-thumbs">
                        <?php foreach ($images as $i => $img): ?>
                            <img src="<?= UPLOAD_URL . e($img['image']) ?>" class="<?= $i === 0 ? 'active' : '' ?>"
                                 onclick="document.getElementById('mainProductImage').src=this.src;document.querySelectorAll('.product-gallery-thumbs img').forEach(function(t){t.classList.remove('active');});this.classList.add('active');">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="product-info">
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

                <button type="button" class="btn-dark add-to-cart-btn"
                    data-id="<?= (int) $product['id'] ?>"
                    data-name="<?= e($product['name']) ?>"
                    data-weight="<?= e($product['weight']) ?>"
                    data-shape="<?= e($shapeName) ?>"
                    data-image="<?= $images ? UPLOAD_URL . e($images[0]['image']) : '' ?>"
                    <?= $product['status'] !== 'available' ? 'disabled' : '' ?>>
                    <?= $product['status'] === 'available' ? 'Add to Enquiry Cart' : e($statusLabels[$product['status']]) ?>
                </button>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
