<?php
/**
 * Returns just the catalogue results fragment (count + grid + pagination) for
 * the gemstones.php filter sidebar's instant, no-page-reload updates.
 */
require_once __DIR__ . '/includes/functions.php';

$filters = product_filters_from_get();
$page    = max(1, (int) ($_GET['page'] ?? 1));

$result       = get_products($filters, $page, 12);
$statusLabels = product_status_labels();

include __DIR__ . '/includes/gemstone-results.php';
