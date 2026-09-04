<?php
/**
 * Customer authentication layer (mirrors admin/auth.php, customer-scoped).
 */
require_once __DIR__ . '/functions.php';

/* Require a logged-in customer (include at top of protected account/checkout pages) */
function require_customer()
{
    if (empty($_SESSION['customer_id'])) {
        header('Location: ' . BASE_URL . 'account/login.php');
        exit;
    }
}

function attempt_customer_login($email, $password)
{
    $stmt = db()->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $customer = $stmt->fetch();
    if ($customer && password_verify($password, $customer['password_hash'])) {
        $_SESSION['customer_id']    = $customer['id'];
        $_SESSION['customer_name']  = $customer['full_name'];
        $_SESSION['customer_email'] = $customer['email'];
        if (password_needs_rehash($customer['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            db()->prepare("UPDATE customers SET password_hash = ? WHERE id = ?")->execute([$newHash, $customer['id']]);
        }
        return true;
    }
    return false;
}

function logout_customer()
{
    unset($_SESSION['customer_id'], $_SESSION['customer_name'], $_SESSION['customer_email']);
    session_regenerate_id(true);
}

function current_customer_id()
{
    return $_SESSION['customer_id'] ?? null;
}

function current_customer()
{
    static $cache = null;
    $id = current_customer_id();
    if (!$id) return null;
    if ($cache !== null && (int) $cache['id'] === (int) $id) return $cache;
    $stmt = db()->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$id]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

/* Register a new customer account. Returns ['id' => int] on success,
   or ['errors' => [...]] on validation failure. */
function register_customer($data)
{
    $fullName = trim($data['full_name'] ?? '');
    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $errors   = [];

    if ($fullName === '') $errors[] = 'Please enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        $chk = db()->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
        $chk->execute([$email]);
        if ((int) $chk->fetchColumn() > 0) {
            $errors[] = 'An account with that email already exists. Please log in instead.';
        }
    }

    if ($errors) return ['errors' => $errors];

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare("INSERT INTO customers (full_name, email, password_hash, phone, country,
        billing_address_line1, billing_address_line2, billing_city, billing_state, billing_postal_code, billing_country,
        shipping_same_as_billing, shipping_address_line1, shipping_address_line2, shipping_city, shipping_state, shipping_postal_code, shipping_country)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute(customer_profile_params($data, $fullName, $email, $hash));

    return ['id' => (int) db()->lastInsertId()];
}

/* Update an existing customer's profile + address fields (not password/email). */
function save_customer_profile($customerId, $data)
{
    $row = db()->prepare("SELECT full_name FROM customers WHERE id = ?");
    $row->execute([$customerId]);
    $existing = $row->fetch();
    if (!$existing) return false;

    $fullName      = trim($data['full_name'] ?? $existing['full_name']);
    $sameAsBilling = !empty($data['shipping_same_as_billing']) ? 1 : 0;

    $stmt = db()->prepare("UPDATE customers SET full_name=?, phone=?, country=?,
        billing_address_line1=?, billing_address_line2=?, billing_city=?, billing_state=?, billing_postal_code=?, billing_country=?,
        shipping_same_as_billing=?, shipping_address_line1=?, shipping_address_line2=?, shipping_city=?, shipping_state=?, shipping_postal_code=?, shipping_country=?
        WHERE id=?");
    $stmt->execute([
        $fullName,
        trim($data['phone'] ?? ''),
        trim($data['country'] ?? ''),
        trim($data['billing_address_line1'] ?? ''),
        trim($data['billing_address_line2'] ?? ''),
        trim($data['billing_city'] ?? ''),
        trim($data['billing_state'] ?? ''),
        trim($data['billing_postal_code'] ?? ''),
        trim($data['billing_country'] ?? ''),
        $sameAsBilling,
        $sameAsBilling ? trim($data['billing_address_line1'] ?? '') : trim($data['shipping_address_line1'] ?? ''),
        $sameAsBilling ? trim($data['billing_address_line2'] ?? '') : trim($data['shipping_address_line2'] ?? ''),
        $sameAsBilling ? trim($data['billing_city'] ?? '') : trim($data['shipping_city'] ?? ''),
        $sameAsBilling ? trim($data['billing_state'] ?? '') : trim($data['shipping_state'] ?? ''),
        $sameAsBilling ? trim($data['billing_postal_code'] ?? '') : trim($data['shipping_postal_code'] ?? ''),
        $sameAsBilling ? trim($data['billing_country'] ?? '') : trim($data['shipping_country'] ?? ''),
        $customerId,
    ]);

    $_SESSION['customer_name'] = $fullName;
    return true;
}

/* Shared field-extraction for register_customer()/save_customer_profile() —
   builds the ordered param list (full_name, email, password_hash, phone, country, billing..., shipping...). */
function customer_profile_params($data, $fullName, $email, $passwordHash)
{
    $sameAsBilling = !empty($data['shipping_same_as_billing']) ? 1 : 0;

    return [
        $fullName,
        $email,
        $passwordHash,
        trim($data['phone'] ?? ''),
        trim($data['country'] ?? ''),
        trim($data['billing_address_line1'] ?? ''),
        trim($data['billing_address_line2'] ?? ''),
        trim($data['billing_city'] ?? ''),
        trim($data['billing_state'] ?? ''),
        trim($data['billing_postal_code'] ?? ''),
        trim($data['billing_country'] ?? ''),
        $sameAsBilling,
        $sameAsBilling ? trim($data['billing_address_line1'] ?? '') : trim($data['shipping_address_line1'] ?? ''),
        $sameAsBilling ? trim($data['billing_address_line2'] ?? '') : trim($data['shipping_address_line2'] ?? ''),
        $sameAsBilling ? trim($data['billing_city'] ?? '') : trim($data['shipping_city'] ?? ''),
        $sameAsBilling ? trim($data['billing_state'] ?? '') : trim($data['shipping_state'] ?? ''),
        $sameAsBilling ? trim($data['billing_postal_code'] ?? '') : trim($data['shipping_postal_code'] ?? ''),
        $sameAsBilling ? trim($data['billing_country'] ?? '') : trim($data['shipping_country'] ?? ''),
    ];
}
