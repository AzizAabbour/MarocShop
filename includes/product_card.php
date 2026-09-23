<?php
/**
 * MarocShop - Reusable Product Card Component with Radix UI Icons
 * Expects $product array in scope
 */
require_once __DIR__ . '/icons.php';

if (!isset($product)) return;

$discountPercent = 0;
if (!empty($product['old_price']) && $product['old_price'] > $product['price']) {
    $discountPercent = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100);
}
$isOutOfStock = ($product['stock'] <= 0);
?>
<div class="product-card">
    <div class="product-image-box">
        <a href="<?= url('product.php?id=' . $product['id']) ?>" style="display:block; width:100%; height:100%;">
            <img src="<?= get_image_url($product['image']) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        </a>
        
        <div class="product-badges">
            <?php if ($discountPercent > 0): ?>
                <span class="badge badge-discount">-<?= $discountPercent ?>%</span>
            <?php endif; ?>
            <?php if ($isOutOfStock): ?>
                <span class="badge badge-out-of-stock">Out of Stock</span>
            <?php endif; ?>
        </div>

        <div class="product-actions-hover">
            <a href="<?= url('product.php?id=' . $product['id']) ?>" class="btn btn-outline btn-sm" style="flex:1;">
                <?= radix_icon('eye-open', '', 14) ?> Details
            </a>
            <?php if (!$isOutOfStock): ?>
                <form action="<?= url('cart.php') ?>" method="POST" class="ajax-add-to-cart" style="flex:1;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-gold btn-sm btn-block">
                        <?= radix_icon('backpack', '', 14) ?> Add
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-content">
        <?php if (!empty($product['category_name'])): ?>
            <div class="product-category-name"><?= e($product['category_name']) ?></div>
        <?php endif; ?>
        
        <h3 class="product-title">
            <a href="<?= url('product.php?id=' . $product['id']) ?>"><?= e($product['name']) ?></a>
        </h3>

        <div class="product-price-row">
            <span class="price-current"><?= format_price($product['price']) ?></span>
            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                <span class="price-old"><?= format_price($product['old_price']) ?></span>
            <?php endif; ?>
        </div>

        <div class="product-footer-btn">
            <?php if ($isOutOfStock): ?>
                <button class="btn btn-outline btn-sm btn-block" disabled style="opacity:0.6; cursor:not-allowed;">Sold Out</button>
            <?php else: ?>
                <form action="<?= url('cart.php') ?>" method="POST" class="ajax-add-to-cart">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <?= radix_icon('backpack', '', 15) ?> Add to Cart
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
