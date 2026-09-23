<?php
/**
 * MarocShop - Admin User & Customer Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$adminPageTitle = "Registered Users & Customers";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verify_csrf()) {
        $delId = (int)($_POST['user_id'] ?? 0);
        if ($delId === (int)$_SESSION['user_id']) {
            set_flash('error', "You cannot delete your own logged-in admin account!");
        } elseif ($delId > 0) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $delId]);
            set_flash('success', "User #{$delId} removed successfully.");
        }
        redirect('admin/users.php');
    }
}

// Fetch all users with order count
$users = $db->query("SELECT u.*, COUNT(o.id) AS order_count FROM users u LEFT JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.id DESC")->fetchAll();
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Registered Accounts (<?= count($users) ?>)</h2>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Role</th>
                    <th>Total Orders</th>
                    <th>Joined Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="9" class="text-center text-muted" style="padding:30px;">No user accounts found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?= $u['id'] ?></strong></td>
                            <td><strong><?= e($u['name']) ?></strong></td>
                            <td><?= e($u['email']) ?></td>
                            <td><?= !empty($u['phone']) ? e($u['phone']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= !empty($u['city']) ? e($u['city']) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <span class="badge <?= ($u['role'] === 'admin') ? 'badge-gold' : 'badge-pending' ?>">
                                    <?= ucfirst(e($u['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= $u['order_count'] ?> order(s)</strong>
                            </td>
                            <td>
                                <small class="text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></small>
                            </td>
                            <td style="text-align:right;">
                                <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <form action="<?= url('admin/users.php') ?>" method="POST" class="confirm-delete-form" data-item="<?= e($u['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn-icon delete" title="Delete User">🗑️</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">Current Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
