<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$formSuccess = false;
$formErrors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    if (!verify_csrf()) {
        $formErrors[] = 'Security check failed — please try again.';
    } else {
        $name    = trim($_POST['full_name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($name === '' || $email === '' || $message === '') {
            $formErrors[] = 'Please fill in your name, email, and message.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = db()->prepare("INSERT INTO contact_messages (full_name, phone, email, company, message) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $phone, $email, $company, $message]);

                $adminEmail = setting('admin_email');
                if ($adminEmail) {
                    $siteName = setting('site_name', 'Ruwanpura Gems');
                    $subject  = 'New contact form message — ' . $siteName;
                    $body     = "Name: $name\nPhone: $phone\nEmail: $email\nCompany: $company\n\nMessage:\n$message";
                    @mail($adminEmail, $subject, $body, 'From: ' . $adminEmail);
                }

                $formSuccess = true;
                $_POST = [];
            } catch (PDOException $e) {
                $formErrors[] = 'Sorry, something went wrong sending your message. Please try again later.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- ================= CONTACT HERO ================= -->
<section class="about-hero">
    <div class="container reveal">
        <div class="eyebrow"><?= e(c('contact_hero_eyebrow')) ?></div>
        <h1 class="about-hero-title contact-hero-title"><?= e(c('contact_hero_title')) ?></h1>
        <p class="about-hero-quote">&ldquo;<?= e(c('contact_hero_quote')) ?>&rdquo;</p>
        <div class="hero-flags" aria-hidden="true">🇱🇰&nbsp;&nbsp;🇹🇭&nbsp;&nbsp;🇺🇸</div>
    </div>
</section>

<!-- ================= CONTACT INFO + FORM ================= -->
<section class="contact-main">
    <div class="container">
        <div class="contact-grid">
            <div class="contact-info reveal">
                <h2 class="contact-heading"><?= e(c('contact_heading')) ?></h2>
                <p><?= e(c('contact_p1')) ?></p>
                <p><?= e(c('contact_p2')) ?></p>

                <div class="contact-detail">
                    <div class="contact-detail-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.6 2.6.7a2 2 0 0 1 1.7 2z"/></svg>
                    </div>
                    <div>
                        <h4>Phone</h4>
                        <p><?= e(phone_flag(c('footer_phone1'))) ?> <?= e(c('footer_phone1')) ?></p>
                        <p><?= e(phone_flag(c('footer_phone2'))) ?> <?= e(c('footer_phone2')) ?></p>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="contact-detail-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
                    </div>
                    <div>
                        <h4>Email</h4>
                        <p><?= e(c('footer_email')) ?></p>
                    </div>
                </div>
                <div class="contact-detail">
                    <div class="contact-detail-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div>
                        <h4>Address</h4>
                        <p><?= e_nl(c('footer_address')) ?></p>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrap reveal">
                <p class="contact-form-intro"><?= e(c('contact_form_intro')) ?></p>

                <?php if ($formSuccess): ?>
                    <div class="flash success" style="margin-bottom:20px;">Thank you — your message has been sent. We'll be in touch soon.</div>
                <?php elseif ($formErrors): ?>
                    <div class="flash error" style="margin-bottom:20px;"><?= e(implode(' ', $formErrors)) ?></div>
                <?php endif; ?>

                <form method="post" class="contact-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="contact_submit" value="1">
                    <div class="form-row-2">
                        <div class="form-field">
                            <label>Full Name</label>
                            <input type="text" name="full_name" required value="<?= e($_POST['full_name'] ?? '') ?>">
                        </div>
                        <div class="form-field">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?= e($_POST['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Email Address</label>
                        <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="form-field">
                        <label>Company Name</label>
                        <input type="text" name="company" value="<?= e($_POST['company'] ?? '') ?>">
                    </div>
                    <div class="form-field">
                        <label>Message</label>
                        <textarea name="message" rows="5" required><?= e($_POST['message'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn-dark">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ================= MAP ================= -->
<section class="contact-map">
    <?php if (c('contact_map_embed')): ?>
        <iframe src="<?= e(c('contact_map_embed')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    <?php else: ?>
        <div class="contact-map-placeholder">Map</div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
