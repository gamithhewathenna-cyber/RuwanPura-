<?php
$page_title = 'Admin Users';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('users');

$roles = admin_roles();
$myId  = (int) current_admin_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $name     = trim($_POST['name'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role     = $_POST['role'] ?? 'content';
            if (!array_key_exists($role, $roles)) $role = 'content';

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                set_flash('error', 'Please enter a valid name and email address.');
            } elseif (strlen($password) < 6) {
                set_flash('error', 'Password must be at least 6 characters.');
            } else {
                $chk = db()->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
                $chk->execute([$email]);
                if ((int) $chk->fetchColumn() > 0) {
                    set_flash('error', 'That email is already in use by another admin user.');
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    db()->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?,?,?,?)")
                        ->execute([$name, $email, $hash, $role]);
                    set_flash('success', 'Admin user added.');
                }
            }
        }
        elseif ($action === 'update_role') {
            $id   = (int) ($_POST['id'] ?? 0);
            $role = $_POST['role'] ?? 'content';
            if (!array_key_exists($role, $roles)) $role = 'content';

            if ($id === $myId) {
                set_flash('error', "You can't change your own role.");
            } else {
                db()->prepare("UPDATE admins SET role = ? WHERE id = ?")->execute([$role, $id]);
                set_flash('success', 'Role updated.');
            }
        }
        elseif ($action === 'reset_password') {
            $id  = (int) ($_POST['id'] ?? 0);
            $new = $_POST['new_password'] ?? '';
            if (strlen($new) < 6) {
                set_flash('error', 'New password must be at least 6 characters.');
            } else {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                db()->prepare("UPDATE admins SET password_hash = ? WHERE id = ?")->execute([$hash, $id]);
                set_flash('success', 'Password reset.');
            }
        }
        elseif ($action === 'delete') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id === $myId) {
                set_flash('error', "You can't delete your own account.");
            } else {
                db()->prepare("DELETE FROM admins WHERE id = ?")->execute([$id]);
                set_flash('success', 'Admin user removed.');
            }
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$admins = db()->query("SELECT * FROM admins ORDER BY id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <div class="card-head-row">
        <h2>Admin Users</h2>
    </div>
    <p class="card-sub">
        <strong>Administrator</strong> has full access to everything, including this page.
        <strong>Content Uploader</strong> can only manage Home Page, About Us, and Contact Us content.
        <strong>Gemstone Uploader</strong> can only manage the Gemstones catalogue (products, categories, shapes, treatments, origins, enquiries).
    </p>

    <table class="items-table">
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th style="text-align:right">Actions</th></tr></thead>
        <tbody>
        <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= e($a['name']) ?><?php if ((int)$a['id'] === $myId): ?> <span class="badge">You</span><?php endif; ?></td>
                <td><?= e($a['email']) ?></td>
                <td>
                    <?php if ((int) $a['id'] === $myId): ?>
                        <span class="badge on"><?= e($roles[$a['role']] ?? $a['role']) ?></span>
                    <?php else: ?>
                        <form method="post" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <select name="role" class="form-control" style="padding:6px 10px;font-size:12.5px;width:auto;" onchange="this.form.submit()">
                                <?php foreach ($roles as $key => $label): ?>
                                    <option value="<?= e($key) ?>" <?= $a['role'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    <?php endif; ?>
                </td>
                <td class="row-actions">
                    <details style="display:inline-block;">
                        <summary class="btn btn-sm" style="cursor:pointer;display:inline-flex;">Reset Password</summary>
                        <form method="post" style="margin-top:10px;min-width:220px;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <input type="password" name="new_password" class="form-control" placeholder="New password" minlength="6" required autocomplete="new-password" style="margin-bottom:8px;">
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Reset the password for <?= e(addslashes($a['name'])) ?>? They will need to use the new password to log in.')">Set Password</button>
                        </form>
                    </details>
                    <?php if ((int) $a['id'] !== $myId): ?>
                        <form method="post" style="display:inline;" onsubmit="return confirm('Remove this admin user? They will no longer be able to log in.')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="section-divider"></div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Admin User</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Login Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <?php foreach ($roles as $key => $label): ?>
                        <option value="<?= e($key) ?>" <?= $key === 'content' ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Admin User</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
