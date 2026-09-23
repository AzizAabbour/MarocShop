<?php
/**
 * Hayz - Shopping Cart Handler & Page
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

// Handle "Buy Now" shortcut (e.g. cart.php?buy_now=5)
if (!empty($_GET['buy_now'])) {
    $buyNowId = (int)$_GET['buy_now'];
    add_to_cart($buyNowId, 1);
    redirect('checkout.php');
}

// Handle Form POST Requests (Add, Update, Remove, Clear)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $isAjax = !empty($_POST['ajax']);

    if (!verify_csrf()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CSRF verification failed.']);
            exit;
        }
        set_flash('error', 'Session expired. Please try again.');
        redirect('cart.php');
    }

    if ($action === 'add' && $productId > 0) {
        $qty = max(1, (int)($_POST['quantity'] ?? 1));
        $added = add_to_cart($productId, $qty);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'    => $added,
                'cart_count' => get_cart_count(),
                'message'    => $added ? 'Product added to your cart!' : 'Could not add product (Out of stock or unavailable).'
            ]);
            exit;
        }

        if ($added) {
            set_flash('success', 'Product added to your cart.');
        } else {
            set_flash('error', 'Could not add product to cart.');
        }
        redirect('cart.php');
    }

    if ($action === 'update' && $productId > 0) {
        $qty = (int)($_POST['quantity'] ?? 1);
        update_cart_quantity($productId, $qty);
        set_flash('success', 'Cart updated successfully.');
        redirect('cart.php');
    }

    if ($action === 'remove' && $productId > 0) {
        remove_from_cart($productId);
        set_flash('info', 'Item removed from your cart.');
        redirect('cart.php');
    }

    if ($action === 'clear') {
        clear_cart();
        set_flash('info', 'Your cart has been cleared.');
        redirect('cart.php');
    }
}

// Fetch Cart Data
$cartItems = get_cart_items();
$subtotal = get_cart_subtotal();
$shipping = get_shipping_cost($subtotal);
$total = get_cart_total();

$pageTitle = "Shopping Cart";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Header -->
        <div style="margin-bottom: 30px;">
            <h1 style="font-size: 2.2rem; margin-bottom:6px;">Shopping Cart</h1>
            <p class="text-muted">Review your selected Moroccan artisan creations before checkout.</p>
        </div>

        <?php if (empty($cartItems)): ?>
            <div style="background:var(--bg-card); padding:70px 20px; text-align:center; border-radius:var(--border-radius-lg); border:1px solid var(--border-color); max-width:650px; margin:0 auto;">
                <div style="font-size:3.5rem; margin-bottom:15px; color:var(--color-gold); display:flex; justify-content:center;"><?= radix_icon('backpack', '', 60) ?></div>
                <h2 style="font-size:1.6rem; margin-bottom:10px;">Your Cart is Currently Empty</h2>
                <p class="text-muted" style="margin-bottom:25px;">You haven't added any authentic Moroccan products yet.</p>
                <a href="<?= url('shop.php') ?>" class="btn btn-gold btn-lg">Explore Catalog &rarr;</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart Items List -->
                <div class="cart-table-wrapper">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th style="text-align:center;">Quantity</th>
                                <th>Subtotal</th>
                                <th style="text-align:right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <?php $prod = $item['product']; ?>
                                <tr>
                                    <td>
                                        <div class="cart-item-flex">
                                            <img src="<?= get_image_url($prod['image']) ?>" alt="<?= e($prod['name']) ?>" class="cart-item-img">
                                            <div>
                                                <div class="cart-item-title">
                                                    <a href="<?= url('product.php?id=' . $prod['id']) ?>"><?= e($prod['name']) ?></a>
                                                </div>
                                                <small class="text-muted"><?= e($prod['category_name']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="font-size:0.95rem;"><?= format_price($prod['price']) ?></strong>
                                    </td>
                                    <td style="text-align:center;">
                                        <!-- Quantity Update Form -->
                                        <form action="<?= url('cart.php') ?>" method="POST" style="display:inline-block;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                            
                                            <div class="quantity-picker" style="height:36px;">
                                                <button type="button" class="qty-btn minus" style="height:34px; width:30px;">-</button>
                                                <input type="number" name="quantity" class="qty-input cart-table-qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $prod['stock'] ?>" style="height:34px; width:40px; font-size:0.9rem;" readonly>
                                                <button type="button" class="qty-btn plus" style="height:34px; width:30px;">+</button>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <strong style="color:var(--bg-dark); font-size:1.05rem;"><?= format_price($item['subtotal']) ?></strong>
                                    </td>
                                    <td style="text-align:right;">
                                        <form action="<?= url('cart.php') ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="remove">
                                            <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                            <button type="submit" title="Remove Item" style="color:var(--color-danger); padding:6px; background:none; border:none; cursor:pointer;" onclick="return confirm('Remove this item from your cart?');">
                                                <?= radix_icon('trash', '', 16) ?>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Cart Actions Toolbar -->
                    <div style="padding: 16px 20px; background:var(--bg-light-gray); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                        <a href="<?= url('shop.php') ?>" class="btn btn-outline btn-sm">&larr; Continue Shopping</a>
                        
                        <form action="<?= url('cart.php') ?>" method="POST" style="display:inline;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--color-danger); border-color:var(--color-danger);" onclick="return confirm('Are you sure you want to empty your entire cart?');">
                                <?= radix_icon('trash', '', 14) ?> Clear Cart
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="cart-summary-box">
                    <h3 style="font-size:1.3rem; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border-color);">
                        Order Summary
                    </h3>

                    <div class="summary-row">
                        <span class="text-muted">Subtotal (<?= get_cart_count() ?> items)</span>
                        <strong><?= format_price($subtotal) ?></strong>
                    </div>

                    <div class="summary-row">
                        <span class="text-muted">Shipping (Morocco Express)</span>
                        <?php if ($shipping === 0.0): ?>
                            <span style="color:var(--color-success); font-weight:700;">FREE</span>
                        <?php else: ?>
                            <strong><?= format_price($shipping) ?></strong>
                        <?php endif; ?>
                    </div>

                    <?php if ($subtotal < FREE_SHIPPING_THRESHOLD): ?>
                        <div style="font-size:0.82rem; color:var(--color-gold); background:var(--color-gold-light); padding:8px 12px; border-radius:4px; margin: 10px 0; display:flex; align-items:center; gap:6px;">
                            <?= radix_icon('sparkles', '', 14) ?> Add <strong><?= format_price(FREE_SHIPPING_THRESHOLD - $subtotal) ?></strong> more for <strong>Free Express Shipping</strong>!
                        </div>
                    <?php endif; ?>

                    <div class="summary-row total">
                        <span>Total</span>
                        <span><?= format_price($total) ?></span>
                    </div>

                    <div style="margin-top: 25px;">
                        <a href="<?= url('checkout.php') ?>" class="btn btn-gold btn-lg btn-block">
                            Proceed to Checkout &rarr;
                        </a>
                    </div>

                    <div style="margin-top: 20px; font-size:0.82rem; color:var(--color-text-muted); text-align:center; display:flex; flex-direction:column; gap:6px;">
                        <div style="display:flex; align-items:center; justify-content:center; gap:6px;"><?= radix_icon('shield-check', 'text-gold', 14) ?> Safe & Secure Checkout</div>
                        <div style="display:flex; align-items:center; justify-content:center; gap:6px;"><?= radix_icon('card-stack', 'text-gold', 14) ?> Cash on Delivery Available in All Moroccan Cities</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
