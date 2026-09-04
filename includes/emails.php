<?php
/**
 * Order email notifications — plain PHP mail(), matching the existing
 * convention used in cart.php's enquiry emails (no HTML, no library).
 */
require_once __DIR__ . '/functions.php';

/* Shared bank-transfer instructions block, built from the bank_details content group */
function bank_transfer_instructions_text()
{
    $lines = [];
    if (c('bank_name')) $lines[] = 'Bank: ' . c('bank_name');
    if (c('bank_account_name')) $lines[] = 'Account Name: ' . c('bank_account_name');
    if (c('bank_account_number')) $lines[] = 'Account Number: ' . c('bank_account_number');
    if (c('bank_branch')) $lines[] = 'Branch: ' . c('bank_branch');
    if (c('bank_swift')) $lines[] = 'SWIFT / BIC: ' . c('bank_swift');
    if (c('bank_instructions_extra')) $lines[] = c('bank_instructions_extra');

    if (!$lines) return "Our team will send bank transfer details shortly.";
    return implode("\n", $lines);
}

function order_items_summary_text($items)
{
    $lines = [];
    foreach ($items as $it) {
        $meta = [];
        if ($it['weight'] !== null) $meta[] = $it['weight'] . ' ct';
        if ($it['shape']) $meta[] = $it['shape'];
        $lines[] = '- ' . $it['product_name'] . ($meta ? ' (' . implode(', ', $meta) . ')' : '') . ' — ' . format_money($it['line_total']);
    }
    return implode("\n", $lines);
}

function send_order_placed_email($order, $items)
{
    $siteName   = setting('site_name', 'Ruwanpura Gems');
    $adminEmail = setting('admin_email');
    $from       = $adminEmail ?: ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    $body = "Dear {$order['customer_name']},\n\n"
          . "Your order has been received and is currently being processed. We will confirm your final total, including shipping charges, before payment.\n\n"
          . "Order Number: {$order['order_number']}\n\n"
          . "Items:\n" . order_items_summary_text($items) . "\n\n"
          . "Gemstone Total: " . format_money($order['gemstone_total']) . "\n\n"
          . "- The displayed price is for the gemstone only. Shipping charges will be calculated separately.\n"
          . "- After you place your order, our team will contact you within a few hours to confirm the shipping charges and final total.\n"
          . "- The final amount, including shipping, will be confirmed with you before payment.\n\n"
          . "Best regards,\n$siteName";

    @mail($order['customer_email'], "Order Received — {$order['order_number']} — $siteName", $body, 'From: ' . $from);

    if ($adminEmail) {
        $adminBody = "A new order has been placed.\n\n"
                   . "Order Number: {$order['order_number']}\n"
                   . "Customer: {$order['customer_name']} ({$order['customer_email']})\n\n"
                   . "Items:\n" . order_items_summary_text($items) . "\n\n"
                   . "Gemstone Total: " . format_money($order['gemstone_total']) . "\n\n"
                   . "Log in to the admin panel to review shipping and add a shipping charge.";
        @mail($adminEmail, "New Order — {$order['order_number']}", $adminBody, 'From: ' . $adminEmail);
    }
}

function send_shipping_confirmation_email($order, $items)
{
    $siteName = setting('site_name', 'Ruwanpura Gems');
    $from     = setting('admin_email') ?: ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    $body = "Dear {$order['customer_name']},\n\n"
          . "Thank you for your order. We have now calculated your shipping charge.\n\n"
          . "Order Number: {$order['order_number']}\n\n"
          . "Items:\n" . order_items_summary_text($items) . "\n\n"
          . "Gemstone Total: " . format_money($order['gemstone_total']) . "\n"
          . "Shipping Charge: " . format_money($order['shipping_charge']) . "\n"
          . "Final Payable Total: " . format_money($order['final_total']) . "\n\n"
          . "Payment Method: Bank Transfer\n"
          . bank_transfer_instructions_text() . "\n\n"
          . "Please make your payment and reference your order number: {$order['order_number']}.\n\n"
          . "Best regards,\n$siteName";

    return (bool) @mail($order['customer_email'], "Your Final Total — {$order['order_number']} — $siteName", $body, 'From: ' . $from);
}

function send_payment_confirmation_email($order, $items)
{
    $siteName = setting('site_name', 'Ruwanpura Gems');
    $from     = setting('admin_email') ?: ('no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

    $body = "Dear {$order['customer_name']},\n\n"
          . "We have received and verified your payment. Thank you!\n\n"
          . "Order Number: {$order['order_number']}\n"
          . "Final Total Paid: " . format_money($order['final_total']) . "\n\n"
          . "Your order is now confirmed and will be prepared for shipment. We will keep you updated on its status.\n\n"
          . "Best regards,\n$siteName";

    return (bool) @mail($order['customer_email'], "Payment Confirmed — {$order['order_number']} — $siteName", $body, 'From: ' . $from);
}
