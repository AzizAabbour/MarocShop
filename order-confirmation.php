<?php
/**
 * MarocShop - Order Confirmation & Invoice Summary
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId <= 0) {
    redirect('shop.php');
}

$db = get_db();

// Fetch Order
$stmt = $db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $orderId]);
$order = $stmt->fetch();

if (!$order) {
    set_flash('error', 'Order not found.');
    redirect('shop.php');
}

// Fetch Order Items
$itemStmt = $db->prepare("SELECT oi.*, p.name AS product_name, p.image AS product_image, c.name AS category_name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE oi.order_id = :order_id");
$itemStmt->execute(['order_id' => $orderId]);
$orderItems = $itemStmt->fetchAll();

$pageTitle = "Order Confirmation #" . $orderId;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container" style="max-width: 850px;">
        <!-- Success Confirmation Card -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--border-radius-lg); padding:40px; box-shadow:var(--shadow-card); text-align:center; margin-bottom:30px;">
            <div style="width:70px; height:70px; border-radius:50%; background:var(--color-success-bg); color:var(--color-success); font-size:2.2rem; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                ✓
            </div>

            <span class="badge badge-gold" style="margin-bottom:12px;">Order Placed Successfully</span>
            <h1 style="font-size:2.2rem; margin-bottom:10px;">Thank You, <?= e($order['customer_name']) ?>!</h1>
            <p class="text-muted" style="font-size:1.05rem; max-width:550px; margin:0 auto 25px;">
                Your order <strong style="color:var(--bg-dark);">#<?= $order['id'] ?></strong> has been received and is currently being carefully packed by our Moroccan artisans.
            </p>

            <div style="display:inline-flex; gap:15px; flex-wrap:wrap; justify-content:center;">
                <a href="<?= url('shop.php') ?>" class="btn btn-outline btn-sm">&larr; Continue Shopping</a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= url('order-details.php?id=' . $order['id']) ?>" class="btn btn-gold btn-sm">View Order History</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice / Order Details Card -->
        <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:var(--border-radius-lg); padding:35px; box-shadow:var(--shadow-subtle);">
            <div class="flex-between" style="padding-bottom:20px; border-bottom:1px solid var(--border-color); margin-bottom:25px; flex-wrap:wrap; gap:15px;">
                <div>
                    <h2 style="font-size:1.3rem;">Order Receipt</h2>
                    <span class="text-muted" style="font-size:0.88rem;">Placed on <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></span>
                </div>
                <div>
                    <span class="badge badge-status badge-<?= e($order['status']) ?>">
                        Status: <?= ucfirst(e($order['status'])) ?>
                    </span>
                </div>
            </div>

            <!-- Customer & Delivery Grid -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:25px; margin-bottom:30px; background:var(--bg-light-gray); padding:20px; border-radius:var(--border-radius-md);">
                <div>
                    <h4 style="font-size:0.95rem; margin-bottom:8px; color:var(--bg-dark);">Customer Details</h4>
                    <div style="font-size:0.9rem; line-height:1.6;">
                        <strong><?= e($order['customer_name']) ?></strong><br>
                        📞 <?= e($order['phone']) ?>
                    </div>
                </div>
                <div>
                    <h4 style="font-size:0.95rem; margin-bottom:8px; color:var(--bg-dark);">Delivery Address</h4>
                    <div style="font-size:0.9rem; line-height:1.6;">
                        <?= nl2br(e($order['address'])) ?><br>
                        <strong><?= e($order['city']) ?>, Morocco</strong>
                    </div>
                </div>
            </div>

            <!-- Ordered Items List -->
            <h3 style="font-size:1.15rem; margin-bottom:15px;">Items in Your Package</h3>
            <table class="cart-table" style="margin-bottom:25px;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th style="text-align:center;">Qty</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotalSum = 0;
                    foreach ($orderItems as $item): 
                        $itemSubtotal = $item['price'] * $item['quantity'];
                        $subtotalSum += $itemSubtotal;
                    ?>
                        <tr>
                            <td>
                                <div class="cart-item-flex">
                                    <img src="<?= get_image_url($item['product_image']) ?>" alt="<?= e($item['product_name']) ?>" class="cart-item-img" style="width:50px; height:50px;">
                                    <div>
                                        <div class="cart-item-title"><?= e($item['product_name']) ?></div>
                                        <small class="text-muted"><?= e($item['category_name']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= format_price($item['price']) ?></td>
                            <td style="text-align:center;"><strong>x<?= $item['quantity'] ?></strong></td>
                            <td style="text-align:right;"><strong><?= format_price($itemSubtotal) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Financial Breakdown -->
            <div style="max-width:350px; margin-left:auto;">
                <div class="summary-row">
                    <span class="text-muted">Items Subtotal</span>
                    <strong><?= format_price($subtotalSum) ?></strong>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Payment Method</span>
                    <strong>Cash on Delivery</strong>
                </div>
                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span style="color:var(--color-gold);"><?= format_price($order['total']) ?></span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
