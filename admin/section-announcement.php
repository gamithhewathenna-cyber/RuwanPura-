<?php
$page_title = 'Home: Announcement Bar';
require_once __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/form-helpers.php';
require_role('home');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf()) {
        $action = $_POST['action'] ?? '';

        if ($action === 'add') {
            $text = trim($_POST['text'] ?? '');
            if ($text !== '') {
                $ord  = db()->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM announcement_cards")->fetchColumn();
                $stmt = db()->prepare("INSERT INTO announcement_cards (text, link, sort_order, is_active) VALUES (?,?,?,1)");
                $stmt->execute([$text, trim($_POST['link'] ?? ''), $ord]);
                set_flash('success', 'Card added.');
            }
        }
        elseif ($action === 'update') {
            $id   = (int) $_POST['id'];
            $stmt = db()->prepare("UPDATE announcement_cards SET text=?, link=?, is_active=? WHERE id=?");
            $stmt->execute([trim($_POST['text'] ?? ''), trim($_POST['link'] ?? ''), isset($_POST['is_active']) ? 1 : 0, $id]);
            set_flash('success', 'Card updated.');
        }
        elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            db()->prepare("DELETE FROM announcement_cards WHERE id=?")->execute([$id]);
            set_flash('success', 'Card deleted.');
        }
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$items = db()->query("SELECT * FROM announcement_cards ORDER BY sort_order, id")->fetchAll();

require_once __DIR__ . '/layout-top.php';
?>

<div class="card">
    <h2>Announcement Bar</h2>
    <p class="card-sub">The black bar shown above the menu on every page. Add two, three, or more cards — they're shown in a row with equal spacing. No cards = the bar is hidden entirely.</p>

    <?php foreach ($items as $a): ?>
        <form method="post" style="border:1px solid var(--line);border-radius:10px;padding:16px;margin-bottom:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
            <div class="form-row">
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Text</label>
                    <input type="text" name="text" class="form-control" value="<?= e($a['text']) ?>">
                </div>
                <div class="form-group" style="margin-bottom:10px;">
                    <label>Link <span class="hint">(optional — e.g. /contact.php)</span></label>
                    <input type="text" name="link" class="form-control" value="<?= e($a['link']) ?>">
                </div>
            </div>
            <div style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;">
                <label style="font-size:13px;"><input type="checkbox" name="is_active" <?= $a['is_active'] ? 'checked' : '' ?>> Show</label>
                <button class="btn btn-sm btn-primary">Save</button>
                <button class="btn btn-sm btn-danger" onclick="this.form.querySelector('[name=action]').value='delete';return confirm('Delete this card?')">Delete</button>
            </div>
        </form>
    <?php endforeach; ?>
    <?php if (!$items): ?>
        <p class="card-sub">No cards yet — add one below.</p>
    <?php endif; ?>

    <div class="section-divider"></div>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <h2 style="font-size:15px;margin-bottom:14px;">Add New Card</h2>
        <div class="form-row">
            <div class="form-group">
                <label>Text</label>
                <input type="text" name="text" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Link <span class="hint">(optional)</span></label>
                <input type="text" name="link" class="form-control" placeholder="/contact.php">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add Card</button>
    </form>
</div>

<?php require_once __DIR__ . '/layout-bottom.php'; ?>
