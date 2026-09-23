<?php
/**
 * MarocShop - Single Product Details Page
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    set_flash('error', 'Product not found.');
    redirect('shop.php');
}

$db = get_db();

// Fetch Product Details with Category Name
$stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id LIMIT 1");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'The requested product is no longer available.');
    redirect('shop.php');
}

// Fetch Related Products (same category, excluding current product)
$relStmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = :cat_id AND p.id != :prod_id ORDER BY p.id DESC LIMIT 4");
$relStmt->execute(['cat_id' => $product['category_id'], 'prod_id' => $product['id']]);
$relatedProducts = $relStmt->fetchAll();

// Calculate discount
$discountPercent = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
    $discountPercent = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
}
$isOutOfStock = ($product['stock'] <= 0);

$pageTitle = $product['name'] . " - " . STORE_NAME;
$pageDescription = substr(strip_tags($product['description'] ?? ''), 0, 160);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Breadcrumbs -->
        <div style="font-size:0.88rem; color:var(--color-text-muted); margin-bottom:25px;">
            <a href="<?= url('index.php') ?>">Home</a> / 
            <a href="<?= url('shop.php') ?>">Shop</a> / 
            <a href="<?= url('shop.php?category_id=' . $product['category_id']) ?>"><?= e($product['category_name']) ?></a> / 
            <span style="color:var(--bg-dark); font-weight:600;"><?= e($product['name']) ?></span>
        </div>

        <!-- Product Details Grid -->
        <div class="product-details-grid">
            <!-- Product Gallery Frame -->
            <div class="product-gallery">
                <div class="main-image-frame">
                    <img id="mainProductImage" src="<?= get_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>">
                </div>
                
                <!-- Guarantee Badges -->
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin-top:10px;">
                    <div style="background:var(--bg-card); padding:12px; border-radius:var(--border-radius-sm); border:1px solid var(--border-color); text-align:center;">
                        <div style="color:var(--color-gold); display:flex; justify-content:center; align-items:center; min-height:26px;"><?= radix_icon('shield-check', '', 22) ?></div>
                        <div style="font-size:0.75rem; font-weight:700; margin-top:4px;">100% Authentic</div>
                    </div>
                    <div style="background:var(--bg-card); padding:12px; border-radius:var(--border-radius-sm); border:1px solid var(--border-color); text-align:center;">
                        <div style="color:var(--color-gold); display:flex; justify-content:center; align-items:center; min-height:26px;"><?= radix_icon('truck', '', 22) ?></div>
                        <div style="font-size:0.75rem; font-weight:700; margin-top:4px;">Fast Morocco Shipping</div>
                    </div>
                    <div style="background:var(--bg-card); padding:12px; border-radius:var(--border-radius-sm); border:1px solid var(--border-color); text-align:center;">
                        <div style="color:var(--color-gold); display:flex; justify-content:center; align-items:center; min-height:26px;"><?= radix_icon('card-stack', '', 22) ?></div>
                        <div style="font-size:0.75rem; font-weight:700; margin-top:4px;">Cash on Delivery</div>
                    </div>
                </div>
            </div>

            <!-- Product Information Panel -->
            <div class="product-info-panel">
                <div class="product-category-name" style="font-size:0.9rem;">
                    <?= e($product['category_name']) ?>
                </div>

                <h1 class="product-detail-title"><?= e($product['name']) ?></h1>

                <!-- Price Row -->
                <div class="product-detail-price">
                    <span class="detail-price-main"><?= format_price($product['price']) ?></span>
                    <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                        <span class="detail-price-old"><?= format_price($product['old_price']) ?></span>
                        <span class="badge badge-discount" style="font-size:0.85rem;">Save <?= $discountPercent ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div style="display:flex; align-items:center; gap:10px;">
                    <?php if ($isOutOfStock): ?>
                        <span class="badge badge-out-of-stock" style="font-size:0.85rem; display:inline-flex; align-items:center; gap:5px;"><?= radix_icon('cross-1', '', 14) ?> Currently Out of Stock</span>
                    <?php else: ?>
                        <span class="badge badge-stock" style="font-size:0.85rem; display:inline-flex; align-items:center; gap:5px;"><?= radix_icon('check-circled', '', 14) ?> In Stock (<?= $product['stock'] ?> units available)</span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description-text">
                    <?= nl2br(e($product['description'])) ?>
                </div>

                <hr style="border:0; border-top:1px solid var(--border-color); margin: 10px 0;">

                <!-- Purchase Forms -->
                <?php if (!$isOutOfStock): ?>
                    <form action="<?= url('cart.php') ?>" method="POST" class="ajax-add-to-cart">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <div class="quantity-control-group" style="margin-bottom: 20px;">
                            <label style="font-weight:700; font-size:0.95rem;">Quantity:</label>
                            <div class="quantity-picker">
                                <button type="button" class="qty-btn minus">-</button>
                                <input type="number" name="quantity" class="qty-input" value="1" min="1" max="<?= $product['stock'] ?>" readonly>
                                <button type="button" class="qty-btn plus">+</button>
                            </div>
                        </div>

                        <div style="display:flex; gap:15px; flex-wrap:wrap;">
                            <button type="submit" class="btn btn-primary btn-lg" style="flex:1; min-width:200px;">
                                <?= radix_icon('backpack', '', 18) ?> Add to Cart
                            </button>
                            
                            <!-- Buy Now button (Adds to cart & redirects to checkout) -->
                            <a href="<?= url('cart.php?buy_now=' . $product['id']) ?>" class="btn btn-gold btn-lg" style="flex:1; min-width:200px;">
                                <?= radix_icon('sparkles', '', 18) ?> Buy Now
                            </a>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning">
                        This product is currently out of stock. Please check back later or contact customer support for upcoming batches.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products Section -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="section-spacing-sm">
                <div class="section-header">
                    <span class="section-subtitle">Complementary Treasures</span>
                    <h2 class="section-title">Related Products</h2>
                    <div class="section-divider left"></div>
                </div>

                <div class="products-grid">
                    <?php foreach ($relatedProducts as $product): ?>
                        <?php include __DIR__ . '/includes/product_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
