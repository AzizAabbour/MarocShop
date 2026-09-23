-- ==========================================================
-- MarocShop E-Commerce Database Schema & Initial Data
-- Compatible with MySQL 5.7+ / MySQL 8+ / MariaDB
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `ecommerce` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `address` VARCHAR(255) NULL,
    `city` VARCHAR(80) NULL,
    `role` ENUM('customer', 'admin') DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `image` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Products Table
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `old_price` DECIMAL(10,2) NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `image` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `status` ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    `customer_name` VARCHAR(120) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `city` VARCHAR(80) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Order Items Table
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `price` DECIMAL(10,2) NOT NULL,
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Contact Messages Table
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(120) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED DATA
-- ==========================================================

-- Insert Default Admin (password: admin123) and Customer (password: user123)
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `role`, `created_at`) VALUES
(1, 'Admin MarocShop', 'admin@marocshop.ma', '$2y$12$skKQgNKAQccnLINt1gkm5eWlyCYz3bh/QHomVZSKINpFS5Ru07TCa', '+212 600-000001', 'Boulevard d\'Anfa 123', 'Casablanca', 'admin', NOW()),
(2, 'Karim Bennani', 'karim@example.com', '$2y$12$3pTqDRJhXxCGdknwiFIsueMv6JXOky6HSFc5ABywS16lKJ9WD4TRi', '+212 611-223344', 'Avenue Mohammed V 45', 'Rabat', 'customer', NOW());

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`) VALUES
(1, 'Pure Argan & Natural Cosmetics', 'Authentic Moroccan cosmetic & culinary argan oils, prickly pear serum, and organic botanical care.', 'cat_argan.svg', NOW()),
(2, 'Artisanal Moroccan Rugs', 'Handwoven Beni Ourain, Berber, and Kilim wool rugs handcrafted in the Atlas Mountains.', 'cat_rugs.svg', NOW()),
(3, 'Handcrafted Ceramics & Tagines', 'Traditional handmade Fez & Safi glazed ceramics, serving platters, and authentic cooking tagines.', 'cat_ceramics.svg', NOW()),
(4, 'Moroccan Leather Goods', 'Premium full-grain leather bags, handcrafted babouches slippers, and artisanal leather poufs.', 'cat_leather.svg', NOW()),
(5, 'Traditional Caftans & Fashion', 'Modern luxury caftans, embroidered gandoras, djellabas, and bespoke Moroccan apparel.', 'cat_fashion.svg', NOW()),
(6, 'Spices & Royal Teas', 'Grade-1 Taliouine Saffron, Ras El Hanout, pure dried Damask rosebuds, and organic mint tea.', 'cat_spices.svg', NOW());

-- Insert Products
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `old_price`, `stock`, `image`, `created_at`) VALUES
(1, 1, 'Organic Moroccan Argan Oil (100ml)', '100% Pure cold-pressed virgin cosmetic argan oil sourced directly from women cooperatives in Taroudant. Rich in vitamin E and essential fatty acids for glowing skin and silky hair.', 180.00, 240.00, 45, 'prod_argan_oil.svg', NOW()),
(2, 1, 'Prickly Pear Seed Oil Elixir (30ml)', 'Pure organic miracle cactus seed oil from Ait Baamrane. The ultimate youth elixir renowned for high antioxidant content and natural anti-aging properties.', 350.00, 420.00, 28, 'prod_prickly_pear.svg', NOW()),
(3, 1, 'Authentic Moroccan Black Soap with Eucalyptus', 'Traditional Hammam Beldi black soap infused with pure eucalyptus essential oil for deep cleansing and gentle skin exfoliation.', 65.00, 85.00, 90, 'prod_black_soap.svg', NOW()),
(4, 2, 'Authentic Beni Ourain Geometric Wool Rug', 'Handknotted 100% natural virgin sheep wool rug featuring timeless Berber diamond patterns. Handcrafted by master weavers in the Middle Atlas.', 2400.00, 2900.00, 6, 'prod_rug_beniourain.svg', NOW()),
(5, 2, 'Vintage Atlas Kilim Handwoven Runner', 'Vibrant vegetable-dyed wool runner rug with intricate tribal geometric symbols. Durable, colorful, and perfect for hallways or living rooms.', 1150.00, 1400.00, 12, 'prod_rug_kilim.svg', NOW()),
(6, 3, 'Handmade Fez Blue Glazed Tagine (Large)', 'Authentic Moroccan lead-free clay tagine handcrafted and hand-painted in Fez. Designed for slow-simmering aromatic stews and oven-to-table dining.', 290.00, 360.00, 24, 'prod_tagine_fez.svg', NOW()),
(7, 3, 'Ceramic Mosaic Serving Platter (35cm)', 'Artisanal decorative ceramic plate painted with classic Andalusian-Moroccan floral arabesques and geometric Zellige motifs.', 195.00, 250.00, 35, 'prod_ceramic_plate.svg', NOW()),
(8, 4, 'Genuine Moroccan Leather Ottoman Pouf', 'Hand-stitched genuine goat leather pouf from the ancient tanneries of Marrakech. Features traditional embossed mandala detailing.', 380.00, 480.00, 20, 'prod_leather_pouf.svg', NOW()),
(9, 4, 'Handmade Royal Babouche Slippers', 'Ultra-comfortable soft calfskin leather slippers with reinforced soles and delicate gold silk thread embroidery.', 220.00, 280.00, 50, 'prod_babouche.svg', NOW()),
(10, 4, 'Vintage Tan Leather Travel Duffle Bag', 'Supple full-grain Marrakech leather weekend duffle bag with brass hardware and reinforced straps.', 890.00, 1100.00, 15, 'prod_leather_bag.svg', NOW()),
(11, 5, 'Luxury Silk Velvet Royal Caftan', 'Masterfully tailored Moroccan caftan in emerald silk velvet with authentic golden Sfifa braidings and Swarovski crystal accents.', 1850.00, 2300.00, 8, 'prod_caftan_royal.svg', NOW()),
(12, 5, 'Handmade Linen Djellaba for Men', 'Elegant breathable pure linen summer djellaba with fine handcrafted Randa embroidery along collar and cuffs.', 650.00, 780.00, 30, 'prod_djellaba.svg', NOW()),
(13, 6, 'Pure Taliouine Organic Saffron (5g)', 'Aromatic red gold saffron stigmas harvested from the volcanic soils of Taliouine. Certified Grade 1 with intense aroma and color.', 160.00, 200.00, 60, 'prod_saffron.svg', NOW()),
(14, 6, 'Royal Moroccan Tea Set & Teapot', 'Traditional engraved brass Moroccan teapot with ornate legs, matching serving tray, and set of 6 gold-rimmed tea glasses.', 460.00, 550.00, 18, 'prod_tea_set.svg', NOW()),
(15, 6, 'Gourmet Ras El Hanout Spice Blend (150g)', 'Artisanal blend of 27 premium aromatic whole spices stone-ground in Marrakech for authentic Moroccan culinary excellence.', 85.00, 110.00, 85, 'prod_ras_el_hanout.svg', NOW());

-- Insert Sample Orders
INSERT INTO `orders` (`id`, `user_id`, `total`, `status`, `customer_name`, `phone`, `address`, `city`, `created_at`) VALUES
(1001, 2, 830.00, 'processing', 'Karim Bennani', '+212 611-223344', 'Avenue Mohammed V 45', 'Rabat', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1002, NULL, 380.00, 'completed', 'Fatima Zahra Alami', '+212 661-889900', 'Quartier Guéliz, Rue de la Liberté 12', 'Marrakech', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1003, 2, 2400.00, 'pending', 'Karim Bennani', '+212 611-223344', 'Avenue Mohammed V 45', 'Rabat', DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Insert Sample Order Items
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1001, 1, 1, 180.00),
(2, 1001, 12, 1, 650.00),
(3, 1002, 8, 1, 380.00),
(4, 1003, 4, 1, 2400.00);

-- Insert Sample Contact Messages
INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Salma Idrissi', 'salma.idrissi@gmail.com', 'Custom Rug Dimensions', 'Hello MarocShop team, I would like to inquire whether it is possible to order a custom size Beni Ourain rug (3m x 4m) with custom geometric motifs. Thank you!', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 'Yassine Mansouri', 'yassine.m@yahoo.fr', 'Wholesale Cosmetic Argan', 'Bonjour, do you offer wholesale bulk prices for 100% pure certified cosmetic argan oil and prickly pear oil for our boutique in Casablanca?', DATE_SUB(NOW(), INTERVAL 3 DAY));
