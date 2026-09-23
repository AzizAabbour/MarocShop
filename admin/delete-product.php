<?php
/**
 * MarocShop - Delete Product Handler
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Security token mismatch. Could not delete product.');
        redirect('admin/products.php');
    }

    $productId = (int)($_POST['id'] ?? 0);
    if ($productId > 0) {
        $db = get_db();
        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute(['id' => $productId]);
            set_flash('success', "Product #{$productId} was permanently deleted.");
        } catch (Exception $e) {
            set_flash('error', "Could not delete product: " . $e->getMessage());
        }
    }
}

redirect('admin/products.php');
