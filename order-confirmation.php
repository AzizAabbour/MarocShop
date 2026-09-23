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

            <?php
            $waItems = [];
            foreach ($orderItems as $item) {
                $waItems[] = "- " . $item['product_name'] . " (x" . $item['quantity'] . ") : " . format_price($item['price'] * $item['quantity']);
            }
            $waMessage = "🌟 *NOUVELLE COMMANDE HAYZ #{$order['id']}*\n\n"
                       . "👤 *Client :* " . $order['customer_name'] . "\n"
                       . "📞 *Téléphone :* " . $order['phone'] . "\n"
                       . "📍 *Ville / Adresse :* " . $order['address'] . ", " . $order['city'] . "\n\n"
                       . "🛍️ *Articles :*\n" . implode("\n", $waItems) . "\n\n"
                       . "💰 *Total à payer :* " . format_price($order['total']) . " (Cash on Delivery)\n\n"
                       . "Je souhaite confirmer ma commande Hayz.";
            $waLink = "https://wa.me/212688212229?text=" . urlencode($waMessage);
            ?>

            <div style="margin-bottom: 25px;">
                <a href="<?= $waLink ?>" target="_blank" class="btn btn-lg" style="background:#25D366; color:#FFFFFF; border:none; font-weight:700; display:inline-flex; align-items:center; gap:10px; box-shadow:0 4px 14px rgba(37,211,102,0.4); padding:14px 28px; border-radius:var(--border-radius-full);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    Confirmer la commande sur WhatsApp (+212 688-212229)
                </a>
            </div>

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
                <div style="display:flex; align-items:center; gap:12px;">
                    <img src="<?= asset('logo/logo.png') ?>" alt="Hayz" style="height:44px; width:auto; object-fit:contain;">
                    <div>
                        <h2 style="font-size:1.3rem; margin:0; line-height:1.2;">Hayz Order Receipt #<?= $order['id'] ?></h2>
                        <span class="text-muted" style="font-size:0.85rem;">Placed on <?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></span>
                    </div>
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
