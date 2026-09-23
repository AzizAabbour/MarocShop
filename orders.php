<?php
/**
 * MarocShop - Customer Orders History
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_login();
$user = current_user();
$db = get_db();

// Fetch customer orders with total item count
$stmt = $db->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.id = oi.order_id 
                      WHERE o.user_id = :user_id 
                      GROUP BY o.id 
                      ORDER BY o.id DESC");
$stmt->execute(['user_id' => $user['id']]);
$orders = $stmt->fetchAll();

$pageTitle = "My Orders";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Header -->
        <div class="flex-between" style="margin-bottom: 25px; flex-wrap:wrap; gap:12px;">
            <div>
                <h1 style="font-size:2.2rem; margin-bottom:4px;">My Order History</h1>
                <p class="text-muted">Track and review all your previous artisan orders.</p>
            </div>
            <a href="<?= url('profile.php') ?>" class="btn btn-outline btn-sm">&larr; Back to Account</a>
        </div>

        <?php if (empty($orders)): ?>
            <div style="background:var(--bg-card); padding:60px 20px; text-align:center; border-radius:var(--border-radius-lg); border:1px solid var(--border-color); max-width:600px; margin:0 auto;">
                <div style="font-size:3rem; margin-bottom:15px;">📦</div>
                <h2 style="font-size:1.5rem; margin-bottom:8px;">No Orders Yet</h2>
                <p class="text-muted" style="margin-bottom:20px;">You haven't placed any orders with MarocShop yet.</p>
                <a href="<?= url('shop.php') ?>" class="btn btn-gold">Explore Authentic Collection</a>
            </div>
        <?php else: ?>
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <strong style="color:var(--bg-dark); font-size:1rem;">#<?= $o['id'] ?></strong>
                                </td>
                                <td>
                                    <span style="font-size:0.9rem; color:var(--color-text-muted);">
                                        <?= date('M j, Y, g:i a', strtotime($o['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span><?= $o['item_count'] ?> item(s)</span>
                                </td>
                                <td>
                                    <strong style="font-size:1rem; color:var(--color-gold);"><?= format_price($o['total']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge badge-status badge-<?= e($o['status']) ?>">
                                        <?= ucfirst(e($o['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="<?= url('order-details.php?id=' . $o['id']) ?>" class="btn btn-outline btn-sm">
                                        View Invoice &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
