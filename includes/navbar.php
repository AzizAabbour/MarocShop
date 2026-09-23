<?php
/**
 * MarocShop - Navbar & Topbar Component
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

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
            <span>🇲🇦</span>
            <span>Express Delivery across Morocco — <span class="highlight">Cash on Delivery (Paiement à la
                    livraison)</span></span>
        </div>
        <div class="top-bar-links">
            <span>tele: +212614484434</span>
            <span>email: [EMAIL_ADDRESS]</span>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:700;">⚙️ Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<header class="navbar">
    <div class="container navbar-container">
        <!-- Brand Logo -->
        <a href="<?= url('index.php') ?>" class="brand-logo" aria-label="MarocShop Homepage">
            <span class="logo-badge">M</span>arocShop
            <span class="tag">MA</span>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="nav-menu">
            <a href="<?= url('index.php') ?>"
                class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a>
            <a href="<?= url('shop.php') ?>"
                class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'shop.php' && empty($_GET['category_id'])) ? 'active' : '' ?>">Shop</a>
            <a href="<?= url('shop.php') ?>" class="nav-link">Categories</a>
            <a href="<?= url('contact.php') ?>"
                class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>">Contact</a>
        </nav>

        <!-- Navbar Action Icons -->
        <div class="nav-actions">
            <!-- Search Trigger -->
            <button class="action-btn search-trigger-btn" aria-label="Search Catalog" title="Search products">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </button>

            <!-- Cart Icon with Live Counter -->
            <a href="<?= url('cart.php') ?>" class="action-btn cart-btn" aria-label="Shopping Cart" title="View Cart">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-count"><?= $cartCount ?></span>
            </a>

            <!-- User Menu / Auth Buttons -->
            <?php if ($user): ?>
                <div class="user-dropdown">
                    <div class="user-menu-btn">
                        <span style="color:var(--color-gold);">👤</span>
                        <span><?= e(explode(' ', $user['name'])[0]) ?></span>
                        <small>▾</small>
                    </div>
                    <div class="user-dropdown-menu">
                        <div
                            style="padding: 10px 18px; font-size:0.82rem; color:var(--color-text-muted); border-bottom:1px solid var(--border-color);">
                            Signed in as<br><strong style="color:var(--bg-dark);"><?= e($user['email']) ?></strong>
                        </div>
                        <a href="<?= url('profile.php') ?>">👤 My Profile</a>
                        <a href="<?= url('orders.php') ?>">📦 My Orders</a>
                        <?php if (is_admin()): ?>
                            <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:700;">⚙️ Admin
                                Dashboard</a>
                        <?php endif; ?>
                        <div class="divider"></div>
                        <a href="<?= url('logout.php') ?>" style="color:var(--color-danger);">🚪 Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div style="display:flex; align-items:center; gap:8px;">
                    <a href="<?= url('login.php') ?>" class="btn btn-outline btn-sm">Login</a>
                    <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm"
                        style="background:var(--color-gold); color:#111111; border-color:var(--color-gold);">Register</a>
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
            <span class="logo-badge">M</span>arocShop
        </a>
        <button class="mobile-close-btn" id="mobileCloseBtn">&times;</button>
    </div>

    <div class="mobile-nav-links">
        <a href="<?= url('index.php') ?>">🏠 Home</a>
        <a href="<?= url('shop.php') ?>">🛍️ All Products</a>
        <a href="<?= url('cart.php') ?>">🛒 Shopping Cart (<?= $cartCount ?>)</a>
        <a href="<?= url('contact.php') ?>">✉️ Contact Us</a>

        <?php if ($user): ?>
            <a href="<?= url('profile.php') ?>">👤 My Profile</a>
            <a href="<?= url('orders.php') ?>">📦 My Orders</a>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/index.php') ?>" style="color:var(--color-gold); font-weight:bold;">⚙️ Admin Panel</a>
            <?php endif; ?>
            <a href="<?= url('logout.php') ?>" style="color:var(--color-danger);">🚪 Logout</a>
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
        <button class="search-close-btn" id="searchCloseBtn">&times;</button>
        <h3 style="font-size:1.4rem; margin-bottom:8px;">Search MarocShop Catalog</h3>
        <p class="text-muted" style="font-size:0.9rem;">Find Moroccan argan oils, rugs, tagines, leather babouches,
            caftans, and spices.</p>

        <form action="<?= url('shop.php') ?>" method="GET" class="search-form-modal">
            <input type="text" name="search" id="searchModalInput" class="search-input-modal"
                placeholder="Type product name, category, or keyword..." required>
            <button type="submit" class="btn btn-gold">Search</button>
        </form>
    </div>
</div>

<!-- Flash Message Notifications (if any) -->
<?php $flash = get_flash();
if ($flash): ?>
    <div class="container" style="margin-top: 20px;">
        <div class="alert alert-<?= e($flash['type']) ?>">
            <span><?= e($flash['message']) ?></span>
        </div>
    </div>
<?php endif; ?>