<?php
/**
 * Hayz - Homepage with Radix UI Icons & Sleek Aesthetics
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/icons.php';

$pageTitle = "Hayz | Authentic Moroccan Craftsmanship & Organic Luxury";
$pageDescription = "Explore the finest Moroccan treasures: certified Argan oil, Beni Ourain wool rugs, Fez ceramics, handcrafted babouche slippers, and bespoke caftans.";

$db = get_db();

// 1. Fetch Popular Categories
$categories = [];
try {
    $catStmt = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC LIMIT 6");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// 2. Fetch Featured Products (Top rated / high value)
$featuredProducts = [];
try {
    $featStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.price DESC LIMIT 4");
    $featuredProducts = $featStmt->fetchAll();
} catch (Exception $e) {
    $featuredProducts = [];
}

// 3. Fetch New Arrivals (Latest created)
$newArrivals = [];
try {
    $newStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC LIMIT 4");
    $newArrivals = $newStmt->fetchAll();
} catch (Exception $e) {
    $newArrivals = [];
}

// 4. Fetch Best Sellers (e.g. Cosmetic & Leather)
$bestSellers = [];
try {
    $bestStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id IN (1, 4, 6) ORDER BY p.stock ASC LIMIT 4");
    $bestSellers = $bestStmt->fetchAll();
} catch (Exception $e) {
    $bestSellers = [];
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main>
    <!-- Hero Section with Background Video & Dark Blur -->
    <section class="hero-section">
        <div class="hero-video-wrapper">
            <video class="hero-bg-video" autoplay loop muted playsinline>
                <source src="<?= asset('videos/hero-shopping.mp4') ?>" type="video/mp4">
                <source src="https://assets.mixkit.co/videos/6302/6302-720.mp4" type="video/mp4">
            </video>
            <div class="hero-video-overlay"></div>
        </div>

        <div class="container hero-grid">
            <div class="hero-text-content">
                <div class="hero-tag">
                    <?= radix_icon('sparkles', '', 14) ?> Authentic Moroccan Heritage
                </div>
                <h1 class="hero-title">
                    Timeless Moroccan Elegance, <br>
                    <span class="gold">Handcrafted</span> for You.
                </h1>
                <p class="hero-description">
                    Immerse yourself in authentic Moroccan artisanal luxury. From 100% pure organic Taroudant Argan oil and handwoven Atlas Berber rugs to Fez ceramics and royal silk velvet caftans.
                </p>
                <div class="hero-actions">
                    <a href="<?= url('shop.php') ?>" class="btn btn-gold btn-lg">
                        Shop Collection <?= radix_icon('chevron-right', '', 16) ?>
                    </a>
                    <a href="<?= url('shop.php?category_id=1') ?>" class="btn btn-outline-gold btn-lg">
                        Explore Argan Beauty
                    </a>
                </div>
            </div>

            <div class="hero-image-wrapper">
                <div class="hero-logo-clean-display">
                    <img src="<?= asset('logo/logo.png') ?>" alt="Hayz Logo" class="hero-clean-logo-img">
                    <div class="hero-clean-logo-title">HAYZ</div>
                    <div class="hero-clean-logo-subtitle">AUTHENTIC MOROCCAN LUXURY</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Value Propositions -->
    <section class="features-strip">
        <div class="container features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <?= radix_icon('truck', '', 22) ?>
                </div>
                <div>
                    <div class="feature-title">Express Delivery</div>
                    <div class="feature-desc">Fast delivery in 24-48h across all Moroccan cities.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <?= radix_icon('wallet', '', 22) ?>
                </div>
                <div>
                    <div class="feature-title">Cash on Delivery</div>
                    <div class="feature-desc">Pay with cash safely upon inspecting your package.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <?= radix_icon('sparkles', '', 22) ?>
                </div>
                <div>
                    <div class="feature-title">100% Organic & Pure</div>
                    <div class="feature-desc">Certified virgin oils & authentic natural ingredients.</div>
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <?= radix_icon('shield-check', '', 22) ?>
                </div>
                <div>
                    <div class="feature-title">Master Craftsmanship</div>
                    <div class="feature-desc">Handmade by verified master artisans across Morocco.</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Categories Section -->
    <section class="section-spacing">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-subtitle"><?= radix_icon('layers', '', 14) ?> Discover Our Specialties</span>
                <h2 class="section-title">Popular Categories</h2>
                <div class="section-divider"></div>
            </div>

            <div class="categories-grid">
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= url('shop.php?category_id=' . $cat['id']) ?>" class="category-card">
                        <div class="category-icon-wrapper">
                            <img src="<?= get_image_url($cat['image'], 'category') ?>" alt="<?= e($cat['name']) ?>" style="width:44px; height:44px;">
                        </div>
                        <h3 class="category-name"><?= e($cat['name']) ?></h3>
                        <span class="category-count"><?= $cat['product_count'] ?> Products</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="section-spacing" style="background-color: var(--bg-light-gray);">
        <div class="container">
            <div class="section-header flex-between" style="flex-wrap:wrap; gap:15px;">
                <div>
                    <span class="section-subtitle"><?= radix_icon('sparkles', '', 14) ?> Handpicked Luxury</span>
                    <h2 class="section-title">Featured Creations</h2>
                    <div class="section-divider left"></div>
                </div>
                <a href="<?= url('shop.php') ?>" class="btn btn-outline btn-sm">
                    View All Products <?= radix_icon('chevron-right', '', 14) ?>
                </a>
            </div>

            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Special Offers / Promo Banner -->
    <section class="section-spacing">
        <div class="container">
            <div class="promo-banner">
                <div class="promo-grid">
                    <div>
                        <span class="badge badge-gold" style="margin-bottom: 15px;">
                            <?= radix_icon('sparkles', '', 13) ?> Seasonal Privilege
                        </span>
                        <h2 class="promo-title">Save Up to <span>30% Off</span> on Pure Argan & Prickly Pear Elixirs</h2>
                        <p style="color:#B5BAC3; margin-bottom: 25px; font-size:1.02rem; line-height:1.7;">
                            Harvested fresh from Taroudant and Ait Baamrane. Unlock the world's most sought-after organic beauty rituals. Limited seasonal batches available.
                        </p>
                        <a href="<?= url('shop.php?category_id=1') ?>" class="btn btn-gold btn-lg">
                            Explore Beauty Offers <?= radix_icon('chevron-right', '', 16) ?>
                        </a>
                    </div>
                    <div class="text-center">
                        <img src="<?= asset('images/prod_prickly_pear.svg') ?>" alt="Argan & Prickly Pear Promo" style="max-height: 270px; margin: 0 auto; border-radius: 12px; border: 1.5px solid var(--color-gold);">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="section-spacing">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-subtitle"><?= radix_icon('sparkles', '', 14) ?> Fresh From The Ateliers</span>
                <h2 class="section-title">New Arrivals</h2>
                <div class="section-divider"></div>
            </div>

            <div class="products-grid">
                <?php foreach ($newArrivals as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Best Sellers Section -->
    <section class="section-spacing" style="background-color: #FFFFFF;">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-subtitle"><?= radix_icon('star-filled', '', 14) ?> Customer Favorites</span>
                <h2 class="section-title">Best Sellers</h2>
                <div class="section-divider"></div>
            </div>

            <div class="products-grid">
                <?php foreach ($bestSellers as $product): ?>
                    <?php include __DIR__ . '/includes/product_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Customer Testimonials -->
    <section class="section-spacing" style="background-color: var(--bg-light-gray);">
        <div class="container">
            <div class="section-header text-center">
                <span class="section-subtitle"><?= radix_icon('check-circled', '', 14) ?> Real Experiences</span>
                <h2 class="section-title">What Our Customers Say</h2>
                <div class="section-divider"></div>
            </div>

            <div class="testimonials-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <?php for($i=0; $i<5; $i++) echo radix_icon('star-filled', 'text-gold', 16); ?>
                    </div>
                    <p class="testimonial-text">
                        "The Beni Ourain rug I ordered arrived in Casablanca in just 24 hours. The wool quality is sublime and the geometric Berber pattern gives our living room an authentic palace feel."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">LB</div>
                        <div>
                            <div class="author-name">Laila Benjelloun</div>
                            <div class="author-city">Casablanca, Morocco</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <?php for($i=0; $i<5; $i++) echo radix_icon('star-filled', 'text-gold', 16); ?>
                    </div>
                    <p class="testimonial-text">
                        "I've been searching for 100% pure organic prickly pear seed oil and this is by far the highest purity on the market. Non-greasy and my skin feels deeply nourished."
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">SM</div>
                        <div>
                            <div class="author-name">Soukaina Mansouri</div>
                            <div class="author-city">Rabat, Morocco</div>
                        </div>
                    </div>
                </div>

                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <?php for($i=0; $i<5; $i++) echo radix_icon('star-filled', 'text-gold', 16); ?>
                    </div>
                    <p class="testimonial-text">
                        "Super convenient Cash on Delivery service. The leather duffle bag and Fez glazed tagine were securely packaged with care. Outstanding Moroccan craftsmanship!"
                    </p>
                    <div class="testimonial-author">
                        <div class="author-avatar">MK</div>
                        <div>
                            <div class="author-name">Mehdi Kabbaj</div>
                            <div class="author-city">Marrakech, Morocco</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-box">
                <span class="badge badge-gold" style="margin-bottom: 12px;">
                    <?= radix_icon('sparkles', '', 13) ?> VIP Club
                </span>
                <h2 style="color:#FFFFFF; font-size:2.15rem; margin-bottom: 12px;">Join the MarocShop Insider</h2>
                <p style="color:#9DA3AE; font-size: 1rem;">
                    Receive private invitations to artisan exhibitions, seasonal discounts, and secret collection launches.
                </p>
                <form action="<?= url('contact.php') ?>" method="GET" class="newsletter-form">
                    <input type="email" name="email" class="newsletter-input" placeholder="Enter your email address..." required>
                    <button type="submit" class="btn btn-gold">
                        Subscribe <?= radix_icon('chevron-right', '', 15) ?>
                    </button>
                </form>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
