<?php
/**
 * Shared section-editor logic.
 * Set $page_title, $section_group, and optionally $section_intro before including.
 */
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

$__sectionRoleMap = [
    'header' => 'home', 'hero' => 'home', 'journey' => 'home', 'collection' => 'home',
    'factory' => 'home', 'branches' => 'home', 'events' => 'home', 'testimonials' => 'home',
    'cta' => 'home', 'footer' => 'home',
    'about_hero' => 'about', 'about_evolution' => 'about', 'history' => 'about',
    'about_video' => 'about', 'awards' => 'about', 'gubelin' => 'about', 'membership' => 'about',
    'contact_hero' => 'contact', 'contact' => 'contact',
];
require_role($__sectionRoleMap[$section_group] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        save_content_group();
        set_flash('success', 'Changes saved successfully.');
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

$blocks = content_group($section_group);

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card">
        <div class="card-head-row">
            <div>
                <h2><?= e($page_title) ?></h2>
                <?php if (!empty($section_intro)): ?>
                    <p class="card-sub" style="margin:4px 0 0;"><?= e($section_intro) ?></p>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                Save Changes
            </button>
        </div>

        <?php foreach ($blocks as $block): ?>
            <?php render_content_field($block); ?>
        <?php endforeach; ?>

        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
