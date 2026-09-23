<?php
/**
 * Hayz - Footer Component
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/icons.php';
?>
<!-- Footer Section -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Info -->
            <div class="footer-column">
                <a href="<?= url('index.php') ?>" class="brand-logo" style="margin-bottom:15px; display:inline-block;">
                    <img src="<?= asset('logo/logo.png') ?>" alt="Hayz" class="brand-logo-img" style="height:40px; width:auto;">
                </a>
                <p style="color:#AAAAAA; line-height:1.7; margin-bottom:20px; font-size:0.92rem;">
                    Your premier online destination for authentic Moroccan craftsmanship, pure organic Argan beauty, handknotted Berber rugs, and timeless cultural heritage.
                </p>
                <div style="color:#CCCCCC; font-size:0.88rem; display:flex; flex-direction:column; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="color:var(--color-gold);"><?= radix_icon('pin', '', 15) ?></span>
                        <span>33°34'12.7"N 7°32'01.1"W — Casablanca, Morocco</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="color:var(--color-gold);"><?= radix_icon('phone', '', 15) ?></span>
                        <span>+212 688-212229</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span style="color:var(--color-gold);"><?= radix_icon('envelope-closed', '', 15) ?></span>
                        <span>hayzcre@gmail.com</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <div class="footer-links">
                    <a href="<?= url('index.php') ?>">Home</a>
                    <a href="<?= url('shop.php') ?>">All Products</a>
                    <a href="<?= url('cart.php') ?>">Shopping Cart</a>
                    <a href="<?= url('contact.php') ?>">Customer Support</a>
                    <a href="<?= url('profile.php') ?>">My Account</a>
                </div>
            </div>

            <!-- Categories -->
            <div class="footer-column">
                <h4>Categories</h4>
                <div class="footer-links">
                    <a href="<?= url('shop.php?category_id=1') ?>">Pure Argan & Cosmetics</a>
                    <a href="<?= url('shop.php?category_id=2') ?>">Berber & Kilim Rugs</a>
                    <a href="<?= url('shop.php?category_id=3') ?>">Ceramics & Tagines</a>
                    <a href="<?= url('shop.php?category_id=4') ?>">Handmade Leather Goods</a>
                    <a href="<?= url('shop.php?category_id=5') ?>">Traditional Caftans</a>
                    <a href="<?= url('shop.php?category_id=6') ?>">Spices & Royal Teas</a>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="footer-column">
                <h4>Join Our Club</h4>
                <p style="color:#AAAAAA; font-size:0.88rem; margin-bottom:15px;">
                    Subscribe to receive private sale invitations, Moroccan artisanal stories, and 10% off your first order.
                </p>
                <form action="<?= url('contact.php') ?>" method="GET" style="display:flex; flex-direction:column; gap:10px;">
                    <input type="email" placeholder="Your email address..." required style="padding:12px 16px; border-radius:var(--border-radius-sm); border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:#FFFFFF; font-size:0.9rem;">
                    <button type="submit" class="btn btn-gold btn-sm">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="footer-bottom">
        <div class="container">
            <div style="font-size:0.85rem; color:#888888;">
                &copy; <?= date('Y') ?> <strong><?= STORE_NAME ?></strong>. All rights reserved. Crafted with authentic passion in Morocco.
            </div>
            <div class="payment-methods-badge">
                <span class="payment-tag" style="display:inline-flex; align-items:center; gap:6px;"><?= radix_icon('card-stack', 'text-gold', 14) ?> Cash on Delivery (COD)</span>
                <span class="payment-tag" style="display:inline-flex; align-items:center; gap:6px;"><?= radix_icon('shield-check', 'text-gold', 14) ?> 100% Authentic Artisanal</span>
                <span class="payment-tag" style="display:inline-flex; align-items:center; gap:6px;"><?= radix_icon('truck', 'text-gold', 14) ?> Express Morocco Shipping</span>
            </div>
        </div>
    </div>
</footer>

<!-- Toast notification anchor -->
<div class="toast-container"></div>

<!-- Custom Vanilla JavaScript -->
<script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>
