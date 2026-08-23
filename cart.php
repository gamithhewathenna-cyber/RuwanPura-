<?php
require_once __DIR__ . '/includes/functions.php';
maybe_show_maintenance_page();

$formSuccess  = false;
$formErrors   = [];
$submittedName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquiry_submit'])) {
    if (!verify_csrf()) {
        $formErrors[] = 'Security check failed — please try again.';
    } else {
        $name    = trim($_POST['full_name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $country = trim($_POST['country'] ?? '');
        $message = trim($_POST['message'] ?? '');

        $cartItems = json_decode($_POST['cart_data'] ?? '[]', true);
        if (!is_array($cartItems)) $cartItems = [];

        if ($name === '' || $email === '') {
            $formErrors[] = 'Please fill in your name and email address.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $formErrors[] = 'Please enter a valid email address.';
        } elseif (!$cartItems) {
            $formErrors[] = 'Your cart is empty — please add at least one gemstone before submitting.';
        } else {
            try {
                $stmt = db()->prepare("INSERT INTO enquiries (full_name, email, phone, country, message) VALUES (?,?,?,?,?)");
                $stmt->execute([$name, $email, $phone, $country, $message]);
                $enquiryId = (int) db()->lastInsertId();

                $itemStmt = db()->prepare("INSERT INTO enquiry_items (enquiry_id, product_id, product_name, weight, shape) VALUES (?,?,?,?,?)");
                $summaryLines = [];

                foreach ($cartItems as $item) {
                    $pid    = isset($item['id']) ? (int) $item['id'] : 0;
                    $pname  = trim((string) ($item['name'] ?? 'Gemstone'));
                    $weight = (isset($item['weight']) && $item['weight'] !== '') ? (float) $item['weight'] : null;
                    $shape  = trim((string) ($item['shape'] ?? ''));

                    // Use authoritative current data if the product still exists
                    if ($pid) {
                        $check = db()->prepare("SELECT name, weight, shape_id FROM products WHERE id=?");
                        $check->execute([$pid]);
                        $row = $check->fetch();
                        if ($row) {
                            $pname  = $row['name'];
                            $weight = $row['weight'];
                            $shape  = lookup_name('gem_shapes', $row['shape_id']);
                        } else {
                            $pid = null;
                        }
                    } else {
                        $pid = null;
                    }

                    $itemStmt->execute([$enquiryId, $pid, $pname, $weight, $shape]);
                    $summaryLines[] = '- ' . $pname . ($weight !== null ? ' (' . $weight . ' ct' . ($shape ? ', ' . $shape : '') . ')' : ($shape ? ' (' . $shape . ')' : ''));
                }

                $siteName   = setting('site_name', 'Ruwanpura Gems');
                $adminEmail = setting('admin_email');

                if ($adminEmail) {
                    $subject = 'New gemstone enquiry — ' . $siteName;
                    $body    = "Name: $name\nEmail: $email\nPhone: $phone\nCountry: $country\n\nGemstones requested:\n"
                             . implode("\n", $summaryLines) . "\n\nMessage:\n$message";
                    @mail($adminEmail, $subject, $body, 'From: ' . $adminEmail);
                }

                $custSubject = 'We received your gemstone enquiry — ' . $siteName;
                $custBody    = "Dear $name,\n\nThank you for your enquiry. We have received your request for the following gemstone(s):\n\n"
                             . implode("\n", $summaryLines)
                             . "\n\nOur team will review your request and get back to you shortly.\n\nBest regards,\n$siteName";
                @mail($email, $custSubject, $custBody, 'From: ' . ($adminEmail ?: 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')));

                $formSuccess   = true;
                $submittedName = $name;
            } catch (PDOException $e) {
                $formErrors[] = 'Sorry, something went wrong submitting your enquiry. Please try again later.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<!-- ================= CART / ENQUIRY ================= -->
<section class="cart-page">
    <div class="container">
        <h1 class="cart-page-title">Your Enquiry Cart</h1>

        <?php if ($formSuccess): ?>
            <div class="flash success" style="margin:24px 0;">
                Thank you<?= $submittedName ? ', ' . e($submittedName) : '' ?> — your enquiry has been received. A confirmation email is on its way, and our team will follow up shortly.
            </div>
            <a href="<?= BASE_URL ?>gemstones.php" class="btn-dark" style="display:inline-block;">Continue Browsing</a>
            <script>try { localStorage.removeItem('ruwanpura_cart'); } catch (e) {}</script>
        <?php else: ?>

            <?php if ($formErrors): ?>
                <div class="flash error" style="margin:0 0 20px;"><?= e(implode(' ', $formErrors)) ?></div>
            <?php endif; ?>

            <div id="cartEmpty" class="cart-empty" style="display:none;">
                Your cart is empty. <a href="<?= BASE_URL ?>gemstones.php">Browse the catalogue</a> to add gemstones.
            </div>

            <div id="cartWrap" class="cart-wrap" style="display:none;">
                <div class="cart-items" id="cartItems"></div>

                <div class="cart-checkout">
                    <h2>Your Details</h2>
                    <p class="cart-checkout-intro">Review your selected gemstones above, then submit your details and our team will follow up with pricing and availability.</p>

                    <form method="post" id="enquiryForm" class="contact-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="enquiry_submit" value="1">
                        <input type="hidden" name="cart_data" id="cartDataInput" value="[]">

                        <div class="form-row-2">
                            <div class="form-field">
                                <label>Full Name</label>
                                <input type="text" name="full_name" required>
                            </div>
                            <div class="form-field">
                                <label>Email Address</label>
                                <input type="email" name="email" required>
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-field">
                                <label>Phone / WhatsApp Number</label>
                                <input type="text" name="phone">
                            </div>
                            <div class="form-field">
                                <label>Country</label>
                                <input type="text" name="country">
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Message / Additional Requirements</label>
                            <textarea name="message" rows="4"></textarea>
                        </div>
                        <button type="submit" class="btn-dark">Submit Enquiry</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
