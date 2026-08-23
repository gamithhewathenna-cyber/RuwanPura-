<?php
require_once __DIR__ . '/auth.php';
require_admin();

$flash = get_flash();
$current = basename($_SERVER['PHP_SELF']);

function nav_active($file) {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}

$homePageSections = [
    'section-header.php'      => 'Header & Navigation',
    'section-hero.php'        => 'Hero Slider',
    'section-journey.php'     => 'Journey Section',
    'section-collection.php'  => 'Gemstones Collection',
    'section-factory.php'     => 'Factory & Laboratories',
    'section-branches.php'    => 'Our Branches',
    'section-events.php'      => 'Exhibitions & Logos',
    'section-testimonials.php'=> 'Testimonials',
    'section-cta.php'         => 'Call To Action',
    'section-footer.php'      => 'Footer',
];
$onHomePage = array_key_exists($current, $homePageSections);

$aboutPageSections = [
    'section-about-hero.php'      => 'Hero Band',
    'section-about-evolution.php' => 'The Radical Evolution',
    'section-history.php'         => 'Our Global Journey',
    'section-about-video.php'     => 'Direct From The Source',
    'section-awards.php'          => 'National Industry Excellence',
    'section-gubelin.php'         => 'Gübelin Gem Lab',
    'section-membership.php'      => 'Memberships',
];
$onAboutPage = array_key_exists($current, $aboutPageSections);

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

            <a href="<?= BASE_URL ?>admin/section-header.php" class="side-link <?= $onHomePage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                Home Page
            </a>

            <a href="<?= BASE_URL ?>admin/section-about-hero.php" class="side-link <?= $onAboutPage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/><path d="M17 3.5a3.5 3.5 0 0 1 0 7"/></svg>
                About Us
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

            <?php if ($onHomePage): ?>
                <div class="home-tabs">
                    <?php foreach ($homePageSections as $file => $label): ?>
                        <a href="<?= BASE_URL ?>admin/<?= $file ?>" class="home-tab <?= nav_active($file) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($onAboutPage): ?>
                <div class="home-tabs">
                    <?php foreach ($aboutPageSections as $file => $label): ?>
                        <a href="<?= BASE_URL ?>admin/<?= $file ?>" class="home-tab <?= nav_active($file) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
