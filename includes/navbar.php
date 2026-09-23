<?php
/**
 * Hayz - Navbar & Topbar Component with Radix UI Icons
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/icons.php';

$cartCount = get_cart_count();
$user = current_user();

// Fetch categories for navigation dropdown
$navCategories = [];
try {
    $db = get_db();
    $stmt = $db->query("SELECT id, name FROM categories ORDER BY name ASC LIMIT 8");
    $navCategories = $stmt->fetchAll();
} catch (Exception $e) {
    $navCategories = [];
}
?>

<!-- Top Notification Bar -->
<div class="top-bar">
    <div class="container">
        <div class="top-bar-notice">
            <span><?= radix_icon('truck', 'text-gold', 15) ?></span>
            <span>Express Delivery across Morocco — <span class="highlight">Cash on Delivery (Paiement à la livraison)</span></span>
        </div>
        <div class="top-bar-links">
            <span>Tele: +212 688-212229</span>
            <span>E-mail: hayzcre@gmail.com</span>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:700;">
                    <?= radix_icon('dashboard', '', 14) ?> Admin Panel
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<header class="navbar">
    <div class="container navbar-container">
        <!-- Brand Logo -->
        <a href="<?= url('index.php') ?>" class="brand-logo" aria-label="Hayz Homepage">
            <img src="<?= asset('logo/logo.png') ?>" alt="Hayz" class="brand-logo-img">
            <span class="brand-name-text" style="color:#BD8432">Hayz</span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="nav-menu">
            <a href="<?= url('index.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a>
            <a href="<?= url('shop.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'shop.php' && empty($_GET['category_id'])) ? 'active' : '' ?>">Shop</a>
            <a href="<?= url('shop.php') ?>" class="nav-link">Categories</a>
            <a href="<?= url('contact.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>">Contact</a>
        </nav>

        <!-- Navbar Action Icons -->
        <div class="nav-actions">
            <!-- Search Trigger -->
            <button class="action-btn search-trigger-btn" aria-label="Search Catalog" title="Search products">
                <?= radix_icon('magnifying-glass', '', 16) ?>
            </button>

            <!-- Cart Icon with Live Counter -->
            <a href="<?= url('cart.php') ?>" class="action-btn cart-btn" aria-label="Shopping Cart" title="View Cart">
                <?= radix_icon('backpack', '', 16) ?>
                <span class="cart-count"><?= $cartCount ?></span>
            </a>

            <!-- User Menu / Auth Buttons -->
            <?php if ($user): ?>
                <div class="user-dropdown">
                    <div class="user-menu-btn">
                        <span style="color:var(--color-gold); display:flex; align-items:center;">
                            <?= radix_icon('person', '', 16) ?>
                        </span>
                        <span class="user-name-text"><?= e(explode(' ', $user['name'])[0]) ?></span>
                        <small style="font-size:0.7rem; margin-left:2px;">▾</small>
                    </div>
                    <div class="user-dropdown-menu">
                        <div style="padding: 10px 18px; font-size:0.8rem; color:var(--color-text-muted); border-bottom:1px solid var(--border-color);">
                            Signed in as<br><strong style="color:var(--bg-dark); font-size:0.85rem;"><?= e($user['email']) ?></strong>
                        </div>
                        <a href="<?= url('profile.php') ?>">
                            <?= radix_icon('person', '', 14) ?> My Profile
                        </a>
                        <a href="<?= url('orders.php') ?>">
                            <?= radix_icon('cube', '', 14) ?> My Orders
                        </a>
                        <?php if (is_admin()): ?>
                            <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:700;">
                                <?= radix_icon('dashboard', '', 14) ?> Admin Dashboard
                            </a>
                        <?php endif; ?>
                        <div class="divider"></div>
                        <a href="<?= url('logout.php') ?>" style="color:var(--color-danger);">
                            <?= radix_icon('exit', '', 14) ?> Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-auth-buttons">
                    <a href="<?= url('login.php') ?>" class="btn btn-outline btn-sm">Login</a>
                    <a href="<?= url('register.php') ?>" class="btn btn-gold btn-sm">Register</a>
                </div>
            <?php endif; ?>

            <!-- Mobile Hamburger Toggle -->
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Navigation Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Navigation Drawer -->
<div class="mobile-nav-overlay" id="mobileOverlay"></div>
<div class="mobile-nav-drawer" id="mobileDrawer">
    <div class="mobile-nav-header">
        <a href="<?= url('index.php') ?>" class="brand-logo" style="font-size:1.4rem;">
            <img src="<?= asset('logo/logo.png') ?>" alt="Hayz" class="brand-logo-img" style="height:32px;">
        </a>
        <button class="mobile-close-btn" id="mobileCloseBtn">
            <?= radix_icon('cross', '', 14) ?>
        </button>
    </div>

    <div class="mobile-nav-links">
        <a href="<?= url('index.php') ?>">
            <?= radix_icon('dashboard', 'text-gold', 16) ?> Home
        </a>
        <a href="<?= url('shop.php') ?>">
            <?= radix_icon('layers', 'text-gold', 16) ?> All Products
        </a>
        <a href="<?= url('cart.php') ?>">
            <?= radix_icon('backpack', 'text-gold', 16) ?> Shopping Cart (<?= $cartCount ?>)
        </a>
        <a href="<?= url('contact.php') ?>">
            <?= radix_icon('envelope-closed', 'text-gold', 16) ?> Contact Us
        </a>
        
        <?php if ($user): ?>
            <a href="<?= url('profile.php') ?>">
                <?= radix_icon('person', 'text-gold', 16) ?> My Profile
            </a>
            <a href="<?= url('orders.php') ?>">
                <?= radix_icon('cube', 'text-gold', 16) ?> My Orders
            </a>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:bold;">
                    <?= radix_icon('dashboard', 'text-gold', 16) ?> Admin Panel
                </a>
            <?php endif; ?>
            <a href="<?= url('logout.php') ?>" style="color:var(--color-danger);">
                <?= radix_icon('exit', '', 16) ?> Logout
            </a>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:10px; margin-top:15px;">
                <a href="<?= url('login.php') ?>" class="btn btn-outline btn-block">Login</a>
                <a href="<?= url('register.php') ?>" class="btn btn-gold btn-block">Create Account</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Search Modal -->
<div class="search-modal" id="searchModal">
    <div class="search-modal-box">
        <button class="search-close-btn" id="searchCloseBtn">
            <?= radix_icon('cross', '', 14) ?>
        </button>
        <h3 style="font-size:1.35rem; margin-bottom:6px;">Search Hayz Catalog</h3>
        <p class="text-muted" style="font-size:0.88rem;">Find Moroccan argan oils, rugs, tagines, leather babouches, caftans, and spices.</p>
        
        <form action="<?= url('shop.php') ?>" method="GET" class="search-form-modal">
            <input type="text" name="search" id="searchModalInput" class="search-input-modal" placeholder="Type product name, category, or keyword..." required>
            <button type="submit" class="btn btn-gold">
                <?= radix_icon('magnifying-glass', '', 15) ?> Search
            </button>
        </form>
    </div>
</div>

<!-- Flash Message Notifications -->
<?php $flash = get_flash(); if ($flash): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= ($flash['type'] === 'success') ? radix_icon('check-circled', '', 18) : radix_icon('cross-1', '', 18) ?>
            <span><?= e($flash['message']) ?></span>
        </div>
    </div>
<?php endif; ?>