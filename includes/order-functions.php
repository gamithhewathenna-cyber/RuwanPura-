<?php
/**
 * Order creation + status transitions (Bank Transfer only).
 */
require_once __DIR__ . '/functions.php';

/**
 * Place a new order for the given customer.
 *
 * @param array $cartItems list of ['id' => int, 'qty' => int] pairs (as sent by checkout.js)
 * @return array ['id' => int, 'order_number' => string] on success,
 *               ['errors' => string[]] on failure (e.g. an item became unavailable or is out of stock).
 */
function place_order($customerId, array $cartItems, array $addressSnapshot, $paymentMethod = 'bank_transfer')
{
    // Normalize + dedupe by product id, summing quantities for any accidental duplicates
    $requested = [];
    foreach ($cartItems as $row) {
        $pid = isset($row['id']) ? (int) $row['id'] : 0;
        $qty = isset($row['qty']) ? (int) $row['qty'] : 1;
        if ($pid <= 0 || $qty <= 0) continue;
        $requested[$pid] = ($requested[$pid] ?? 0) + $qty;
    }
    if (!$requested) {
        return ['errors' => ['Your cart is empty.']];
    }

    $customerRow = db()->prepare("SELECT * FROM customers WHERE id = ?");
    $customerRow->execute([$customerId]);
    $customer = $customerRow->fetch();
    if (!$customer) {
        return ['errors' => ['Your session has expired. Please log in again.']];
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $items          = [];
        $stockUpdates   = []; // product_id => new quantity, status
        $unavailable    = [];
        $originalTotal  = 0.0;
        $discountTotal  = 0.0;

        foreach ($requested as $pid => $qty) {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? FOR UPDATE");
            $stmt->execute([$pid]);
            $product = $stmt->fetch();

            $stock = $product ? (int) ($product['quantity'] ?? 1) : 0;

            if (!$product || !$product['is_active'] || $product['status'] !== 'available' || $stock < $qty) {
                $unavailable[] = $product ? $product['name'] . ($stock > 0 && $stock < $qty ? " (only $stock in stock)" : '') : ('#' . $pid);
                continue;
            }

            $pricing = product_pricing($product);
            $unit    = $pricing['original'] ?? 0.0;
            $final   = $pricing['final'] ?? 0.0;
            $discountPerUnit = round($unit - $final, 2);

            $imgStmt = $pdo->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id LIMIT 1");
            $imgStmt->execute([$pid]);
            $image = $imgStmt->fetchColumn();

            $shapeName = lookup_name('gem_shapes', $product['shape_id']);

            $items[] = [
                'product_id'      => $pid,
                'product_name'    => $product['name'],
                'weight'          => $product['weight'],
                'shape'           => $shapeName,
                'sku'             => $product['sku'],
                'image'           => $image ?: null,
                'quantity'        => $qty,
                'unit_price'      => $unit,
                'discount_amount' => round($discountPerUnit * $qty, 2),
                'line_total'      => round($final * $qty, 2),
            ];

            $newStock = $stock - $qty;
            $stockUpdates[$pid] = [
                'quantity' => $newStock,
                'status'   => $newStock <= 0 ? 'reserved' : $product['status'],
            ];

            $originalTotal += round($unit * $qty, 2);
            $discountTotal += round($discountPerUnit * $qty, 2);
        }

        if ($unavailable) {
            $pdo->rollBack();
            return ['errors' => ['The following item(s) are no longer available in the requested quantity: ' . implode(', ', $unavailable) . '.'], 'unavailable' => $unavailable];
        }
        if (!$items) {
            $pdo->rollBack();
            return ['errors' => ['Your cart is empty.']];
        }

        $gemstoneTotal = round($originalTotal - $discountTotal, 2);
        $sameAsBilling = !empty($addressSnapshot['shipping_same_as_billing']);
        $orderNumber   = generate_order_number();

        $insertOrder = $pdo->prepare("INSERT INTO orders (
                order_number, customer_id, customer_name, customer_email, customer_phone, customer_country,
                billing_address_line1, billing_address_line2, billing_city, billing_state, billing_postal_code, billing_country,
                shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_postal_code, shipping_country,
                payment_method, items_original_total, items_discount_total, gemstone_total, order_status
            ) VALUES (?, ?,?,?,?,?, ?,?,?,?,?,?, ?,?,?,?,?,?, 'bank_transfer', ?,?,?, 'order_in_process')");
        $insertOrder->execute([
            $orderNumber,
            $customerId,
            $customer['full_name'],
            $customer['email'],
            $addressSnapshot['phone'] ?? $customer['phone'],
            $addressSnapshot['country'] ?? $customer['country'],
            $addressSnapshot['billing_address_line1'] ?? '',
            $addressSnapshot['billing_address_line2'] ?? '',
            $addressSnapshot['billing_city'] ?? '',
            $addressSnapshot['billing_state'] ?? '',
            $addressSnapshot['billing_postal_code'] ?? '',
            $addressSnapshot['billing_country'] ?? '',
            $sameAsBilling ? ($addressSnapshot['billing_address_line1'] ?? '') : ($addressSnapshot['shipping_address_line1'] ?? ''),
            $sameAsBilling ? ($addressSnapshot['billing_address_line2'] ?? '') : ($addressSnapshot['shipping_address_line2'] ?? ''),
            $sameAsBilling ? ($addressSnapshot['billing_city'] ?? '') : ($addressSnapshot['shipping_city'] ?? ''),
            $sameAsBilling ? ($addressSnapshot['billing_state'] ?? '') : ($addressSnapshot['shipping_state'] ?? ''),
            $sameAsBilling ? ($addressSnapshot['billing_postal_code'] ?? '') : ($addressSnapshot['shipping_postal_code'] ?? ''),
            $sameAsBilling ? ($addressSnapshot['billing_country'] ?? '') : ($addressSnapshot['shipping_country'] ?? ''),
            $originalTotal,
            $discountTotal,
            $gemstoneTotal,
        ]);

        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, weight, shape, sku, image, quantity, unit_price, discount_amount, line_total)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        foreach ($items as $it) {
            $itemStmt->execute([
                $orderId, $it['product_id'], $it['product_name'], $it['weight'], $it['shape'], $it['sku'], $it['image'],
                $it['quantity'], $it['unit_price'], $it['discount_amount'], $it['line_total'],
            ]);
        }

        $stockStmt = $pdo->prepare("UPDATE products SET quantity = ?, status = ? WHERE id = ?");
        foreach ($stockUpdates as $pid => $upd) {
            $stockStmt->execute([$upd['quantity'], $upd['status'], $pid]);
        }

        $pdo->commit();

        return ['id' => $orderId, 'order_number' => $orderNumber];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return ['errors' => ['Something went wrong placing your order. Please try again.']];
    }
}

/**
 * Apply an order-status change and its product-status side effects (decision A),
 * atomically. Target-status-driven so it's safe for any admin jump, not just the
 * happy-path sequence.
 */
function apply_order_status_transition($orderId, $newStatus)
{
    if (!array_key_exists($newStatus, order_status_labels())) {
        return false;
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $orderStmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
        $orderStmt->execute([$orderId]);
        $order = $orderStmt->fetch();
        if (!$order) {
            $pdo->rollBack();
            return false;
        }

        $lineStmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL");
        $lineStmt->execute([$orderId]);
        $lines = $lineStmt->fetchAll();

        $now = date('Y-m-d H:i:s');
        $paymentConfirmedAt = $order['payment_confirmed_at'];

        if (in_array($newStatus, ['payment_verified', 'order_confirmed', 'shipped', 'completed'], true)) {
            // Only flip a product to 'sold' once its live stock is actually exhausted —
            // if other units of the same product remain, it stays purchasable.
            foreach ($lines as $line) {
                $pStmt = $pdo->prepare("SELECT quantity, status FROM products WHERE id = ? FOR UPDATE");
                $pStmt->execute([$line['product_id']]);
                $p = $pStmt->fetch();
                if ($p && (int) $p['quantity'] <= 0 && $p['status'] !== 'sold') {
                    $pdo->prepare("UPDATE products SET status = 'sold' WHERE id = ?")->execute([$line['product_id']]);
                }
            }
            if (!$paymentConfirmedAt) $paymentConfirmedAt = $now;
        } elseif ($newStatus === 'cancelled' && $order['order_status'] !== 'cancelled') {
            // Restore the exact quantity this order had reserved for each product,
            // and revert to Available if the product was auto-marked reserved/sold.
            foreach ($lines as $line) {
                $pStmt = $pdo->prepare("SELECT quantity, status FROM products WHERE id = ? FOR UPDATE");
                $pStmt->execute([$line['product_id']]);
                $p = $pStmt->fetch();
                if (!$p) continue;
                $restoredQty = (int) $p['quantity'] + (int) $line['quantity'];
                $newStatusForProduct = ($restoredQty > 0 && in_array($p['status'], ['reserved', 'sold'], true)) ? 'available' : $p['status'];
                $pdo->prepare("UPDATE products SET quantity = ?, status = ? WHERE id = ?")
                    ->execute([$restoredQty, $newStatusForProduct, $line['product_id']]);
            }
        }

        $pdo->prepare("UPDATE orders SET order_status = ?, payment_confirmed_at = ? WHERE id = ?")
            ->execute([$newStatus, $paymentConfirmedAt, $orderId]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

/* Set/update an order's shipping charge, computing final_total, and auto-advance
   order_status to 'shipping_quoted' the first time a charge is entered. */
function set_order_shipping_charge($orderId, $shippingCharge)
{
    $stmt = db()->prepare("SELECT gemstone_total, order_status FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) return false;

    $shippingCharge = max(0, (float) $shippingCharge);
    $finalTotal     = round((float) $order['gemstone_total'] + $shippingCharge, 2);
    $newStatus      = $order['order_status'] === 'order_in_process' ? 'shipping_quoted' : $order['order_status'];

    db()->prepare("UPDATE orders SET shipping_charge=?, final_total=?, order_status=?, shipping_confirmed_at=IFNULL(shipping_confirmed_at, NOW()) WHERE id=?")
        ->execute([$shippingCharge, $finalTotal, $newStatus, $orderId]);

    return true;
}

function get_order_items($orderId)
{
    $stmt = db()->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id");
    $stmt->execute([(int) $orderId]);
    return $stmt->fetchAll();
}
