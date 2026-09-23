<?php
/**
 * MarocShop - Admin Order Details & Status Changer
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    redirect('admin/orders.php');
}

$db = get_db();

// Handle Status Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (verify_csrf()) {
        $newStatus = $_POST['status'] ?? 'pending';
        $allowed = ['pending', 'processing', 'completed', 'cancelled'];
        if (in_array($newStatus, $allowed)) {
            $uStmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $uStmt->execute(['status' => $newStatus, 'id' => $orderId]);
            set_flash('success', "Order #{$orderId} status successfully updated to " . ucfirst($newStatus) . ".");
            redirect("admin/order-details.php?id={$orderId}");
        }
    }
}

// Fetch Order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('admin/orders.php');
}

// Fetch Items
$itemStmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.image AS product_image, c.name AS category_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE oi.order_id = :order_id");
$itemStmt->execute(['order_id' => $orderId]);
$orderItems = $itemStmt->fetchAll();

$adminPageTitle = "Order #" . $orderId . " Details";
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="flex-between" style="margin-bottom: 25px; flex-wrap:wrap; gap:12px;">
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-outline btn-sm">&larr; Back to Orders</a>
    
    <div style="display:flex; gap:10px;">
        <button onclick="window.print();" class="btn btn-outline btn-sm">🖨️ Print Packing Slip</button>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1.6fr 1fr; gap:30px;" class="admin-dashboard-grid">
    <!-- Ordered Products -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Order Items (<?= count($orderItems) ?> items)</h2>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit Price</th>
                        <th style="text-align:center;">Quantity</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $calcSubtotal = 0;
                    foreach ($orderItems as $item): 
                        $sub = $item['price'] * $item['quantity'];
                        $calcSubtotal += $sub;
                    ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <img src="<?= get_image_url($item['product_image']) ?>" alt="" class="table-img">
                                    <div>
                                        <strong><?= e($item['product_name']) ?></strong><br>
                                        <small class="text-muted"><?= e($item['category_name']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= format_price($item['price']) ?></td>
                            <td style="text-align:center;"><strong>x<?= $item['quantity'] ?></strong></td>
                            <td style="text-align:right;"><strong><?= format_price($sub) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="padding: 24px; border-top:1px solid var(--admin-border); max-width:320px; margin-left:auto;">
            <div class="summary-row">
                <span class="text-muted">Subtotal</span>
                <strong><?= format_price($calcSubtotal) ?></strong>
            </div>
            <div class="summary-row">
                <span class="text-muted">Shipping</span>
                <span><?= ($order['total'] > $calcSubtotal) ? format_price($order['total'] - $calcSubtotal) : 'FREE' ?></span>
            </div>
            <div class="summary-row total">
                <span>Total Amount</span>
                <span style="color:var(--color-gold);"><?= format_price($order['total']) ?></span>
            </div>
        </div>
    </div>

    <!-- Status & Customer Sidebar -->
    <div style="display:flex; flex-direction:column; gap:25px;">
        <!-- Order Status Updater -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Order Status</h2>
                <span class="badge badge-status badge-<?= e($order['status']) ?>">
                    <?= ucfirst(e($order['status'])) ?>
                </span>
            </div>
            <div style="padding: 20px;">
                <form action="<?= url('admin/order-details.php?id=' . $orderId) ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="update_status" value="1">

                    <label class="form-label" for="statusSelect">Update Order Fulfillment Status:</label>
                    <select name="status" id="statusSelect" class="form-control" style="margin-bottom:15px;">
                        <option value="pending" <?= ($order['status'] === 'pending') ? 'selected' : '' ?>>⏳ Pending</option>
                        <option value="processing" <?= ($order['status'] === 'processing') ? 'selected' : '' ?>>⚙️ Processing (In Packing)</option>
                        <option value="completed" <?= ($order['status'] === 'completed') ? 'selected' : '' ?>>✓ Completed (Delivered)</option>
                        <option value="cancelled" <?= ($order['status'] === 'cancelled') ? 'selected' : '' ?>>✕ Cancelled</option>
                    </select>

                    <button type="submit" class="btn btn-gold btn-block">Save New Status</button>
                </form>
            </div>
        </div>

        <!-- Customer & Delivery Details -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Customer & Shipping</h2>
            </div>
            <div style="padding: 20px; font-size:0.92rem; line-height:1.7;">
                <div style="margin-bottom:12px;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--admin-text-muted); text-transform:uppercase;">Recipient:</div>
                    <strong><?= e($order['customer_name']) ?></strong>
                </div>

                <div style="margin-bottom:12px;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--admin-text-muted); text-transform:uppercase;">Telephone:</div>
                    <a href="tel:<?= e($order['phone']) ?>" style="color:var(--color-gold); font-weight:700;">📞 <?= e($order['phone']) ?></a>
                </div>

                <div style="margin-bottom:12px;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--admin-text-muted); text-transform:uppercase;">Destination City:</div>
                    <strong><?= e($order['city']) ?>, Morocco</strong>
                </div>

                <div>
                    <div style="font-size:0.8rem; font-weight:700; color:var(--admin-text-muted); text-transform:uppercase;">Full Delivery Address:</div>
                    <div style="background:var(--admin-bg); padding:10px; border-radius:6px; margin-top:4px;">
                        <?= nl2br(e($order['address'])) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
