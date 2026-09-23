<?php
/**
 * MarocShop - Checkout Page & Order Placement
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$cartItems = get_cart_items();
if (empty($cartItems)) {
    set_flash('warning', 'Your shopping cart is empty.');
    redirect('shop.php');
}

$subtotal = get_cart_subtotal();
$shipping = get_shipping_cost($subtotal);
$total = get_cart_total();
$user = current_user();

$errors = [];
$customerName = $_POST['customer_name'] ?? ($user['name'] ?? '');
$phone = $_POST['phone'] ?? ($user['phone'] ?? '');
$address = $_POST['address'] ?? ($user['address'] ?? '');
$city = $_POST['city'] ?? ($user['city'] ?? 'Casablanca');

// Moroccan Cities List
$moroccanCities = [
    'Casablanca', 'Rabat', 'Marrakech', 'Tangier', 'Fes', 'Agadir', 
    'Meknes', 'Oujda', 'Kenitra', 'Tetouan', 'Safi', 'Mohammedia', 
    'El Jadida', 'Nador', 'Beni Mellal', 'Taza', 'Khemisset', 'Taroudant'
];

// Handle Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch. Please submit the form again.";
    }

    if (empty(trim($customerName))) {
        $errors[] = "Please provide your full name.";
    }

    if (empty(trim($phone))) {
        $errors[] = "Please provide a valid phone number for delivery confirmation.";
    }

    if (empty(trim($address))) {
        $errors[] = "Please provide your detailed delivery address.";
    }

    if (empty(trim($city))) {
        $errors[] = "Please select your delivery city.";
    }

    if (empty($errors)) {
        $db = get_db();
        try {
            // Start Database Transaction for Data Consistency
            $db->beginTransaction();

            // 1. Insert Order
            $orderSql = "INSERT INTO orders (user_id, total, status, customer_name, phone, address, city, created_at) 
                         VALUES (:user_id, :total, 'pending', :customer_name, :phone, :address, :city, NOW())";
            $orderStmt = $db->prepare($orderSql);
            $orderStmt->execute([
                ':user_id'       => $user['id'] ?? null,
                ':total'         => $total,
                ':customer_name' => trim($customerName),
                ':phone'         => trim($phone),
                ':address'       => trim($address),
                ':city'          => trim($city)
            ]);

            $orderId = (int)$db->lastInsertId();

            // 2. Insert Order Items & Update Product Stocks
            $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (:order_id, :product_id, :quantity, :price)";
            $itemStmt = $db->prepare($itemSql);

            $stockSql = "UPDATE products SET stock = GREATEST(0, stock - :quantity) WHERE id = :product_id";
            $stockStmt = $db->prepare($stockSql);

            foreach ($cartItems as $item) {
                $prod = $item['product'];
                $qty = $item['quantity'];
                $price = $prod['price'];

                // Insert item record
                $itemStmt->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $prod['id'],
                    ':quantity'   => $qty,
                    ':price'      => $price
                ]);

                // Deduct stock
                $stockStmt->execute([
                    ':quantity'   => $qty,
                    ':product_id' => $prod['id']
                ]);
            }

            // Commit Transaction
            $db->commit();

            // Clear Cart Session
            clear_cart();

            // Redirect to Order Confirmation
            $_SESSION['last_order_id'] = $orderId;
            set_flash('success', "Order #{$orderId} placed successfully! Thank you for supporting Moroccan artisans.");
            redirect('order-confirmation.php?id=' . $orderId);

        } catch (Exception $e) {
            $db->rollBack();
            $errors[] = "Failed to place order: " . $e->getMessage();
        }
    }
}

$pageTitle = "Checkout";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 30px;">
            <h1 style="font-size: 2.2rem; margin-bottom:6px;">Order Checkout</h1>
            <p class="text-muted">Enter your delivery address to complete your order with Cash on Delivery.</p>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin-left: 20px; list-style-type: disc;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= url('checkout.php') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="checkout-layout">
                <!-- Shipping Address Form -->
                <div style="background:var(--bg-card); padding:35px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                    <h2 style="font-size:1.35rem; margin-bottom:24px; padding-bottom:12px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; gap:10px;">
                        <span>📍</span> Shipping & Delivery Details
                    </h2>

                    <div class="form-group">
                        <label class="form-label" for="customer_name">Full Name *</label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" value="<?= e($customerName) ?>" placeholder="e.g. Karim Bennani" required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" class="form-control" value="<?= e($phone) ?>" placeholder="e.g. +212 600-112233" required>
                            <small class="text-muted" style="font-size:0.78rem;">Our delivery team will call before arrival.</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="city">City / Region *</label>
                            <select name="city" id="city" class="form-control" required>
                                <option value="">Select your city...</option>
                                <?php foreach ($moroccanCities as $c): ?>
                                    <option value="<?= e($c) ?>" <?= ($city === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Full Delivery Address *</label>
                        <textarea name="address" id="address" class="form-control" rows="3" placeholder="Street name, building number, apartment/floor, neighborhood..." required><?= e($address) ?></textarea>
                    </div>

                    <!-- Payment Method Section -->
                    <div style="margin-top:30px; padding-top:20px; border-top:1px solid var(--border-color);">
                        <h3 style="font-size:1.2rem; margin-bottom:15px; display:flex; align-items:center; gap:10px;">
                            <span>💳</span> Payment Method
                        </h3>

                        <div style="background:var(--color-gold-light); border:2px solid var(--color-gold); border-radius:var(--border-radius-md); padding:18px 20px; display:flex; align-items:flex-start; gap:14px;">
                            <input type="radio" name="payment_method" id="cod" value="cod" checked style="margin-top:4px; accent-color:var(--color-gold);">
                            <div>
                                <label for="cod" style="font-weight:700; font-size:1rem; color:#111111; cursor:pointer;">
                                    💵 Cash on Delivery (Paiement à la livraison)
                                </label>
                                <p style="font-size:0.85rem; color:#555555; margin-top:4px;">
                                    Pay in cash directly to the courier upon delivery and inspection of your package anywhere in Morocco.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Review & Submit -->
                <div class="cart-summary-box">
                    <h3 style="font-size:1.3rem; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                        Review Your Order
                    </h3>

                    <!-- Mini Cart List -->
                    <div style="display:flex; flex-direction:column; gap:12px; margin-bottom:20px; max-height:260px; overflow-y:auto; padding-right:5px;">
                        <?php foreach ($cartItems as $item): ?>
                            <?php $p = $item['product']; ?>
                            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.9rem; gap:10px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <img src="<?= get_image_url($p['image']) ?>" alt="<?= e($p['name']) ?>" style="width:40px; height:40px; border-radius:4px; object-fit:cover;">
                                    <div>
                                        <div style="font-weight:600; color:var(--bg-dark);"><?= e($p['name']) ?></div>
                                        <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                    </div>
                                </div>
                                <strong><?= format_price($item['subtotal']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="summary-row">
                        <span class="text-muted">Subtotal</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>

                    <div class="summary-row">
                        <span class="text-muted">Shipping</span>
                        <?php if ($shipping === 0.0): ?>
                            <span style="color:var(--color-success); font-weight:700;">FREE</span>
                        <?php else: ?>
                            <strong><?= format_price($shipping) ?></strong>
                        <?php endif; ?>
                    </div>

                    <div class="summary-row total">
                        <span>Total to Pay</span>
                        <span style="color:var(--color-gold);"><?= format_price($total) ?></span>
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn btn-gold btn-lg btn-block">
                            Confirm & Place Order (<?= format_price($total) ?>)
                        </button>
                    </div>

                    <div style="margin-top: 15px; font-size:0.8rem; color:var(--color-text-muted); text-align:center;">
                        By placing your order, you agree to MarocShop's terms of service and authentic artisan guarantee.
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
