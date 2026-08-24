<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/xml; charset=UTF-8');

$urls = [
    ['loc' => site_url('index.php'),     'priority' => '1.0'],
    ['loc' => site_url('gemstones.php'), 'priority' => '0.9'],
    ['loc' => site_url('about.php'),     'priority' => '0.7'],
    ['loc' => site_url('blog.php'),      'priority' => '0.8'],
    ['loc' => site_url('contact.php'),   'priority' => '0.6'],
];

$products = db()->query("SELECT slug FROM products WHERE is_active = 1")->fetchAll();
foreach ($products as $p) {
    $urls[] = ['loc' => site_url('gemstone.php?slug=' . urlencode($p['slug'])), 'priority' => '0.6'];
}

$posts = db()->query("SELECT slug, updated_at FROM blog_posts WHERE status = 'published' AND published_at <= NOW()")->fetchAll();
foreach ($posts as $p) {
    $urls[] = [
        'loc'     => site_url('blog-post.php?slug=' . urlencode($p['slug'])),
        'lastmod' => date('Y-m-d', strtotime($p['updated_at'])),
        'priority'=> '0.6',
    ];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars($u['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
    if (!empty($u['lastmod'])) {
        echo '    <lastmod>' . htmlspecialchars($u['lastmod'], ENT_XML1, 'UTF-8') . "</lastmod>\n";
    }
    if (!empty($u['priority'])) {
        echo '    <priority>' . htmlspecialchars($u['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
    }
    echo "  </url>\n";
}
echo '</urlset>';
