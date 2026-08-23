<?php
require_once __DIR__ . '/functions.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(setting('site_name', 'Ruwanpura Gems')) ?> — <?= e(c('hero_title')) ?></title>
    <meta name="description" content="<?= e(c('hero_desc')) ?>">
    <?php if (setting('noindex_site') === '1'): ?>
        <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <?php
        // Dynamic theme colours from settings
        $primary = setting('theme_primary', '#c99a5b');
        $darkc   = setting('theme_dark', '#0d0d0d');
    ?>
    <style>
        :root {
            --gold: <?= e($primary) ?>;
            --dark: <?= e($darkc) ?>;
        }
    </style>
</head>
<body>

<!-- ================= HEADER ================= -->
<header class="site-header">
    <div class="container">
        <nav class="nav">
            <div class="nav-left nav-menu">
                <a href="<?= BASE_URL ?>index.php" class="nav-link<?= $currentPage === 'index.php' ? ' active' : '' ?>"><?= e(c('nav_home')) ?></a>
                <a href="<?= BASE_URL ?>gemstones.php" class="nav-link<?= in_array($currentPage, ['gemstones.php', 'gemstone.php'], true) ? ' active' : '' ?>"><?= e(c('nav_gemstones')) ?></a>
                <a href="<?= BASE_URL ?>about.php" class="nav-link<?= $currentPage === 'about.php' ? ' active' : '' ?>"><?= e(c('nav_about')) ?></a>
            </div>

            <a class="nav-logo" href="<?= BASE_URL ?>index.php">
                <img src="<?= logo_url() ?>" alt="<?= e(setting('site_name')) ?>"
                     onerror="this.style.display='none'">
            </a>

            <div class="nav-right">
                <a href="<?= BASE_URL ?>index.php#events" class="nav-link"><?= e(c('nav_news')) ?></a>
                <a href="<?= BASE_URL ?>contact.php" class="nav-link<?= $currentPage === 'contact.php' ? ' active' : '' ?>"><?= e(c('nav_contact')) ?></a>
                <div class="nav-icons">
                    <a href="tel:<?= e(c('footer_phone1')) ?>" aria-label="Call">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.6 2.6.7a2 2 0 0 1 1.7 2z"/><circle cx="19" cy="5" r="3.5" fill="currentColor" stroke="none"/><path d="M19 3.5v3M17.5 5h3" stroke="#fff" stroke-width="1"/></svg>
                    </a>
                    <a href="mailto:<?= e(c('footer_email')) ?>" aria-label="Email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
                    </a>
                    <a href="<?= BASE_URL ?>contact.php" aria-label="Location">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </a>
                    <a href="<?= BASE_URL ?>cart.php" aria-label="Enquiry Cart" class="cart-icon-link">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                        <span id="cartCount" class="cart-badge" style="display:none;">0</span>
                    </a>
                </div>
                <button class="menu-toggle" aria-label="Menu">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
            </div>
        </nav>
    </div>
</header>
