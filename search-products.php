<?php
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Keep the maintenance gate's spirit without rendering its HTML page — an
// admin browsing while maintenance mode is on should still get live results.
if (setting('maintenance_mode') === '1' && empty($_SESSION['admin_id'])) {
    echo json_encode([]);
    exit;
}

$q = trim($_GET['q'] ?? '');
$rows = search_products($q, 8);

$results = array_map(function ($r) {
    return [
        'name'  => $r['name'],
        'thumb' => $r['thumb'] ? UPLOAD_URL . $r['thumb'] : '',
        'url'   => BASE_URL . 'gemstone.php?slug=' . urlencode($r['slug']),
    ];
}, $rows);

echo json_encode($results);
