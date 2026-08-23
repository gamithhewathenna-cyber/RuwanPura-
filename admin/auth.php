<?php
/**
 * Admin authentication layer
 */
require_once __DIR__ . '/../includes/functions.php';

/* Require login for any admin page (include at top of protected pages) */
function require_admin()
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

/* Attempt login */
function attempt_login($email, $password)
{
    $stmt = db()->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        // rehash if needed
        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $up = db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
            $up->execute([$newHash, $admin['id']]);
        }
        return true;
    }
    return false;
}

function logout_admin()
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
    session_regenerate_id(true);
}

function current_admin_id()
{
    return $_SESSION['admin_id'] ?? null;
}
