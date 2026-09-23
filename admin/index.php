<?php
/**
 * MarocShop - Admin Dashboard
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

$adminPageTitle = "Store Overview & Performance";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// 1. Fetch KPI Metrics
$totalProducts = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// 2. Fetch Recent Orders (Latest 6)
$recentOrders = [];
try {
    $stmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6");
    $recentOrders = $stmt->fetchAll();
} catch (Exception $e) {
    $recentOrders = [];
}

// 3. Fetch Low Stock Products (stock <= 10)
$lowStockProducts = [];
try {
    $lsStmt = $db->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= 10 ORDER BY p.stock ASC LIMIT 5");
    $lowStockProducts = $lsStmt->fetchAll();
} catch (Exception $e) {
    $lowStockProducts = [];
}
?>

<!-- KPI Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon revenue">💰</div>
        <div class="stat-info">
            <div class="stat-value"><?= format_price($totalRevenue) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orders">📦</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalOrders ?></div>
            <div class="stat-label">Total Orders</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon pending">⏳</div>
        <div class="stat-info">
            <div class="stat-value"><?= $pendingOrders ?></div>
            <div class="stat-label">Pending Orders</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon products">🛍️</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalProducts ?></div>
            <div class="stat-label">Total Products</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon users">👥</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalUsers ?></div>
            <div class="stat-label">Registered Customers</div>
        </div>
    </div>
</div>

<!-- Quick Action Shortcuts -->
<div style="display:flex; gap:12px; margin-bottom:30px; flex-wrap:wrap;">
    <a href="<?= url('admin/add-product.php') ?>" class="btn btn-gold btn-sm">➕ Add New Product</a>
    <a href="<?= url('admin/categories.php') ?>" class="btn btn-outline btn-sm">📂 Manage Categories</a>
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-outline btn-sm">📦 View All Orders</a>
</div>

<div style="display:grid; grid-template-columns: 1.5fr 1fr; gap:30px;" class="admin-dashboard-grid">
    <!-- Recent Orders Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Recent Customer Orders</h2>
            <a href="<?= url('admin/orders.php') ?>" class="btn btn-outline btn-sm">View All &rarr;</a>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr><td colspan="5" class="text-center text-muted" style="padding:30px;">No orders found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ro): ?>
                            <tr>
                                <td><strong>#<?= $ro['id'] ?></strong></td>
                                <td>
                                    <strong><?= e($ro['customer_name']) ?></strong><br>
                                    <small class="text-muted"><?= e($ro['city']) ?></small>
                                </td>
                                <td><strong><?= format_price($ro['total']) ?></strong></td>
                                <td>
                                    <span class="badge badge-status badge-<?= e($ro['status']) ?>">
                                        <?= ucfirst(e($ro['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?= url('admin/order-details.php?id=' . $ro['id']) ?>" class="btn btn-outline btn-sm">
                                        Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Inventory Alerts -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Low Stock Alerts</h2>
            <span class="badge badge-warning"><?= count($lowStockProducts) ?> Alert(s)</span>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lowStockProducts)): ?>
                        <tr><td colspan="3" class="text-center text-muted" style="padding:30px;">All product inventory is healthy.</td></tr>
                    <?php else: ?>
                        <?php foreach ($lowStockProducts as $lp): ?>
                            <tr>
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <img src="<?= get_image_url($lp['image']) ?>" alt="" class="table-img" style="width:36px; height:36px;">
                                        <div>
                                            <strong style="font-size:0.88rem;"><?= e($lp['name']) ?></strong><br>
                                            <small class="text-muted"><?= e($lp['category_name']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-danger" style="font-size:0.8rem;"><?= $lp['stock'] ?> left</span>
                                </td>
                                <td>
                                    <a href="<?= url('admin/edit-product.php?id=' . $lp['id']) ?>" class="btn btn-outline btn-sm">
                                        Restock
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
