<?php
/**
 * Shared helper functions (front-end + admin)
 */
require_once __DIR__ . '/db.php';

/* ------------------------------------------------------------------ */
/*  Output escaping                                                    */
/* ------------------------------------------------------------------ */
function e($str)
{
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/* Preserve line breaks for textarea content on the front-end */
function e_nl($str)
{
    return nl2br(e($str));
}

/* ------------------------------------------------------------------ */
/*  Content blocks (key/value editable text + images)                  */
/* ------------------------------------------------------------------ */
function get_all_content()
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $rows = db()->query("SELECT block_key, block_value FROM content_blocks")->fetchAll();
    $cache = [];
    foreach ($rows as $r) {
        $cache[$r['block_key']] = $r['block_value'];
    }
    return $cache;
}

/* Get a single content block value */
function c($key, $default = '')
{
    $all = get_all_content();
    return isset($all[$key]) && $all[$key] !== null ? $all[$key] : $default;
}

/* Get image URL for a content block (falls back to placeholder) */
function c_img($key, $default = '')
{
    $val = c($key);
    if ($val) return UPLOAD_URL . e($val);
    return $default;
}

/* ------------------------------------------------------------------ */
/*  Settings                                                           */
/* ------------------------------------------------------------------ */
function get_all_settings()
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $rows = db()->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
    $cache = [];
    foreach ($rows as $r) {
        $cache[$r['setting_key']] = $r['setting_value'];
    }
    return $cache;
}

function setting($key, $default = '')
{
    $all = get_all_settings();
    return isset($all[$key]) && $all[$key] !== null && $all[$key] !== '' ? $all[$key] : $default;
}

/* Logo URL helper */
function logo_url()
{
    $logo = setting('site_logo');
    if ($logo) return UPLOAD_URL . e($logo);
    return BASE_URL . 'assets/images/logo.png';
}

/* White/light logo URL helper (for dark backgrounds e.g. footer) — falls back to the main logo */
function logo_white_url()
{
    $logo = setting('site_logo_white');
    if ($logo) return UPLOAD_URL . e($logo);
    return logo_url();
}

/* ------------------------------------------------------------------ */
/*  Maintenance mode (public pages)                                    */
/* ------------------------------------------------------------------ */
function maybe_show_maintenance_page()
{
    if (setting('maintenance_mode') !== '1' || !empty($_SESSION['admin_id'])) {
        return;
    }
    http_response_code(503);
    header('Retry-After: 3600');
    $siteName = setting('site_name', 'Ruwanpura Gems');
    $primary  = setting('theme_primary', '#c99a5b');
    $dark     = setting('theme_dark', '#0d0d0d');
    $message  = setting('maintenance_message', "We're currently performing scheduled maintenance. Please check back soon.");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Under Maintenance — <?= e($siteName) ?></title>
        <style>
            body {
                margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
                background: <?= e($dark) ?>; color: #f5f1e8; font-family: Georgia, 'Times New Roman', serif;
                text-align: center; padding: 24px; box-sizing: border-box;
            }
            .maint-box { max-width: 560px; }
            .maint-logo { max-height: 64px; margin-bottom: 28px; }
            h1 { font-size: 28px; letter-spacing: 1px; margin: 0 0 16px; color: <?= e($primary) ?>; }
            p { font-size: 16px; line-height: 1.7; color: #d8d2c4; }
        </style>
    </head>
    <body>
        <div class="maint-box">
            <img class="maint-logo" src="<?= logo_white_url() ?>" alt="<?= e($siteName) ?>">
            <h1>We'll be right back</h1>
            <p><?= e_nl($message) ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* ------------------------------------------------------------------ */
/*  Repeatable content fetchers                                        */
/* ------------------------------------------------------------------ */
function get_hero_slides()
{
    return db()->query("SELECT * FROM hero_slides WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_gemstones()
{
    return db()->query("SELECT * FROM gemstones WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_branches()
{
    return db()->query("SELECT * FROM branches WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_partners()
{
    return db()->query("SELECT * FROM partners WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_testimonials()
{
    return db()->query("SELECT * FROM testimonials WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_history_milestones()
{
    return db()->query("SELECT * FROM history_milestones WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_achievements()
{
    return db()->query("SELECT * FROM achievements WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}
function get_memberships()
{
    return db()->query("SELECT * FROM memberships WHERE is_active=1 ORDER BY sort_order, id")->fetchAll();
}

/* Image URL for a repeatable row image column */
function row_img($file, $placeholder = '')
{
    if ($file) return UPLOAD_URL . e($file);
    return $placeholder;
}

/* Per-slide text field, falling back to a default (e.g. the global hero content block) when blank */
function slide_text($slide, $field, $default = '')
{
    $val = $slide[$field] ?? null;
    return ($val !== null && $val !== '') ? $val : $default;
}

/* ------------------------------------------------------------------ */
/*  File uploads                                                       */
/* ------------------------------------------------------------------ */
function handle_upload($fileField, $oldFile = '')
{
    if (empty($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile; // nothing uploaded, keep existing
    }
    $file = $_FILES[$fileField];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $oldFile;
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return $oldFile; // reject invalid type
    }

    // size limit 8MB
    if ($file['size'] > 8 * 1024 * 1024) {
        return $oldFile;
    }

    if (!is_dir(UPLOAD_DIR)) {
        @mkdir(UPLOAD_DIR, 0755, true);
    }

    $newName = uniqid('img_', true) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $newName;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        // delete old file
        if ($oldFile && file_exists(UPLOAD_DIR . '/' . $oldFile)) {
            @unlink(UPLOAD_DIR . '/' . $oldFile);
        }
        return $newName;
    }
    return $oldFile;
}

/* ------------------------------------------------------------------ */
/*  Flash messages                                                     */
/* ------------------------------------------------------------------ */
function set_flash($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/* ------------------------------------------------------------------ */
/*  CSRF protection                                                    */
/* ------------------------------------------------------------------ */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function verify_csrf()
{
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        set_flash('error', 'Security token mismatch. Please try again.');
        return false;
    }
    return true;
}

/* ------------------------------------------------------------------ */
/*  Update helpers                                                     */
/* ------------------------------------------------------------------ */
function update_content($key, $value)
{
    $stmt = db()->prepare("UPDATE content_blocks SET block_value = ? WHERE block_key = ?");
    $stmt->execute([$value, $key]);
}
function update_setting($key, $value)
{
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->execute([$key, $value]);
}
