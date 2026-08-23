<?php
require_once __DIR__ . '/includes/functions.php';

if (setting('maintenance_mode') === '1' && empty($_SESSION['admin_id'])) {
    http_response_code(503);
    header('Retry-After: 3600');
    $siteName = setting('site_name', 'Ruwanpura Gems');
    $primary  = setting('theme_primary', '#c99a5b');
    $dark     = setting('theme_dark', '#0d0d0d');
    $message  = setting('maintenance_message', "We're currently performing scheduled maintenance. Please check back soon.");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Under Maintenance — <?= e($siteName) ?></title>
        <style>
            body {
                margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
                background: <?= e($dark) ?>; color: #f5f1e8; font-family: Georgia, 'Times New Roman', serif;
                text-align: center; padding: 24px; box-sizing: border-box;
            }
            .maint-box { max-width: 560px; }
            .maint-logo { max-height: 64px; margin-bottom: 28px; }
            h1 { font-size: 28px; letter-spacing: 1px; margin: 0 0 16px; color: <?= e($primary) ?>; }
            p { font-size: 16px; line-height: 1.7; color: #d8d2c4; }
        </style>
    </head>
    <body>
        <div class="maint-box">
            <img class="maint-logo" src="<?= logo_url() ?>" alt="<?= e($siteName) ?>">
            <h1>We'll be right back</h1>
            <p><?= e_nl($message) ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

include __DIR__ . '/includes/header.php';

$heroSlides   = get_hero_slides();
$gemstones    = get_gemstones();
$branches     = get_branches();
$partners     = get_partners();
$testimonials = get_testimonials();
?>

<!-- ================= HERO ================= -->
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div class="hero-text">
                <div class="hero-eyebrow"><?= e(c('hero_eyebrow')) ?></div>
                <h1 class="hero-title"><?= e(c('hero_title')) ?></h1>
                <p class="hero-desc"><?= e(c('hero_desc')) ?></p>
                <a href="<?= e(c('hero_btn_link', '#')) ?>" class="btn-dark"><?= e(c('hero_btn_text')) ?></a>
            </div>

            <div class="hero-slider">
                <button class="hero-arrow prev" aria-label="Previous">‹</button>
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
                <button class="hero-arrow next" aria-label="Next">›</button>
            </div>
        </div>
    </div>
</section>

<!-- ================= JOURNEY ================= -->
<section class="journey" id="journey">
    <div class="container">
        <div class="journey-grid">
            <div class="journey-img">
                <?php if (c('journey_image')): ?>
                    <img src="<?= c_img('journey_image') ?>" alt="Sapphire">
                <?php else: ?>
                    <img src="<?= BASE_URL ?>assets/images/journey-placeholder.png" alt="Sapphire">
                <?php endif; ?>
            </div>
            <div class="journey-content">
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
        <div class="collection-head">
            <div class="eyebrow"><?= e(c('collection_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('collection_title')) ?></h2>
        </div>
        <div class="collection-slider">
            <button class="collection-arrow prev" aria-label="Previous">‹</button>
            <div class="collection-track">
                <?php foreach ($gemstones as $g): ?>
                    <div class="gem-card">
                        <div class="gem-thumb">
                            <?php
                                // Fallback: map default gemstone names to bundled placeholder images
                                $gemFallbacks = [
                                    'Alexandrite'                => 'gem-alexandrite.png',
                                    'Padparadscha (Pasparaja)'   => 'gem-padparadscha.png',
                                    'Blue Sapphire'              => 'gem-bluesapphire.png',
                                    'Yellow & Orange Sapphires'  => 'gem-yelloworange.png',
                                ];
                                $gemFallback = $gemFallbacks[$g['name']] ?? 'gem-placeholder.png';
                            ?>
                            <?php if ($g['image']): ?>
                                <img src="<?= row_img($g['image']) ?>" alt="<?= e($g['name']) ?>">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>assets/images/<?= $gemFallback ?>" alt="<?= e($g['name']) ?>">
                            <?php endif; ?>
                        </div>
                        <div class="gem-name"><?= e($g['name']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="collection-arrow next" aria-label="Next">›</button>
        </div>
    </div>
</section>

<!-- ================= FACTORY ================= -->
<section class="factory">
    <div class="container">
        <div class="factory-grid">
            <div class="factory-images">
                <div class="fac-col">
                    <img class="fac-a" src="<?= c('factory_image1') ? c_img('factory_image1') : BASE_URL.'assets/images/factory1.jpg' ?>" alt="Laboratory">
                    <img class="fac-c" src="<?= c('factory_image3') ? c_img('factory_image3') : BASE_URL.'assets/images/factory3.jpg' ?>" alt="Craftsman">
                </div>
                <div class="fac-col">
                    <img class="fac-b" src="<?= c('factory_image2') ? c_img('factory_image2') : BASE_URL.'assets/images/factory2.jpg' ?>" alt="Gem inspection">
                </div>
            </div>
            <div class="factory-content">
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
            <div class="branches-left">
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
            <div class="branches-map">
                <?php if (c('branches_map')): ?>
                    <img src="<?= c_img('branches_map') ?>" alt="Global branches map">
                <?php else: ?>
                    <div class="map-placeholder">Ruwanpura Gems — Global Presence</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= EVENTS ================= -->
<section class="events" id="events">
    <div class="container">
        <div class="events-head">
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
        <div class="testi-head">
            <div class="testi-quote-mark">“</div>
            <div class="eyebrow"><?= e(c('testi_eyebrow')) ?></div>
            <h2 class="testi-title"><?= e(c('testi_title')) ?></h2>
            <p class="testi-desc"><?= e(c('testi_desc')) ?></p>
        </div>

        <div class="testi-track">
            <?php foreach ($testimonials as $i => $t): ?>
                <div class="testi-card<?= $i === 0 ? ' dark' : '' ?>">
                    <p class="quote">“<?= e($t['quote']) ?>”</p>
                    <div class="testi-author">
                        <?php if ($t['avatar']): ?>
                            <img class="avatar" src="<?= row_img($t['avatar']) ?>" alt="<?= e($t['author_name']) ?>">
                        <?php else: ?>
                            <span class="avatar"></span>
                        <?php endif; ?>
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

<!-- ================= CTA ================= -->
<section class="cta">
    <div class="container">
        <div class="cta-box">
            <?php if (c('cta_image')): ?>
                <img class="cta-bg" src="<?= c_img('cta_image') ?>" alt="">
            <?php else: ?>
                <img class="cta-bg" src="<?= BASE_URL ?>assets/images/cta-placeholder.jpg" alt="">
            <?php endif; ?>
            <div class="cta-inner">
                <div class="cta-top">
                    <h2 class="cta-title"><?= e(c('cta_title')) ?></h2>
                    <p class="cta-desc"><?= e(c('cta_desc')) ?></p>
                </div>
                <div class="cta-help">
                    <div class="help-text"><?= e_nl(c('cta_box_text')) ?></div>
                    <a href="<?= e(c('cta_btn_link', '#')) ?>" class="btn-dark"><?= e(c('cta_btn_text')) ?></a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
