<?php
/**
 * MarocShop - Authentication & Cart Session Management
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Check if a user is currently logged in
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['user_id']) && !empty($_SESSION['user_role']);
}

/**
 * Get current logged in user array or null
 * @return array|null
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }

    static $currentUser = null;
    if ($currentUser !== null) {
        return $currentUser;
    }

    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT id, name, email, phone, address, city, role, created_at FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        if ($user) {
            $currentUser = $user;
            return $currentUser;
        } else {
            // User ID in session doesn't exist anymore in DB
            user_logout();
            return null;
        }
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Check if the logged-in user is an administrator
 * @return bool
 */
function is_admin(): bool {
    return is_logged_in() && ($_SESSION['user_role'] === 'admin');
}

/**
 * Require normal user authentication to access page
 * @param string|null $redirectAfter
 */
function require_login(?string $redirectAfter = null): void {
    if (!is_logged_in()) {
        $target = $redirectAfter ?? $_SERVER['REQUEST_URI'] ?? 'login.php';
        $_SESSION['redirect_after_login'] = $target;
        set_flash('error', 'Please log in to your account to continue.');
        redirect('login.php');
    }
}

/**
 * Require admin authentication to access admin page
 */
function require_admin(): void {
    if (!is_logged_in() || !is_admin()) {
        set_flash('error', 'Access denied. Administrator privileges required.');
        redirect('admin/login.php');
    }
}

/**
 * Log in a user by setting session variables securely
 * @param array $user
 */
function user_login(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

/**
 * Destroy user session and log out
 */
function user_logout(): void {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_role']);
}

/* ==========================================================
   SHOPPING CART SESSION MANAGEMENT
   ========================================================== */

/**
 * Initialize the cart if not exists
 */
function init_cart(): void {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

/**
 * Add a product to the cart
 * @param int $productId
 * @param int $quantity
 * @return bool
 */
function add_to_cart(int $productId, int $quantity = 1): bool {
    init_cart();
    if ($quantity < 1) $quantity = 1;

    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT id, name, price, stock FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product || $product['stock'] < 1) {
            return false;
        }

        $currentQty = $_SESSION['cart'][$productId] ?? 0;
        $newQty = $currentQty + $quantity;

        // Cap at available stock
        if ($newQty > $product['stock']) {
            $newQty = $product['stock'];
        }

        $_SESSION['cart'][$productId] = $newQty;
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Update quantity for a specific product in cart
 * @param int $productId
 * @param int $quantity
 * @return bool
 */
function update_cart_quantity(int $productId, int $quantity): bool {
    init_cart();
    
    if ($quantity <= 0) {
        remove_from_cart($productId);
        return true;
    }

    try {
        $db = get_db();
        $stmt = $db->prepare("SELECT stock FROM products WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch();

        if (!$product) {
            remove_from_cart($productId);
            return false;
        }

        if ($quantity > $product['stock']) {
            $quantity = $product['stock'];
        }

        $_SESSION['cart'][$productId] = $quantity;
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Remove an item from cart
 * @param int $productId
 */
function remove_from_cart(int $productId): void {
    init_cart();
    unset($_SESSION['cart'][$productId]);
}

/**
 * Empty the shopping cart
 */
function clear_cart(): void {
    $_SESSION['cart'] = [];
}

/**
 * Count total number of items in cart
 * @return int
 */
function get_cart_count(): int {
    init_cart();
    $totalCount = 0;
    foreach ($_SESSION['cart'] as $qty) {
        $totalCount += (int)$qty;
    }
    return $totalCount;
}

/**
 * Retrieve all items in cart with product details from database
 * @return array
 */
function get_cart_items(): array {
    init_cart();
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $productIds = array_keys($_SESSION['cart']);
    if (empty($productIds)) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $db = get_db();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id IN ($placeholders)");
    $stmt->execute($productIds);
    $products = $stmt->fetchAll();

    $cartItems = [];
    foreach ($products as $product) {
        $pid = $product['id'];
        $qty = $_SESSION['cart'][$pid] ?? 0;
        if ($qty > 0) {
            // Auto-adjust if stock is less than cart qty
            if ($qty > $product['stock']) {
                $qty = $product['stock'];
                $_SESSION['cart'][$pid] = $qty;
            }

            if ($qty > 0) {
                $subtotal = $product['price'] * $qty;
                $cartItems[] = [
                    'product'  => $product,
                    'quantity' => $qty,
                    'subtotal' => $subtotal
                ];
            }
        }
    }

    return $cartItems;
}

/**
 * Calculate cart subtotal
 * @return float
 */
function get_cart_subtotal(): float {
    $items = get_cart_items();
    $subtotal = 0.0;
    foreach ($items as $item) {
        $subtotal += $item['subtotal'];
    }
    return $subtotal;
}

/**
 * Calculate shipping cost
 * @param float $subtotal
 * @return float
 */
function get_shipping_cost(float $subtotal): float {
    if ($subtotal <= 0) return 0.0;
    return ($subtotal >= FREE_SHIPPING_THRESHOLD) ? 0.0 : SHIPPING_COST;
}

/**
 * Calculate total order price (subtotal + shipping)
 * @return float
 */
function get_cart_total(): float {
    $subtotal = get_cart_subtotal();
    if ($subtotal <= 0) return 0.0;
    return $subtotal + get_shipping_cost($subtotal);
}
