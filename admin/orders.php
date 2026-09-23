<?php
/**
 * MarocShop - Admin Order Management
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$adminPageTitle = "Order Management";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Handle Status Change from list
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf()) {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? 'pending';
        $allowed = ['pending', 'processing', 'completed', 'cancelled'];

        if ($orderId > 0 && in_array($newStatus, $allowed)) {
            $uStmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $uStmt->execute(['status' => $newStatus, 'id' => $orderId]);
            set_flash('success', "Order #{$orderId} status changed to " . ucfirst($newStatus) . ".");
            redirect('admin/orders.php' . (!empty($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
        }
    }
}

// Status Filter Tab
$statusFilter = $_GET['status'] ?? '';
$where = ["1=1"];
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'processing', 'completed', 'cancelled'])) {
    $where[] = "status = :status";
    $params[':status'] = $statusFilter;
}

$whereSql = implode(" AND ", $where);

$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id WHERE $whereSql GROUP BY o.id ORDER BY o.id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Count per status for tabs
$counts = [
    'all'        => (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending'    => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'processing' => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn(),
    'completed'  => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn(),
    'cancelled'  => (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn(),
];
?>

<!-- Order Status Tabs -->
<div style="display:flex; gap:10px; margin-bottom:25px; flex-wrap:wrap;">
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>">
        All Orders (<?= $counts['all'] ?>)
    </a>
    <a href="<?= url('admin/orders.php?status=pending') ?>" class="btn btn-sm <?= ($statusFilter === 'pending') ? 'btn-gold' : 'btn-outline' ?>">
        ⏳ Pending (<?= $counts['pending'] ?>)
    </a>
    <a href="<?= url('admin/orders.php?status=processing') ?>" class="btn btn-sm <?= ($statusFilter === 'processing') ? 'btn-gold' : 'btn-outline' ?>">
        ⚙️ Processing (<?= $counts['processing'] ?>)
    </a>
    <a href="<?= url('admin/orders.php?status=completed') ?>" class="btn btn-sm <?= ($statusFilter === 'completed') ? 'btn-gold' : 'btn-outline' ?>">
        ✓ Completed (<?= $counts['completed'] ?>)
    </a>
    <a href="<?= url('admin/orders.php?status=cancelled') ?>" class="btn btn-sm <?= ($statusFilter === 'cancelled') ? 'btn-gold' : 'btn-outline' ?>">
        ✕ Cancelled (<?= $counts['cancelled'] ?>)
    </a>
</div>

<!-- Orders Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">Customer Orders List (<?= count($orders) ?>)</h2>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No orders found in this category.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><strong>#<?= $o['id'] ?></strong></td>
                            <td><small class="text-muted"><?= date('M d, Y H:i', strtotime($o['created_at'])) ?></small></td>
                            <td><strong><?= e($o['customer_name']) ?></strong></td>
                            <td><?= e($o['phone']) ?></td>
                            <td><?= e($o['city']) ?></td>
                            <td><strong style="color:var(--color-gold);"><?= format_price($o['total']) ?></strong></td>
                            <td>
                                <!-- Inline Quick Status Change Form -->
                                <form action="<?= url('admin/orders.php' . (!empty($statusFilter) ? '?status=' . urlencode($statusFilter) : '')) ?>" method="POST" style="margin:0;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" onchange="this.form.submit();" style="padding:4px 8px; border-radius:4px; font-size:0.8rem; font-weight:600; border:1px solid var(--admin-border); cursor:pointer;">
                                        <option value="pending" <?= ($o['status'] === 'pending') ? 'selected' : '' ?>>⏳ Pending</option>
                                        <option value="processing" <?= ($o['status'] === 'processing') ? 'selected' : '' ?>>⚙️ Processing</option>
                                        <option value="completed" <?= ($o['status'] === 'completed') ? 'selected' : '' ?>>✓ Completed</option>
                                        <option value="cancelled" <?= ($o['status'] === 'cancelled') ? 'selected' : '' ?>>✕ Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= url('admin/order-details.php?id=' . $o['id']) ?>" class="btn btn-outline btn-sm">
                                    Details &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
