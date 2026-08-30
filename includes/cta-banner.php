<!-- ================= CTA (shared partial — same content everywhere, edited only from Home Page → Call To Action) ================= -->
<section class="cta">
    <div class="container">
        <div class="cta-box">
            <?php if (c('cta_image')): ?>
                <img class="cta-bg parallax" data-speed="0.12" src="<?= c_img('cta_image') ?>" alt="">
            <?php else: ?>
                <img class="cta-bg parallax" data-speed="0.12" src="<?= BASE_URL ?>assets/images/cta-placeholder.jpg" alt="">
            <?php endif; ?>
            <div class="cta-inner reveal-left">
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
