<?php
$page_title = 'Website Settings';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        // Site name
        update_setting('site_name', trim($_POST['site_name'] ?? 'Ruwanpura Gems'));

        // Theme colours
        update_setting('theme_primary', trim($_POST['theme_primary'] ?? '#c99a5b'));
        update_setting('theme_dark', trim($_POST['theme_dark'] ?? '#0d0d0d'));

        // Admin email (contact) — also updates the logged-in admin's login email
        $newEmail = trim($_POST['admin_email'] ?? '');
        if ($newEmail && filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            update_setting('admin_email', $newEmail);
        }

        // Logo upload
        $oldLogo = setting('site_logo');
        $newLogo = handle_upload('site_logo', $oldLogo);
        if ($newLogo !== $oldLogo) {
            update_setting('site_logo', $newLogo);
        }

        // White / light logo upload (for dark backgrounds)
        $oldLogoWhite = setting('site_logo_white');
        $newLogoWhite = handle_upload('site_logo_white', $oldLogoWhite);
        if ($newLogoWhite !== $oldLogoWhite) {
            update_setting('site_logo_white', $newLogoWhite);
        }

        // Maintenance mode
        update_setting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0');
        update_setting('maintenance_message', trim($_POST['maintenance_message'] ?? ''));

        set_flash('success', 'Settings saved successfully.');
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

require_once __DIR__ . '/layout-top.php';
?>

<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="card">
        <h2>Branding</h2>
        <p class="card-sub">Your website logo and name.</p>

        <div class="form-group">
            <label>Website Logo <span class="hint">(PNG or SVG recommended, transparent background, ~400 × 120px)</span></label>
            <div class="img-field">
                <div class="img-preview" id="logoPrev" style="background:#1b1e27;">
                    <?php if (setting('site_logo')): ?>
                        <img src="<?= UPLOAD_URL . e(setting('site_logo')) ?>">
                    <?php else: ?>
                        <img src="<?= logo_url() ?>">
                    <?php endif; ?>
                </div>
                <div class="upload-btn-wrap">
                    <button type="button" class="btn btn-sm">Choose Logo</button>
                    <input type="file" name="site_logo" accept="image/*" data-preview="logoPrev">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>White Logo <span class="hint">(used on dark backgrounds, e.g. the footer, ~400 × 120px)</span></label>
            <div class="img-field">
                <div class="img-preview" id="logoWhitePrev" style="background:#1b1e27;">
                    <?php if (setting('site_logo_white')): ?>
                        <img src="<?= UPLOAD_URL . e(setting('site_logo_white')) ?>">
                    <?php else: ?>
                        <img src="<?= logo_white_url() ?>">
                    <?php endif; ?>
                </div>
                <div class="upload-btn-wrap">
                    <button type="button" class="btn btn-sm">Choose White Logo</button>
                    <input type="file" name="site_logo_white" accept="image/*" data-preview="logoWhitePrev">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Website Name</label>
            <input type="text" name="site_name" class="form-control" value="<?= e(setting('site_name','Ruwanpura Gems')) ?>">
        </div>
    </div>

    <div class="card">
        <h2>Colour Theme</h2>
        <p class="card-sub">These colours are applied across the website automatically.</p>

        <div class="form-row">
            <div class="form-group">
                <label>Primary / Gold Accent</label>
                <div class="color-input-row">
                    <input type="color" id="primaryColor" value="<?= e(setting('theme_primary','#c99a5b')) ?>"
                           oninput="document.getElementById('primaryText').value=this.value">
                    <input type="text" id="primaryText" name="theme_primary" class="form-control"
                           value="<?= e(setting('theme_primary','#c99a5b')) ?>"
                           oninput="document.getElementById('primaryColor').value=this.value">
                </div>
            </div>
            <div class="form-group">
                <label>Dark / Base Colour</label>
                <div class="color-input-row">
                    <input type="color" id="darkColor" value="<?= e(setting('theme_dark','#0d0d0d')) ?>"
                           oninput="document.getElementById('darkText').value=this.value">
                    <input type="text" id="darkText" name="theme_dark" class="form-control"
                           value="<?= e(setting('theme_dark','#0d0d0d')) ?>"
                           oninput="document.getElementById('darkColor').value=this.value">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Contact / Admin Email</h2>
        <p class="card-sub">The main administrative email address for the website.</p>
        <div class="form-group">
            <label>Admin Email Address</label>
            <input type="email" name="admin_email" class="form-control" value="<?= e(setting('admin_email')) ?>">
        </div>
    </div>

    <div class="card">
        <h2>Maintenance Mode</h2>
        <p class="card-sub">Temporarily take the public website offline while you make changes. Logged-in admins can still browse the site normally.</p>

        <div class="form-group">
            <label class="switch-label">
                <input type="checkbox" name="maintenance_mode" value="1" <?= setting('maintenance_mode') === '1' ? 'checked' : '' ?>>
                Enable maintenance mode
            </label>
        </div>

        <div class="form-group">
            <label>Message shown to visitors</label>
            <textarea name="maintenance_message" class="form-control" rows="3"><?= e(setting('maintenance_message', "We're currently performing scheduled maintenance. Please check back soon.")) ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save All Settings</button>
</form>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
