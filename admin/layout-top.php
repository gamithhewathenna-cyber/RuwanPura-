<?php
require_once __DIR__ . '/auth.php';
require_admin();

$flash = get_flash();
$current = basename($_SERVER['PHP_SELF']);

function nav_active($file) {
    return basename($_SERVER['PHP_SELF']) === $file ? 'active' : '';
}

$homePageSections = [
    'section-announcement.php' => 'Announcement Bar',
    'section-header.php'      => 'Header & Navigation',
    'section-hero.php'        => 'Hero Slider',
    'section-trust-badges.php'=> 'Trust Badges',
    'section-journey.php'     => 'Journey Section',
    'section-collection.php'  => 'Gemstones Collection',
    'section-factory.php'     => 'Factory & Laboratories',
    'section-branches.php'    => 'Our Branches',
    'section-why-choose.php'  => 'Why Choose Us',
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

$contactPageSections = [
    'section-contact-hero.php' => 'Hero Band',
    'section-contact.php'      => 'Page Content',
];
$onContactPage = array_key_exists($current, $contactPageSections);

$gemPageSections = [
    ['file' => 'gemstones-list.php',                     'label' => 'Products'],
    ['file' => 'gem-taxonomy.php', 'type' => 'category',  'label' => 'Categories'],
    ['file' => 'gem-taxonomy.php', 'type' => 'shape',     'label' => 'Shapes'],
    ['file' => 'gem-taxonomy.php', 'type' => 'treatment', 'label' => 'Treatments'],
    ['file' => 'gem-taxonomy.php', 'type' => 'origin',    'label' => 'Origins'],
    ['file' => 'enquiries.php',                           'label' => 'Enquiries'],
];
$onGemPage = in_array($current, ['gemstones-list.php', 'gemstones-edit.php', 'gem-taxonomy.php', 'enquiries.php'], true);

$blogPageSections = [
    ['file' => 'section-blog-hero.php', 'label' => 'Hero Band'],
    ['file' => 'blog-list.php',         'label' => 'Posts'],
    ['file' => 'blog-categories.php',   'label' => 'Categories'],
];
$onBlogPage = in_array($current, ['section-blog-hero.php', 'blog-list.php', 'blog-edit.php', 'blog-categories.php'], true);

function blog_tab_active($section) {
    $cur = basename($_SERVER['PHP_SELF']);
    if ($section['file'] === 'blog-list.php' && $cur === 'blog-edit.php') return true;
    return $cur === $section['file'];
}

function gem_tab_active($section) {
    $cur = basename($_SERVER['PHP_SELF']);
    if ($section['file'] === 'gemstones-list.php' && $cur === 'gemstones-edit.php') return true;
    if ($cur !== $section['file']) return false;
    if (isset($section['type'])) {
        return ($_GET['type'] ?? 'category') === $section['type'];
    }
    return true;
}

$unreadMessages  = count_unread_messages();
$unreadEnquiries = count_unread_enquiries();

// ---- Role-based access gate (central choke point for every admin page) ----
$adminOnlySections = ['settings.php' => 'settings', 'messages.php' => 'messages', 'users.php' => 'users'];
if ($onHomePage) {
    require_role('home');
} elseif ($onAboutPage) {
    require_role('about');
} elseif ($onContactPage) {
    require_role('contact');
} elseif ($onGemPage) {
    require_role('gemstones');
} elseif ($onBlogPage) {
    require_role('blog');
} elseif (isset($adminOnlySections[$current])) {
    require_role($adminOnlySections[$current]);
}
// index.php (dashboard) and account.php are unrestricted for any logged-in admin

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

            <?php if (role_can('home')): ?>
            <a href="<?= BASE_URL ?>admin/section-header.php" class="side-link <?= $onHomePage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                Home Page
            </a>
            <?php endif; ?>

            <?php if (role_can('gemstones')): ?>
            <a href="<?= BASE_URL ?>admin/gemstones-list.php" class="side-link <?= $onGemPage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12l4 6-10 12L2 9z"/><path d="M2 9h20M12 3 8 9l4 12 4-12-4-6"/></svg>
                Gemstones
                <?php if ($unreadEnquiries > 0): ?>
                    <span class="badge on" style="margin-left:auto;"><?= (int)$unreadEnquiries ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <?php if (role_can('about')): ?>
            <a href="<?= BASE_URL ?>admin/section-about-hero.php" class="side-link <?= $onAboutPage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/><path d="M17 3.5a3.5 3.5 0 0 1 0 7"/></svg>
                About Us
            </a>
            <?php endif; ?>

            <?php if (role_can('contact')): ?>
            <a href="<?= BASE_URL ?>admin/section-contact-hero.php" class="side-link <?= $onContactPage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.6A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.6a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.5-1.2a2 2 0 0 1 2.1-.5c.8.3 1.7.6 2.6.7a2 2 0 0 1 1.7 2z"/></svg>
                Contact Us
            </a>
            <?php endif; ?>

            <?php if (role_can('blog')): ?>
            <a href="<?= BASE_URL ?>admin/blog-list.php" class="side-link <?= $onBlogPage ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h8M9 11h8"/></svg>
                Insights
            </a>
            <?php endif; ?>

            <?php if (role_can('messages') || role_can('settings') || role_can('users')): ?>
            <div class="nav-group-label">System</div>
            <?php endif; ?>
            <?php if (role_can('messages')): ?>
            <a href="<?= BASE_URL ?>admin/messages.php" class="side-link <?= nav_active('messages.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 6 10 7L22 6"/></svg>
                Messages
                <?php if ($unreadMessages > 0): ?>
                    <span class="badge on" style="margin-left:auto;"><?= (int)$unreadMessages ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>
            <?php if (role_can('settings')): ?>
            <a href="<?= BASE_URL ?>admin/settings.php" class="side-link <?= nav_active('settings.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-1.8-.3 1.6 1.6 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.6 1.6 0 0 0-1-1.5 1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0 .3-1.8 1.6 1.6 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.6 1.6 0 0 0 1.5-1 1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H9a1.6 1.6 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 1 1.5 1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V9a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/></svg>
                Website Settings
            </a>
            <?php endif; ?>
            <?php if (role_can('users')): ?>
            <a href="<?= BASE_URL ?>admin/users.php" class="side-link <?= nav_active('users.php') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Admin Users
            </a>
            <?php endif; ?>
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

            <?php if ($onContactPage): ?>
                <div class="home-tabs">
                    <?php foreach ($contactPageSections as $file => $label): ?>
                        <a href="<?= BASE_URL ?>admin/<?= $file ?>" class="home-tab <?= nav_active($file) ?>"><?= e($label) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($onGemPage): ?>
                <div class="home-tabs">
                    <?php foreach ($gemPageSections as $section):
                        $href = BASE_URL . 'admin/' . $section['file'] . (isset($section['type']) ? '?type=' . urlencode($section['type']) : '');
                    ?>
                        <a href="<?= $href ?>" class="home-tab <?= gem_tab_active($section) ? 'active' : '' ?>">
                            <?= e($section['label']) ?>
                            <?php if ($section['file'] === 'enquiries.php' && $unreadEnquiries > 0): ?>
                                <span class="badge on" style="margin-left:6px;"><?= (int)$unreadEnquiries ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($onBlogPage): ?>
                <div class="home-tabs">
                    <?php foreach ($blogPageSections as $section): ?>
                        <a href="<?= BASE_URL ?>admin/<?= $section['file'] ?>" class="home-tab <?= blog_tab_active($section) ? 'active' : '' ?>"><?= e($section['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
