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
        $_SESSION['admin_role']  = $admin['role'] ?? 'admin'; // fallback for pre-migration accounts
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
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email'], $_SESSION['admin_role']);
    session_regenerate_id(true);
}

function current_admin_id()
{
    return $_SESSION['admin_id'] ?? null;
}

/* ------------------------------------------------------------------ */
/*  Roles & permissions                                                */
/* ------------------------------------------------------------------ */
function admin_roles()
{
    return [
        'admin'    => 'Administrator (Full Access)',
        'content'  => 'Content Uploader',
        'gemstone' => 'Gemstone Uploader',
        'blog'     => 'Blog Manager',
    ];
}

function current_admin_role()
{
    return $_SESSION['admin_role'] ?? 'admin';
}

/* Which admin sections a role is allowed into. 'admin' always has full access. */
function role_can($section)
{
    if ($section === null) return true; // unrestricted pages (dashboard, my account)
    $role = current_admin_role();
    if ($role === 'admin') return true;

    $map = [
        'content'  => ['home', 'about', 'contact'],
        'gemstone' => ['gemstones'],
        'blog'     => ['blog'],
    ];
    return in_array($section, $map[$role] ?? [], true);
}

/* Call after require_admin() to gate a section by role; redirects home if not permitted */
function require_role($section)
{
    if (!role_can($section)) {
        set_flash('error', "You don't have permission to access that section.");
        header('Location: ' . BASE_URL . 'admin/index.php');
        exit;
    }
}
