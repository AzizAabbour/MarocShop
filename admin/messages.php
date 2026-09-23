<?php
/**
 * MarocShop - Admin Contact Inquiries & Support Inbox
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$adminPageTitle = "Customer Contact Messages";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Handle Delete Message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verify_csrf()) {
        $msgId = (int)($_POST['msg_id'] ?? 0);
        if ($msgId > 0) {
            $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = :id");
            $stmt->execute(['id' => $msgId]);
            set_flash('success', "Message #{$msgId} deleted.");
        }
        redirect('admin/messages.php');
    }
}

// Fetch all messages
$messages = $db->query("SELECT * FROM contact_messages ORDER BY id DESC")->fetchAll();
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Inquiry Inbox (<?= count($messages) ?>)</h2>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Sender</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr><td colspan="5" class="text-center text-muted" style="padding:40px;">Your inbox is empty. No messages yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                        <tr>
                            <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($m['created_at'])) ?></small></td>
                            <td>
                                <strong><?= e($m['name']) ?></strong><br>
                                <a href="mailto:<?= e($m['email']) ?>" style="color:var(--color-gold); font-size:0.85rem;"><?= e($m['email']) ?></a>
                            </td>
                            <td>
                                <strong><?= e($m['subject']) ?></strong>
                            </td>
                            <td style="max-width:380px;">
                                <div style="font-size:0.88rem; color:#444; line-height:1.5;">
                                    <?= nl2br(e($m['message'])) ?>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <div class="action-buttons" style="justify-content:flex-end;">
                                    <a href="mailto:<?= e($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn-icon" title="Reply via Email">✉️</a>
                                    <form action="<?= url('admin/messages.php') ?>" method="POST" class="confirm-delete-form" data-item="this inquiry" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                        <button type="submit" class="btn-icon delete" title="Delete Message">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
