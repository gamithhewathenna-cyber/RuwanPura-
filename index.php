<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

include __DIR__ . '/includes/header.php';

$heroSlides   = get_hero_slides();
$legacyStats  = get_legacy_stats();
$gemstones    = get_latest_products(10);
$branches     = get_branches();
$partners     = get_partners();
$testimonials = get_testimonials();
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <button class="hero-arrow hero-arrow-outer prev" aria-label="Previous">‹</button>
    <button class="hero-arrow hero-arrow-outer next" aria-label="Next">›</button>
    <div class="container">
        <div class="hero-grid">
            <div class="hero-text">
                <?php if ($heroSlides): foreach ($heroSlides as $i => $s): ?>
                    <div class="hero-text-slide<?= $i === 0 ? ' active' : '' ?>">
                        <div class="hero-eyebrow"><?= e(slide_text($s, 'eyebrow', c('hero_eyebrow'))) ?></div>
                        <h1 class="hero-title"><?= e(slide_text($s, 'title', c('hero_title'))) ?></h1>
                        <p class="hero-desc"><?= e(slide_text($s, 'description', c('hero_desc'))) ?></p>
                        <a href="<?= e(slide_text($s, 'btn_link', c('hero_btn_link', '#'))) ?>" class="btn-dark"><?= e(slide_text($s, 'btn_text', c('hero_btn_text'))) ?></a>
                    </div>
                <?php endforeach; else: ?>
                    <div class="hero-text-slide active">
                        <div class="hero-eyebrow"><?= e(c('hero_eyebrow')) ?></div>
                        <h1 class="hero-title"><?= e(c('hero_title')) ?></h1>
                        <p class="hero-desc"><?= e(c('hero_desc')) ?></p>
                        <a href="<?= e(c('hero_btn_link', '#')) ?>" class="btn-dark"><?= e(c('hero_btn_text')) ?></a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="hero-slider">
                <button class="hero-arrow hero-arrow-inner prev" aria-label="Previous">‹</button>
                <div class="hero-slides">
                    <?php if ($heroSlides): foreach ($heroSlides as $i => $s): ?>
                        <div class="hero-slide<?= $i === 0 ? ' active' : '' ?>">
                            <?php if ($s['image']): ?>
                                <img src="<?= row_img($s['image']) ?>" alt="Gemstone slide <?= $i + 1 ?>">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>assets/images/hero-placeholder.jpg" alt="Gemstone">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="hero-slide active">
                            <img src="<?= BASE_URL ?>assets/images/hero-placeholder.jpg" alt="Gemstone">
                        </div>
                    <?php endif; ?>
                </div>
                <button class="hero-arrow hero-arrow-inner next" aria-label="Next">›</button>
            </div>
        </div>
    </div>
</section>

<?php if ($legacyStats): ?>
<!-- ================= LEGACY IN NUMBERS ================= -->
<section class="legacy-stats">
    <div class="container">
        <div class="legacy-grid">
            <?php foreach ($legacyStats as $ls): ?>
                <div class="legacy-item reveal">
                    <?php if ($ls['icon']): ?>
                        <img class="legacy-icon" src="<?= UPLOAD_URL . e($ls['icon']) ?>" alt="">
                    <?php endif; ?>
                    <div class="legacy-value"><?= e($ls['stat_value']) ?></div>
                    <div class="legacy-label"><?= e($ls['stat_label']) ?></div>
                    <p class="legacy-desc"><?= e($ls['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ================= JOURNEY ================= -->
<section class="journey" id="journey">
    <div class="container">
        <div class="journey-grid">
            <div class="journey-img reveal-fade">
                <div class="journey-img-frame">
                    <?php if (c('journey_image')): ?>
                        <img src="<?= c_img('journey_image') ?>" alt="Sapphire">
                    <?php else: ?>
                        <img src="<?= BASE_URL ?>assets/images/journey-placeholder.png" alt="Sapphire">
                    <?php endif; ?>
                </div>
            </div>
            <div class="journey-content reveal-right">
                <div class="eyebrow"><?= e(c('journey_eyebrow')) ?></div>
                <h2 class="journey-title"><?= e(c('journey_title')) ?></h2>
                <p><?= e(c('journey_p1')) ?></p>
                <p><?= e(c('journey_p2')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ================= COLLECTION ================= -->
<section class="collection" id="collection">
    <div class="container">
        <div class="collection-head reveal-left">
            <div class="eyebrow"><?= e(c('collection_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('collection_title')) ?></h2>
        </div>
        <?php if ($gemstones): ?>
            <div class="collection-slider">
                <div class="collection-track">
                    <?php // Rendered twice back-to-back so the CSS animation can loop seamlessly ?>
                    <?php foreach (array_merge($gemstones, $gemstones) as $g): ?>
                        <a href="<?= BASE_URL ?>gemstone.php?slug=<?= urlencode($g['slug']) ?>" class="gem-card">
                            <div class="gem-thumb">
                                <?php if ($g['thumb']): ?>
                                    <img src="<?= UPLOAD_URL . e($g['thumb']) ?>" alt="<?= e($g['name']) ?>">
                                <?php else: ?>
                                    <img src="<?= BASE_URL ?>assets/images/gem-placeholder.png" alt="<?= e($g['name']) ?>">
                                <?php endif; ?>
                            </div>
                            <div class="gem-name"><?= e($g['name']) ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p style="color:var(--muted);text-align:center;">Gemstones will appear here once added to the catalogue.</p>
        <?php endif; ?>

        <div class="collection-btn-wrap">
            <a href="<?= BASE_URL ?>gemstones.php" class="btn-dark"><?= e(c('collection_btn_text', 'View Gems Collection')) ?></a>
        </div>
    </div>
</section>

<!-- ================= FACTORY ================= -->
<section class="factory">
    <div class="container">
        <div class="factory-grid">
            <div class="factory-images reveal-fade">
                <img class="fac-single" src="<?= c('factory_image1') ? c_img('factory_image1') : BASE_URL.'assets/images/factory1.jpg' ?>" alt="Factory & Laboratories">
            </div>
            <div class="factory-content reveal-right">
                <div class="eyebrow left"><?= e(c('factory_eyebrow')) ?></div>
                <h2 class="factory-title"><?= e(c('factory_title')) ?></h2>
                <p><?= e(c('factory_p1')) ?></p>
                <p><?= e(c('factory_p2')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ================= BRANCHES ================= -->
<section class="branches">
    <div class="container">
        <div class="branches-grid">
            <div class="branches-left reveal-left">
                <div class="eyebrow left"><?= e(c('branches_eyebrow')) ?></div>
                <h2 class="branches-title"><?= e(c('branches_title')) ?></h2>
                <p class="branches-desc"><?= e(c('branches_desc')) ?></p>
                <?php foreach ($branches as $b): ?>
                    <div class="branch-item">
                        <span class="dot">●</span>
                        <div>
                            <h4><?= e($b['title']) ?></h4>
                            <p><?= e($b['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="branches-map reveal-fade">
                <?php if (c('branches_map')): ?>
                    <img src="<?= c_img('branches_map') ?>" alt="Global branches map">
                <?php else: ?>
                    <div class="map-placeholder">Ruwanpura Gems — Global Presence</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= WHY CHOOSE US ================= -->
<section class="why-choose">
    <div class="container">
        <div class="why-grid">
            <div class="why-content reveal-left">
                <div class="eyebrow left"><?= e(c('why_eyebrow')) ?></div>
                <h2 class="why-title"><?= e(c('why_title')) ?></h2>
                <p class="why-desc"><?= e(c('why_desc')) ?></p>
                <div class="why-features">
                    <div class="why-feature">
                        <h4><?= e(c('why_item1_title')) ?></h4>
                        <p><?= e(c('why_item1_desc')) ?></p>
                    </div>
                    <div class="why-feature">
                        <h4><?= e(c('why_item2_title')) ?></h4>
                        <p><?= e(c('why_item2_desc')) ?></p>
                    </div>
                    <div class="why-feature">
                        <h4><?= e(c('why_item3_title')) ?></h4>
                        <p><?= e(c('why_item3_desc')) ?></p>
                    </div>
                    <div class="why-feature">
                        <h4><?= e(c('why_item4_title')) ?></h4>
                        <p><?= e(c('why_item4_desc')) ?></p>
                    </div>
                </div>
            </div>
            <div class="why-image reveal-fade">
                <?php if (c('why_image')): ?>
                    <img class="parallax" data-speed="0.12" src="<?= c_img('why_image') ?>" alt="<?= e(c('why_title')) ?>">
                <?php else: ?>
                    <div class="map-placeholder">Why Choose Ruwanpura Gems</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= EVENTS ================= -->
<section class="events" id="events">
    <div class="container">
        <div class="events-head reveal-right">
            <div class="eyebrow"><?= e(c('events_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('events_title')) ?></h2>
            <p class="events-desc"><?= e(c('events_desc')) ?></p>
        </div>
        <div class="events-logos">
            <?php foreach ($partners as $p): ?>
                <div class="logo-item">
                    <?php if ($p['image']): ?>
                        <img src="<?= row_img($p['image']) ?>" alt="<?= e($p['name']) ?>">
                    <?php else: ?>
                        <span class="logo-fallback"><?= e($p['name']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= TESTIMONIALS ================= -->
<section class="testimonials">
    <div class="container">
        <div class="testi-head reveal-left">
            <div class="testi-quote-mark">“</div>
            <div class="eyebrow"><?= e(c('testi_eyebrow')) ?></div>
            <h2 class="testi-title"><?= e(c('testi_title')) ?></h2>
            <p class="testi-desc"><?= e(c('testi_desc')) ?></p>
        </div>

        <div class="testi-track">
            <?php foreach ($testimonials as $t): ?>
                <div class="testi-card">
                    <p class="quote">“<?= e($t['quote']) ?>”</p>
                    <div class="testi-author">
                        <span class="avatar"><i class="fa-solid fa-user"></i></span>
                        <div>
                            <div class="name"><?= e($t['author_name']) ?></div>
                            <div class="role"><?= e($t['author_role']) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="testi-progress"><div class="bar"></div></div>
        <div class="testi-nav">
            <button class="prev" aria-label="Previous">←</button>
            <button class="next" aria-label="Next">→</button>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/cta-banner.php'; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
