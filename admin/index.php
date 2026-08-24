<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/layout-top.php';

$gemCount    = db()->query("SELECT COUNT(*) FROM gemstones")->fetchColumn();
$branchCount = db()->query("SELECT COUNT(*) FROM branches")->fetchColumn();
$testiCount  = db()->query("SELECT COUNT(*) FROM testimonials")->fetchColumn();
$slideCount  = db()->query("SELECT COUNT(*) FROM hero_slides")->fetchColumn();

try {
    $productCount = (int) db()->query("SELECT COUNT(*) FROM products")->fetchColumn();
} catch (PDOException $e) {
    $productCount = 0; // catalogue tables not migrated yet
}
?>

<div class="stat-grid">
    <?php if (role_can('home')): ?>
    <div class="stat">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12l4 6-10 12L2 9z"/></svg></div>
        <div><div class="num"><?= (int)$gemCount ?></div><div class="lbl">Homepage Gemstones</div></div>
    </div>
    <div class="stat">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg></div>
        <div><div class="num"><?= (int)$branchCount ?></div><div class="lbl">Branches</div></div>
    </div>
    <div class="stat">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
        <div><div class="num"><?= (int)$testiCount ?></div><div class="lbl">Testimonials</div></div>
    </div>
    <div class="stat">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 14l4-4 5 5"/></svg></div>
        <div><div class="num"><?= (int)$slideCount ?></div><div class="lbl">Hero Slides</div></div>
    </div>
    <?php endif; ?>
    <?php if (role_can('gemstones')): ?>
    <div class="stat">
        <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12l4 6-10 12L2 9z"/></svg></div>
        <div><div class="num"><?= (int)$productCount ?></div><div class="lbl">Catalogue Gemstones</div></div>
    </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Welcome back, <?= e($_SESSION['admin_name']) ?> 👋</h2>
    <p class="card-sub">Manage every part of your website from the sections below. All changes appear on your live website instantly.</p>

    <div class="quick-links">
        <?php if (role_can('home')): ?>
        <a href="<?= BASE_URL ?>admin/section-hero.php" class="quick-link">
            <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 14l4-4 5 5"/></svg></div>
            <div><h3>Hero Slider</h3><p>Edit the main headline, description, button and slider images.</p></div>
        </a>
        <a href="<?= BASE_URL ?>admin/section-testimonials.php" class="quick-link">
            <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <div><h3>Testimonials</h3><p>Manage client reviews shown in the testimonials slider.</p></div>
        </a>
        <?php endif; ?>
        <?php if (role_can('gemstones')): ?>
        <a href="<?= BASE_URL ?>admin/gemstones-list.php" class="quick-link">
            <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12l4 6-10 12L2 9z"/></svg></div>
            <div><h3>Gemstone Products</h3><p>Add, edit, or remove gemstones in the online catalogue.</p></div>
        </a>
        <?php endif; ?>
        <?php if (role_can('about')): ?>
        <a href="<?= BASE_URL ?>admin/section-about-hero.php" class="quick-link">
            <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg></div>
            <div><h3>About Us</h3><p>Edit the company story, timeline, and recognitions.</p></div>
        </a>
        <?php endif; ?>
        <?php if (role_can('settings')): ?>
        <a href="<?= BASE_URL ?>admin/settings.php" class="quick-link">
            <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M2 12h4M18 12h4"/></svg></div>
            <div><h3>Website Settings</h3><p>Change your logo, colour theme, email and password.</p></div>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
