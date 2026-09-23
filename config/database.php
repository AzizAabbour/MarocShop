<?php
/**
 * MarocShop - Database Configuration & Core Helper Functions
 * Pure Native PHP 8+ PDO Connection
 */

// Prevent direct script execution if accessed from outside entry points
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Currency & Store Settings
define('STORE_NAME', 'MarocShop');
define('STORE_TAGLINE', 'Authentic Moroccan Treasures & Artisanal Luxury');
define('CURRENCY_SYMBOL', 'MAD');
define('SHIPPING_COST', 40.00); // Standard Moroccan domestic express shipping
define('FREE_SHIPPING_THRESHOLD', 500.00); // Free shipping over 500 MAD

/**
 * Returns the singleton PDO Database Connection
 * @return PDO
 */
function get_db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If the database doesn't exist, try to connect without dbname and provide helpful error
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                // Check if we can auto-create the ecommerce database
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `ecommerce` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (Exception $subEx) {
                die('<div style="font-family: sans-serif; padding: 30px; background: #fff3f3; color: #a94442; border: 1px solid #f2dede; border-radius: 8px; max-width: 650px; margin: 50px auto;">
                    <h2 style="margin-top:0;">Database Connection Error</h2>
                    <p>Could not connect to MySQL database <strong>' . DB_NAME . '</strong> on <strong>' . DB_HOST . '</strong>.</p>
                    <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                    <hr style="border:0; border-top:1px solid #ebccd1; margin:15px 0;">
                    <p>Please make sure MySQL is running in XAMPP and import <code>database/ecommerce.sql</code> via phpMyAdmin.</p>
                </div>');
            }
        }
    }

    return $pdo;
}

/**
 * Compute the dynamic application base URL based on current server context
 * @return string
 */
function get_base_url(): string {
    static $baseUrl = null;
    if ($baseUrl !== null) {
        return $baseUrl;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Determine script directory relative to document root
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    
    // If the script is executed inside an /admin subdirectory, trim /admin
    $scriptDir = preg_replace('#/admin(/.*)?$#i', '', $scriptDir);
    $scriptDir = rtrim($scriptDir, '/');

    $baseUrl = $protocol . $host . $scriptDir;
    return $baseUrl;
}

/**
 * Generate a complete application URL
 * @param string $path
 * @return string
 */
function url(string $path = ''): string {
    $base = get_base_url();
    $path = ltrim($path, '/');
    return empty($path) ? $base . '/' : $base . '/' . $path;
}

/**
 * Generate asset URL
 * @param string $path
 * @return string
 */
function asset(string $path = ''): string {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Resolve product/category image path with fallback
 * @param string|null $imageName
 * @param string $type
 * @return string
 */
function get_image_url(?string $imageName, string $type = 'product'): string {
    if (!empty($imageName)) {
        // If it's a full URL or stored in assets/images/
        if (str_starts_with($imageName, 'http://') || str_starts_with($imageName, 'https://')) {
            return $imageName;
        }
        
        $localPath = __DIR__ . '/../assets/images/' . $imageName;
        if (file_exists($localPath)) {
            return asset('images/' . $imageName);
        }
    }
    
    return asset('images/placeholder.svg');
}

/**
 * Sanitize string for HTML output
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format price in Moroccan Dirhams
 * @param float|int|string $amount
 * @return string
 */
function format_price($amount): string {
    $num = (float)$amount;
    return number_format($num, 2, '.', ' ') . ' ' . CURRENCY_SYMBOL;
}

/**
 * Generate and return CSRF token
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden CSRF token input field
 * @return string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verify submitted CSRF token
 * @param string|null $token
 * @return bool
 */
function verify_csrf(?string $token = null): bool {
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Flash message helper (stores a toast or notification in session)
 * @param string $type 'success' | 'error' | 'warning' | 'info'
 * @param string $message
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash_message'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Retrieve and clear flash message
 * @return array|null
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Redirect helper
 * @param string $path
 */
function redirect(string $path): void {
    if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
        $path = url($path);
    }
    header("Location: " . $path);
    exit;
}
