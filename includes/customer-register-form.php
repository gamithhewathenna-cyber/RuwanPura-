<?php
/**
 * Shared customer registration field-set, included by checkout.php's
 * inline "Register" tab and by account/register.php's standalone page.
 * Expects an optional $old array (posted values to re-fill on validation error).
 */
$old = $old ?? [];
?>
<div class="form-row-2">
    <div class="form-field">
        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required>
    </div>
    <div class="form-field">
        <label>Email Address</label>
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required>
    </div>
</div>
<div class="form-row-2">
    <div class="form-field">
        <label>Phone / WhatsApp Number</label>
        <input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>">
    </div>
    <div class="form-field">
        <label>Country</label>
        <input type="text" name="country" value="<?= e($old['country'] ?? '') ?>">
    </div>
</div>

<h4 style="margin:18px 0 10px;font-size:15px;">Billing Address</h4>
<div class="form-field">
    <label>Address Line 1</label>
    <input type="text" name="billing_address_line1" value="<?= e($old['billing_address_line1'] ?? '') ?>" required>
</div>
<div class="form-field">
    <label>Address Line 2 <span class="hint">(optional)</span></label>
    <input type="text" name="billing_address_line2" value="<?= e($old['billing_address_line2'] ?? '') ?>">
</div>
<div class="form-row-2">
    <div class="form-field">
        <label>City</label>
        <input type="text" name="billing_city" value="<?= e($old['billing_city'] ?? '') ?>" required>
    </div>
    <div class="form-field">
        <label>State / Province</label>
        <input type="text" name="billing_state" value="<?= e($old['billing_state'] ?? '') ?>">
    </div>
</div>
<div class="form-row-2">
    <div class="form-field">
        <label>Postal Code</label>
        <input type="text" name="billing_postal_code" value="<?= e($old['billing_postal_code'] ?? '') ?>">
    </div>
    <div class="form-field">
        <label>Country</label>
        <input type="text" name="billing_country" value="<?= e($old['billing_country'] ?? '') ?>" required>
    </div>
</div>

<h4 style="margin:18px 0 10px;font-size:15px;">Shipping Address</h4>
<label class="filter-check" style="margin-bottom:14px;display:flex;align-items:center;gap:8px;">
    <input type="checkbox" name="shipping_same_as_billing" value="1" id="shipSameChk" <?= !isset($old['shipping_same_as_billing']) || $old['shipping_same_as_billing'] ? 'checked' : '' ?>>
    Same as billing address
</label>
<div id="shippingAddressFields" style="<?= (!isset($old['shipping_same_as_billing']) || $old['shipping_same_as_billing']) ? 'display:none;' : '' ?>">
    <div class="form-field">
        <label>Address Line 1</label>
        <input type="text" name="shipping_address_line1" value="<?= e($old['shipping_address_line1'] ?? '') ?>">
    </div>
    <div class="form-field">
        <label>Address Line 2 <span class="hint">(optional)</span></label>
        <input type="text" name="shipping_address_line2" value="<?= e($old['shipping_address_line2'] ?? '') ?>">
    </div>
    <div class="form-row-2">
        <div class="form-field">
            <label>City</label>
            <input type="text" name="shipping_city" value="<?= e($old['shipping_city'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>State / Province</label>
            <input type="text" name="shipping_state" value="<?= e($old['shipping_state'] ?? '') ?>">
        </div>
    </div>
    <div class="form-row-2">
        <div class="form-field">
            <label>Postal Code</label>
            <input type="text" name="shipping_postal_code" value="<?= e($old['shipping_postal_code'] ?? '') ?>">
        </div>
        <div class="form-field">
            <label>Country</label>
            <input type="text" name="shipping_country" value="<?= e($old['shipping_country'] ?? '') ?>">
        </div>
    </div>
</div>

<?php if (!empty($showPassword)): ?>
<h4 style="margin:18px 0 10px;font-size:15px;">Account Password</h4>
<div class="form-row-2">
    <div class="form-field">
        <label>Password</label>
        <input type="password" name="password" minlength="6" required autocomplete="new-password">
    </div>
    <div class="form-field">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" minlength="6" required autocomplete="new-password">
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    var chk = document.getElementById('shipSameChk');
    var fields = document.getElementById('shippingAddressFields');
    if (chk && fields) {
        chk.addEventListener('change', function () {
            fields.style.display = chk.checked ? 'none' : 'block';
        });
    }
})();
</script>
