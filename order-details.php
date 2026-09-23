<?php
/**
 * MarocShop - Customer Specific Order Details
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_login();
$user = current_user();
$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    redirect('orders.php');
}

$db = get_db();

// Verify that the order belongs to the logged-in customer (or if admin)
if (is_admin()) {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $orderId]);
} else {
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = :id AND user_id = :user_id LIMIT 1");
    $stmt->execute(['id' => $orderId, 'user_id' => $user['id']]);
}

$order = $stmt->fetch();
if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('orders.php');
}

// Fetch Items
$itemStmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.image AS product_image, c.name AS category_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE oi.order_id = :order_id");
$itemStmt->execute(['order_id' => $orderId]);
$orderItems = $itemStmt->fetchAll();

$pageTitle = "Order #" . $orderId . " Invoice";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container" style="max-width: 860px;">
        <!-- Header Actions -->
        <div class="flex-between" style="margin-bottom: 25px; flex-wrap:wrap; gap:12px;">
            <a href="<?= url('orders.php') ?>" class="btn btn-outline btn-sm">&larr; Back to Orders</a>
            <button onclick="window.print();" class="btn btn-gold btn-sm">🖨️ Print Invoice</button>
        </div>

        <!-- Invoice Card -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--border-radius-lg); padding:40px; box-shadow:var(--shadow-subtle);">
            <!-- Invoice Header -->
            <div class="flex-between" style="padding-bottom:25px; border-bottom:2px solid var(--border-color); margin-bottom:25px; flex-wrap:wrap; gap:20px;">
                <div>
                    <div class="brand-logo" style="font-size:1.8rem; margin-bottom:4px;">
                        <span class="logo-badge">M</span>arocShop
                    </div>
                    <div style="font-size:0.85rem; color:var(--color-text-muted);">Authentic Moroccan Treasures</div>
                </div>
                <div style="text-align:right;">
                    <h2 style="font-size:1.4rem; color:var(--bg-dark);">INVOICE #<?= $order['id'] ?></h2>
                    <div style="font-size:0.88rem; color:var(--color-text-muted); margin-top:4px;">
                        Date: <?= date('F d, Y', strtotime($order['created_at'])) ?>
                    </div>
                    <div style="margin-top:6px;">
                        <span class="badge badge-status badge-<?= e($order['status']) ?>">
                            <?= ucfirst(e($order['status'])) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Customer & Delivery Info -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-bottom:30px; background:var(--bg-light-gray); padding:20px; border-radius:var(--border-radius-md);">
                <div>
                    <div style="font-size:0.8rem; text-transform:uppercase; font-weight:700; color:var(--color-text-muted); margin-bottom:6px;">Billed & Shipped To:</div>
                    <strong style="font-size:1.05rem;"><?= e($order['customer_name']) ?></strong>
                    <div style="font-size:0.9rem; color:var(--color-text-muted); margin-top:4px; line-height:1.5;">
                        📞 <?= e($order['phone']) ?><br>
                        <?= nl2br(e($order['address'])) ?><br>
                        <strong><?= e($order['city']) ?>, Morocco</strong>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.8rem; text-transform:uppercase; font-weight:700; color:var(--color-text-muted); margin-bottom:6px;">Payment Information:</div>
                    <div style="font-size:0.9rem; line-height:1.6;">
                        <strong>Method:</strong> Cash on Delivery (COD)<br>
                        <strong>Shipping:</strong> Domestic Express Morocco (24-48h)<br>
                        <strong>Order Status:</strong> <?= ucfirst(e($order['status'])) ?>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="cart-table" style="margin-bottom:25px;">
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Unit Price</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotalCalc = 0;
                    foreach ($orderItems as $it): 
                        $sub = $it['price'] * $it['quantity'];
                        $subtotalCalc += $sub;
                    ?>
                        <tr>
                            <td>
                                <div class="cart-item-flex">
                                    <img src="<?= get_image_url($it['product_image']) ?>" alt="<?= e($it['product_name']) ?>" class="cart-item-img" style="width:48px; height:48px;">
                                    <div>
                                        <div class="cart-item-title"><?= e($it['product_name']) ?></div>
                                        <small class="text-muted"><?= e($it['category_name']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= format_price($it['price']) ?></td>
                            <td style="text-align:center;">x<?= $it['quantity'] ?></td>
                            <td style="text-align:right;"><strong><?= format_price($sub) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Financials -->
            <div style="max-width:320px; margin-left:auto;">
                <div class="summary-row">
                    <span class="text-muted">Items Subtotal</span>
                    <strong><?= format_price($subtotalCalc) ?></strong>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Delivery</span>
                    <span><?= ($order['total'] > $subtotalCalc) ? format_price($order['total'] - $subtotalCalc) : 'FREE' ?></span>
                </div>
                <div class="summary-row total">
                    <span>Total Paid/Due</span>
                    <span style="color:var(--color-gold);"><?= format_price($order['total']) ?></span>
                </div>
            </div>

            <div style="margin-top:40px; padding-top:20px; border-top:1px solid var(--border-color); text-align:center; font-size:0.85rem; color:var(--color-text-muted);">
                Thank you for your trust in <strong>MarocShop</strong>. For assistance with your delivery, contact us at <code>support@marocshop.ma</code>.
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
