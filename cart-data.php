<?php
/**
 * AJAX endpoint: given a list of product ids, return server-computed pricing/
 * availability for each. The client (cart.php, checkout.php) never supplies
 * or trusts a price — this endpoint is the single source of truth for display.
 */
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$ids = array_values(array_unique(array_filter(array_map('intval', $payload['ids'] ?? []))));

$items = [];
if ($ids) {
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $byId = [];
    foreach ($rows as $r) $byId[(int) $r['id']] = $r;

    $statusLabels = product_status_labels();

    foreach ($ids as $id) {
        if (!isset($byId[$id])) {
            $items[] = ['id' => $id, 'found' => false, 'available' => false];
            continue;
        }
        $p = $byId[$id];
        $pricing = product_pricing($p);
        $images  = get_product_images($id);
        $shapeName = lookup_name('gem_shapes', $p['shape_id']);

        $stock = (int) ($p['quantity'] ?? 1);

        $items[] = [
            'id'            => $id,
            'found'         => true,
            'name'          => $p['name'],
            'slug'          => $p['slug'],
            'weight'        => $p['weight'],
            'shape'         => $shapeName,
            'image'         => $images ? UPLOAD_URL . $images[0]['image'] : '',
            'status'        => $p['status'],
            'status_label'  => $statusLabels[$p['status']] ?? $p['status'],
            'stock'         => $stock,
            'available'     => $p['is_active'] == 1 && $p['status'] === 'available' && $stock > 0,
            'price'         => $pricing['original'],
            'final_price'   => $pricing['final'],
            'has_discount'  => $pricing['has_discount'],
            'price_display' => $pricing['original'] !== null ? format_money($pricing['original']) : null,
            'final_display' => $pricing['final'] !== null ? format_money($pricing['final']) : null,
        ];
    }
}

echo json_encode(['items' => $items]);
