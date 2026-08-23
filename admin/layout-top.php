<?php
require_once __DIR__ . '/auth.php';
require_admin();

$flash = get_flash();
$current = basename($_SERVER['PHP_SELF']);

function nav_active($file) {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}
// $page_title should be set before including
$page_title = $page_title ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body>
<div class="admin">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= logo_url() ?>" alt="Logo" onerror="this.style.display='none'">
            <span><?= e(setting('site_name', 'Ruwanpura Gems')) ?></span>
        </div>
        <nav>
            <a href="<?= BASE_URL ?>admin/index.php" class="side-link <?= nav_active('index.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <div class="nav-group-label">Home Page Sections</div>
            <a href="<?= BASE_URL ?>admin/section-header.php" class="side-link <?= nav_active('section-header.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="4" rx="1"/><path d="M3 12h18M3 16h12"/></svg>
                Header &amp; Navigation
            </a>
            <a href="<?= BASE_URL ?>admin/section-hero.php" class="side-link <?= nav_active('section-hero.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 14l4-4 5 5 3-3 6 6"/><circle cx="8" cy="9" r="1.5"/></svg>
                Hero Slider
            </a>
            <a href="<?= BASE_URL ?>admin/section-journey.php" class="side-link <?= nav_active('section-journey.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2 2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                Journey Section
            </a>
            <a href="<?= BASE_URL ?>admin/section-collection.php" class="side-link <?= nav_active('section-collection.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M12 3 8 9l4 12 4-12-4-6"/></svg>
                Gemstones Collection
            </a>
            <a href="<?= BASE_URL ?>admin/section-factory.php" class="side-link <?= nav_active('section-factory.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 20h20V9l-6 4V9l-6 4V4H4a2 2 0 0 0-2 2z"/></svg>
                Factory &amp; Labs
            </a>
            <a href="<?= BASE_URL ?>admin/section-branches.php" class="side-link <?= nav_active('section-branches.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/></svg>
                Our Branches
            </a>
            <a href="<?= BASE_URL ?>admin/section-events.php" class="side-link <?= nav_active('section-events.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16v14H4z"/><path d="M8 3v4M16 3v4M4 10h16"/></svg>
                Exhibitions &amp; Logos
            </a>
            <a href="<?= BASE_URL ?>admin/section-testimonials.php" class="side-link <?= nav_active('section-testimonials.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Testimonials
            </a>
            <a href="<?= BASE_URL ?>admin/section-cta.php" class="side-link <?= nav_active('section-cta.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M8 12h8"/></svg>
                Call To Action
            </a>
            <a href="<?= BASE_URL ?>admin/section-footer.php" class="side-link <?= nav_active('section-footer.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="16" width="18" height="4" rx="1"/><path d="M3 8h18M3 12h12"/></svg>
                Footer
            </a>

            <div class="nav-group-label">System</div>
            <a href="<?= BASE_URL ?>admin/settings.php" class="side-link <?= nav_active('settings.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/></svg>
                Website Settings
            </a>
            <a href="<?= BASE_URL ?>admin/account.php" class="side-link <?= nav_active('account.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg>
                My Account
            </a>
        </nav>
        <div class="side-foot">
            <a href="<?= BASE_URL ?>admin/logout.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <div class="overlay" id="overlay"></div>

    <!-- MAIN -->
    <div class="main">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <button class="mobile-toggle" id="mobileToggle" aria-label="Menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
                <h1><?= e($page_title) ?></h1>
            </div>
            <div class="top-actions">
                <a href="<?= BASE_URL ?>index.php" target="_blank" class="view-site">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/></svg>
                    View Site
                </a>
                <span class="who">Hi, <?= e($_SESSION['admin_name'] ?? 'Admin') ?></span>
            </div>
        </div>

        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= e($flash['type']) ?>"><?= e($flash['msg']) ?></div>
            <?php endif; ?>
