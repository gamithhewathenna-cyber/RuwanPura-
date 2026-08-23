<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

include __DIR__ . '/includes/header.php';

$milestones   = get_history_milestones();
$achievements = get_achievements();
$memberships  = get_memberships();
?>

<!-- ================= ABOUT HERO ================= -->
<section class="about-hero">
    <div class="container">
        <div class="eyebrow"><?= e(c('about_hero_eyebrow')) ?></div>
        <h1 class="about-hero-title"><?= e(c('about_hero_title')) ?></h1>
        <p class="about-hero-quote">&ldquo;<?= e(c('about_hero_quote')) ?>&rdquo;</p>
    </div>
</section>

<!-- ================= THE RADICAL EVOLUTION ================= -->
<section class="evolution">
    <div class="container">
        <div class="evolution-grid">
            <div class="evolution-content">
                <h2 class="evolution-title"><?= e(c('evolution_title')) ?></h2>
                <p><?= e(c('evolution_p1')) ?></p>
                <p><?= e(c('evolution_p2')) ?></p>
                <p><?= e(c('evolution_p3')) ?></p>
            </div>
            <div class="evolution-img">
                <?php if (c('evolution_image')): ?>
                    <img src="<?= c_img('evolution_image') ?>" alt="<?= e(c('evolution_title')) ?>">
                <?php else: ?>
                    <div class="img-placeholder">Ruwanpura Gold House</div>
                <?php endif; ?>
                <?php if (c('evolution_badge_image')): ?>
                    <img class="evolution-badge" src="<?= c_img('evolution_badge_image') ?>" alt="">
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= OUR GLOBAL JOURNEY ================= -->
<section class="history">
    <div class="container">
        <div class="history-head">
            <div class="eyebrow"><?= e(c('history_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('history_title')) ?></h2>
        </div>
        <div class="timeline">
            <?php foreach ($milestones as $m): ?>
                <div class="timeline-item">
                    <div class="timeline-year"><?= e($m['year_label']) ?></div>
                    <div class="timeline-dot"></div>
                    <div class="timeline-desc"><?= e($m['description']) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ================= DIRECT FROM THE SOURCE ================= -->
<section class="about-video">
    <div class="container">
        <div class="eyebrow"><?= e(c('video_eyebrow')) ?></div>

        <div class="video-frame">
            <?php if (c('video_thumbnail')): ?>
                <img src="<?= c_img('video_thumbnail') ?>" alt="">
            <?php endif; ?>
            <?php if (c('video_url')): ?>
                <a href="<?= e(c('video_url')) ?>" target="_blank" rel="noopener" class="play-btn" aria-label="Play video">&#9658;</a>
            <?php else: ?>
                <span class="play-btn" aria-hidden="true">&#9658;</span>
            <?php endif; ?>
        </div>

        <h2 class="video-heading"><?= e(c('video_heading')) ?></h2>
        <p><?= e(c('video_p1')) ?></p>
        <p><?= e(c('video_p2')) ?></p>

        <div class="video-cards">
            <div class="video-card">
                <h3><?= e(c('video_card1_title')) ?></h3>
                <p><?= e(c('video_card1_desc')) ?></p>
            </div>
            <div class="video-card">
                <h3><?= e(c('video_card2_title')) ?></h3>
                <p><?= e(c('video_card2_desc')) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ================= NATIONAL INDUSTRY EXCELLENCE ================= -->
<section class="awards">
    <div class="container">
        <div class="awards-head">
            <div class="eyebrow"><?= e(c('awards_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('awards_title')) ?></h2>
        </div>
        <div class="awards-grid">
            <div class="awards-img">
                <?php if (c('awards_image')): ?>
                    <img src="<?= c_img('awards_image') ?>" alt="Award trophy">
                <?php else: ?>
                    <div class="img-placeholder">Award</div>
                <?php endif; ?>
            </div>
            <div class="awards-list">
                <?php foreach ($achievements as $a): ?>
                    <div class="branch-item">
                        <span class="dot">●</span>
                        <div>
                            <h4><?= e($a['title']) ?></h4>
                            <p><?= e($a['description']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= GUBELIN GEM LAB ================= -->
<section class="gubelin">
    <div class="container">
        <div class="gubelin-grid">
            <div class="gubelin-content">
                <div class="eyebrow left"><?= e(c('gubelin_eyebrow')) ?></div>
                <h2 class="gubelin-title"><?= e(c('gubelin_title')) ?></h2>
                <p class="gubelin-subtitle"><?= e(c('gubelin_subtitle')) ?></p>
                <p><?= e(c('gubelin_desc')) ?></p>
                <div class="gubelin-signatures">
                    <div class="signature">
                        <div class="sig-name"><?= e(c('gubelin_sign1_name')) ?></div>
                        <div class="sig-title"><?= e(c('gubelin_sign1_title')) ?></div>
                    </div>
                    <div class="signature">
                        <div class="sig-name"><?= e(c('gubelin_sign2_name')) ?></div>
                        <div class="sig-title"><?= e(c('gubelin_sign2_title')) ?></div>
                    </div>
                </div>
            </div>
            <div class="gubelin-img">
                <?php if (c('gubelin_image')): ?>
                    <img src="<?= c_img('gubelin_image') ?>" alt="Gübelin certificate">
                <?php else: ?>
                    <div class="img-placeholder">Certificate</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ================= PROFESSIONAL STANDING / MEMBERSHIPS ================= -->
<section class="membership">
    <div class="container">
        <div class="membership-head">
            <div class="eyebrow"><?= e(c('membership_eyebrow')) ?></div>
            <h2 class="section-title"><?= e(c('membership_title')) ?></h2>
            <p><?= e(c('membership_p1')) ?></p>
        </div>
        <div class="membership-logos">
            <?php foreach ($memberships as $m): ?>
                <div class="membership-item">
                    <?php if ($m['logo']): ?>
                        <img src="<?= row_img($m['logo']) ?>" alt="<?= e($m['name']) ?>">
                    <?php else: ?>
                        <span class="logo-fallback"><?= e($m['name']) ?></span>
                    <?php endif; ?>
                    <p><?= e($m['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="membership-footer"><?= e(c('membership_p2')) ?></p>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
