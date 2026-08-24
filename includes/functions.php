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

/* Absolute site URL for a given relative path — for canonical/OG tags and the sitemap,
   where a full https://domain/... URL is required rather than BASE_URL's relative '/'. */
function site_url($path = '')
{
    $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
    $scheme = $https ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . BASE_URL . ltrim($path, '/');
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
        <meta name="robots" content="noindex, nofollow">
        <title>Under Maintenance — <?= e($siteName) ?></title>
        <style>
            body {
                margin: 0; min-height: 100vh; min-height: 100dvh; display: flex; align-items: center; justify-content: center;
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
/* Latest active catalogue products, for the homepage "Explore Our Gemstones" section */
function get_latest_products($limit = 10)
{
    $limit = max(1, (int) $limit);
    try {
        $sql = "SELECT p.*,
                       (SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS thumb
                FROM products p
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC, p.id DESC
                LIMIT $limit";
        return db()->query($sql)->fetchAll();
    } catch (PDOException $e) {
        return []; // catalogue tables not migrated yet
    }
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

/* ------------------------------------------------------------------ */
/*  Contact messages                                                   */
/* ------------------------------------------------------------------ */
function count_unread_messages()
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0")->fetchColumn();
    } catch (PDOException $e) {
        return 0; // table not migrated yet
    }
}

function count_unread_enquiries()
{
    try {
        return (int) db()->query("SELECT COUNT(*) FROM enquiries WHERE is_read = 0")->fetchColumn();
    } catch (PDOException $e) {
        return 0; // table not migrated yet
    }
}

/* ------------------------------------------------------------------ */
/*  Gemstone catalogue                                                 */
/* ------------------------------------------------------------------ */
function get_categories($activeOnly = true)
{
    $sql = "SELECT * FROM gem_categories" . ($activeOnly ? " WHERE is_active=1" : "") . " ORDER BY sort_order, id";
    return db()->query($sql)->fetchAll();
}
function get_shapes($activeOnly = true)
{
    $sql = "SELECT * FROM gem_shapes" . ($activeOnly ? " WHERE is_active=1" : "") . " ORDER BY sort_order, id";
    return db()->query($sql)->fetchAll();
}
function get_treatments($activeOnly = true)
{
    $sql = "SELECT * FROM gem_treatments" . ($activeOnly ? " WHERE is_active=1" : "") . " ORDER BY sort_order, id";
    return db()->query($sql)->fetchAll();
}
function get_origins($activeOnly = true)
{
    $sql = "SELECT * FROM gem_origins" . ($activeOnly ? " WHERE is_active=1" : "") . " ORDER BY sort_order, id";
    return db()->query($sql)->fetchAll();
}

/* Lookup a name by id from a small reference table (cached per-request) */
function lookup_name($table, $id)
{
    static $cache = [];
    if (!$id) return '';
    $allowed = ['gem_categories', 'gem_shapes', 'gem_treatments', 'gem_origins'];
    if (!in_array($table, $allowed, true)) return '';
    if (!isset($cache[$table])) {
        $cache[$table] = [];
        foreach (db()->query("SELECT id, name FROM `$table`")->fetchAll() as $r) {
            $cache[$table][$r['id']] = $r['name'];
        }
    }
    return $cache[$table][$id] ?? '';
}

/* Fixed carat-weight buckets used for catalogue filtering */
function weight_ranges()
{
    return [
        'u1'   => ['label' => 'Under 1 ct',    'min' => 0,  'max' => 1],
        '1-3'  => ['label' => '1 – 3 ct',       'min' => 1,  'max' => 3],
        '3-5'  => ['label' => '3 – 5 ct',       'min' => 3,  'max' => 5],
        '5-10' => ['label' => '5 – 10 ct',      'min' => 5,  'max' => 10],
        '10p'  => ['label' => '10 ct & above',  'min' => 10, 'max' => null],
    ];
}

function product_status_labels()
{
    return [
        'available'   => 'Available',
        'reserved'    => 'Reserved',
        'sold'        => 'Sold',
        'unavailable' => 'Unavailable',
    ];
}

/* Build the $filters array for get_products() from $_GET — shared by gemstones.php
   (full page load) and filter-products.php (AJAX partial reload) so both read the
   same query-string shape identically. */
function product_filters_from_get()
{
    return [
        'category'  => array_map('intval', $_GET['category'] ?? []),
        'shape'     => array_map('intval', $_GET['shape'] ?? []),
        'treatment' => array_map('intval', $_GET['treatment'] ?? []),
        'origin'    => array_map('intval', $_GET['origin'] ?? []),
        'weight'    => array_values(array_intersect((array) ($_GET['weight'] ?? []), array_keys(weight_ranges()))),
        'status'    => array_values(array_intersect((array) ($_GET['status'] ?? []), array_keys(product_status_labels()))),
    ];
}

/* Filtered + paginated product listing for the catalogue page */
function get_products($filters = [], $page = 1, $perPage = 12)
{
    $where  = ['p.is_active = 1'];
    $params = [];

    foreach (['category' => 'category_id', 'shape' => 'shape_id', 'treatment' => 'treatment_id', 'origin' => 'origin_id'] as $key => $col) {
        if (!empty($filters[$key]) && is_array($filters[$key])) {
            $ids = array_filter(array_map('intval', $filters[$key]));
            if ($ids) {
                $in = implode(',', array_fill(0, count($ids), '?'));
                $where[] = "p.$col IN ($in)";
                foreach ($ids as $v) $params[] = $v;
            }
        }
    }

    if (!empty($filters['status']) && is_array($filters['status'])) {
        $valid = array_keys(product_status_labels());
        $statuses = array_values(array_intersect($filters['status'], $valid));
        if ($statuses) {
            $in = implode(',', array_fill(0, count($statuses), '?'));
            $where[] = "p.status IN ($in)";
            foreach ($statuses as $v) $params[] = $v;
        }
    }

    if (!empty($filters['weight']) && is_array($filters['weight'])) {
        $ranges = weight_ranges();
        $clauses = [];
        foreach ($filters['weight'] as $key) {
            if (!isset($ranges[$key])) continue;
            $r = $ranges[$key];
            $clauses[] = $r['max'] === null
                ? '(p.weight >= ' . (float)$r['min'] . ')'
                : '(p.weight >= ' . (float)$r['min'] . ' AND p.weight < ' . (float)$r['max'] . ')';
        }
        if ($clauses) $where[] = '(' . implode(' OR ', $clauses) . ')';
    }

    $whereSql = implode(' AND ', $where);

    $countStmt = db()->prepare("SELECT COUNT(*) FROM products p WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $perPage = max(1, (int) $perPage);
    $page    = max(1, (int) $page);
    $offset  = ($page - 1) * $perPage;

    $sql = "SELECT p.*,
                   (SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS thumb
            FROM products p
            WHERE $whereSql
            ORDER BY p.sort_order, p.id DESC
            LIMIT $perPage OFFSET $offset";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => (int) ceil($total / $perPage),
        'page'  => $page,
    ];
}

function get_product_by_slug($slug)
{
    $stmt = db()->prepare("SELECT * FROM products WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function get_product_images($productId)
{
    $stmt = db()->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id");
    $stmt->execute([(int) $productId]);
    return $stmt->fetchAll();
}

/* "You May Also Like" — same-category gemstones first, topped up with other latest active products */
function get_related_products($product, $limit = 4)
{
    $limit = max(1, (int) $limit);
    $thumbSql = "(SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS thumb";

    try {
        $items = [];

        if (!empty($product['category_id'])) {
            $stmt = db()->prepare("SELECT p.*, $thumbSql
                    FROM products p
                    WHERE p.is_active = 1 AND p.id <> ? AND p.category_id = ?
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT $limit");
            $stmt->execute([(int) $product['id'], (int) $product['category_id']]);
            $items = $stmt->fetchAll();
        }

        if (count($items) < $limit) {
            $need       = $limit - count($items);
            $excludeIds = array_merge([(int) $product['id']], array_map('intval', array_column($items, 'id')));
            $in         = implode(',', array_fill(0, count($excludeIds), '?'));
            $stmt2 = db()->prepare("SELECT p.*, $thumbSql
                    FROM products p
                    WHERE p.is_active = 1 AND p.id NOT IN ($in)
                    ORDER BY p.created_at DESC, p.id DESC
                    LIMIT $need");
            $stmt2->execute($excludeIds);
            $items = array_merge($items, $stmt2->fetchAll());
        }

        return $items;
    } catch (PDOException $e) {
        return [];
    }
}

/* Search-as-you-type: name contains the query, "starts with" matches ranked first */
function search_products($query, $limit = 8)
{
    $query = trim((string) $query);
    if ($query === '') return [];
    $limit = max(1, (int) $limit);

    try {
        $stmt = db()->prepare("
            SELECT p.name, p.slug,
                   (SELECT image FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order, id LIMIT 1) AS thumb
            FROM products p
            WHERE p.is_active = 1 AND p.name LIKE ?
            ORDER BY (p.name LIKE ?) DESC, p.name ASC
            LIMIT $limit
        ");
        $stmt->execute(['%' . $query . '%', $query . '%']);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/* ------------------------------------------------------------------ */
/*  Blog (Gemstone Insights)                                           */
/* ------------------------------------------------------------------ */
function get_blog_categories($activeOnly = true)
{
    $sql = "SELECT * FROM blog_categories" . ($activeOnly ? " WHERE is_active=1" : "") . " ORDER BY sort_order, id";
    return db()->query($sql)->fetchAll();
}

function blog_status_labels()
{
    return ['draft' => 'Draft', 'published' => 'Published'];
}

/* Filtered + paginated published post listing for the public blog page */
function get_blog_posts($filters = [], $page = 1, $perPage = 9)
{
    $where  = ["p.status = 'published'", 'p.published_at <= NOW()'];
    $params = [];

    if (!empty($filters['category'])) {
        $where[] = 'p.category_id = ?';
        $params[] = (int) $filters['category'];
    }
    if (!empty($filters['search'])) {
        $where[] = '(p.title LIKE ? OR p.excerpt LIKE ?)';
        $like = '%' . $filters['search'] . '%';
        $params[] = $like;
        $params[] = $like;
    }

    $whereSql = implode(' AND ', $where);

    $countStmt = db()->prepare("SELECT COUNT(*) FROM blog_posts p WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $perPage = max(1, (int) $perPage);
    $page    = max(1, (int) $page);
    $offset  = ($page - 1) * $perPage;

    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM blog_posts p
            LEFT JOIN blog_categories c ON c.id = p.category_id
            WHERE $whereSql
            ORDER BY p.published_at DESC, p.id DESC
            LIMIT $perPage OFFSET $offset";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(),
        'total' => $total,
        'pages' => (int) ceil($total / $perPage),
        'page'  => $page,
    ];
}

function get_post_by_slug($slug)
{
    $stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                            FROM blog_posts p
                            LEFT JOIN blog_categories c ON c.id = p.category_id
                            WHERE p.slug = ? AND p.status = 'published' AND p.published_at <= NOW()");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/* Related articles — same category first, topped up with other latest published posts */
function get_related_posts($post, $limit = 3)
{
    $limit = max(1, (int) $limit);
    try {
        $items = [];

        if (!empty($post['category_id'])) {
            $stmt = db()->prepare("SELECT * FROM blog_posts
                    WHERE status='published' AND published_at <= NOW() AND id <> ? AND category_id = ?
                    ORDER BY published_at DESC, id DESC
                    LIMIT $limit");
            $stmt->execute([(int) $post['id'], (int) $post['category_id']]);
            $items = $stmt->fetchAll();
        }

        if (count($items) < $limit) {
            $need       = $limit - count($items);
            $excludeIds = array_merge([(int) $post['id']], array_map('intval', array_column($items, 'id')));
            $in         = implode(',', array_fill(0, count($excludeIds), '?'));
            $stmt2 = db()->prepare("SELECT * FROM blog_posts
                    WHERE status='published' AND published_at <= NOW() AND id NOT IN ($in)
                    ORDER BY published_at DESC, id DESC
                    LIMIT $need");
            $stmt2->execute($excludeIds);
            $items = array_merge($items, $stmt2->fetchAll());
        }

        return $items;
    } catch (PDOException $e) {
        return [];
    }
}

/* Latest published posts for the "Recent Articles" sidebar widget, excluding the current post */
function get_recent_posts($excludeId = 0, $limit = 5)
{
    $limit = max(1, (int) $limit);
    try {
        $stmt = db()->prepare("SELECT * FROM blog_posts
                WHERE status='published' AND published_at <= NOW() AND id <> ?
                ORDER BY published_at DESC, id DESC
                LIMIT $limit");
        $stmt->execute([(int) $excludeId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/* Render blog body text as paragraphs (blank-line separated), escaped for safety */
function render_blog_content($text)
{
    $text = trim((string) $text);
    if ($text === '') return '';
    $blocks = preg_split('/\n\s*\n/', $text);
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') continue;
        $html .= '<p>' . e_nl($block) . '</p>';
    }
    return $html;
}

/* URL-safe slug from a string */
function slugify($text)
{
    $text = preg_replace('~[^\pL\d]+~u', '-', (string) $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    $text = preg_replace('~[^-a-z0-9]+~', '', $text);
    return $text !== '' ? $text : 'item';
}

/* Unique slug for a table's `slug` column, excluding a given id (for edits) */
function unique_slug($table, $name, $excludeId = 0)
{
    $allowed = ['products', 'gem_categories', 'blog_posts', 'blog_categories'];
    if (!in_array($table, $allowed, true)) {
        throw new InvalidArgumentException('Invalid table for unique_slug()');
    }
    $base = slugify($name);
    $slug = $base;
    $i = 2;
    while (true) {
        $stmt = db()->prepare("SELECT COUNT(*) FROM `$table` WHERE slug = ? AND id <> ?");
        $stmt->execute([$slug, (int) $excludeId]);
        if ((int) $stmt->fetchColumn() === 0) break;
        $slug = $base . '-' . $i;
        $i++;
    }
    return $slug;
}

/* Normalize a pasted YouTube link (watch/share/shorts URL, or a full <iframe> embed
   snippet) into a plain embeddable URL. Non-YouTube URLs are returned as-is, so a
   direct Vimeo/other embed src still works. */
function video_embed_url($url)
{
    $url = trim((string) $url);
    if ($url === '') return '';

    if (stripos($url, '<iframe') !== false && preg_match('/src=["\']([^"\']+)["\']/i', $url, $m)) {
        $url = html_entity_decode($m[1]);
    }

    if (preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~[?&]v=([A-Za-z0-9_-]{6,})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    if (preg_match('~youtube\.com/(?:embed|shorts)/([A-Za-z0-9_-]{6,})~i', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }

    return $url;
}

/* Best-effort flag emoji for a phone number, based on its leading +country code.
   Longer/more specific codes are checked first to avoid mismatching a shorter prefix. */
function phone_flag($phone)
{
    $phone = trim((string) $phone);
    if ($phone === '' || $phone[0] !== '+') return '';

    static $codes = [
        '+971' => '🇦🇪', '+966' => '🇸🇦', '+960' => '🇲🇻', '+886' => '🇹🇼',
        '+852' => '🇭🇰', '+853' => '🇲🇴', '+673' => '🇧🇳',
        '+94'  => '🇱🇰', '+66'  => '🇹🇭', '+65'  => '🇸🇬', '+95'  => '🇲🇲',
        '+91'  => '🇮🇳', '+92'  => '🇵🇰', '+86'  => '🇨🇳', '+81'  => '🇯🇵',
        '+82'  => '🇰🇷', '+84'  => '🇻🇳', '+63'  => '🇵🇭', '+62'  => '🇮🇩',
        '+61'  => '🇦🇺', '+64'  => '🇳🇿', '+44'  => '🇬🇧', '+49'  => '🇩🇪',
        '+33'  => '🇫🇷', '+39'  => '🇮🇹', '+34'  => '🇪🇸', '+31'  => '🇳🇱',
        '+41'  => '🇨🇭', '+27'  => '🇿🇦',
        '+1'   => '🇺🇸',
    ];

    foreach ($codes as $code => $flag) {
        if (strpos($phone, $code) === 0) return $flag;
    }
    return '';
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
function handle_upload($fileField, $oldFile = '', $allowed = null, $maxBytes = null)
{
    if (empty($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldFile; // nothing uploaded, keep existing
    }
    $file = $_FILES[$fileField];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return $oldFile;
    }

    $allowed = $allowed ?? ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) {
        return $oldFile; // reject invalid type
    }

    $maxBytes = $maxBytes ?? (8 * 1024 * 1024); // default 8MB
    if ($file['size'] > $maxBytes) {
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

/* Video file upload (mp4/webm/mov), up to 80MB by default */
function handle_video_upload($fileField, $oldFile = '', $maxBytes = null)
{
    return handle_upload($fileField, $oldFile, ['mp4', 'webm', 'mov'], $maxBytes ?? (80 * 1024 * 1024));
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
