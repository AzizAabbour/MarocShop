<?php
/**
 * MarocShop - Admin Product Management with Radix UI Icons
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/icons.php';

$adminPageTitle = "Product Catalog Management";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();

// Filters
$search = trim($_GET['search'] ?? '');
$categoryId = (int)($_GET['category_id'] ?? 0);

$where = ["1=1"];
$params = [];

if (!empty($search)) {
    $where[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($categoryId > 0) {
    $where[] = "p.category_id = :cat_id";
    $params[':cat_id'] = $categoryId;
}

$whereSql = implode(" AND ", $where);

// Fetch categories for filter dropdown
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

// Fetch products
$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereSql ORDER BY p.id DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<!-- Products Header Bar -->
<div class="flex-between" style="margin-bottom:25px; flex-wrap:wrap; gap:15px;">
    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
        <form action="<?= url('admin/products.php') ?>" method="GET" style="display:flex; gap:8px;">
            <input type="text" name="search" class="form-control" placeholder="Search product name..." value="<?= e($search) ?>" style="padding:8px 14px; width:220px;">
            <select name="category_id" class="form-control" style="padding:8px 14px; width:180px;">
                <option value="0">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">
                <?= radix_icon('magnifying-glass', '', 14) ?> Filter
            </button>
            <?php if (!empty($search) || $categoryId > 0): ?>
                <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <a href="<?= url('admin/add-product.php') ?>" class="btn btn-gold">
        <?= radix_icon('plus', '', 15) ?> Add New Product
    </a>
</div>

<!-- Product Table -->
<div class="admin-card">
    <div class="admin-card-header">
        <h2 class="admin-card-title">All Products (<?= count($products) ?>)</h2>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Old Price</th>
                    <th>Stock</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding:40px;">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <img src="<?= get_image_url($p['image']) ?>" alt="<?= e($p['name']) ?>" class="table-img">
                            </td>
                            <td>
                                <strong style="font-size:0.92rem;"><?= e($p['name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-gold"><?= e($p['category_name']) ?></span>
                            </td>
                            <td>
                                <strong><?= format_price($p['price']) ?></strong>
                            </td>
                            <td>
                                <?= !empty($p['old_price']) ? format_price($p['old_price']) : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td>
                                <?php if ($p['stock'] <= 0): ?>
                                    <span class="badge badge-danger">Out of stock (0)</span>
                                <?php elseif ($p['stock'] <= 10): ?>
                                    <span class="badge badge-warning">Low: <?= $p['stock'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-stock"><?= $p['stock'] ?> units</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;">
                                <div class="action-buttons" style="justify-content:flex-end;">
                                    <a href="<?= url('product.php?id=' . $p['id']) ?>" target="_blank" class="btn-icon" title="View in Store">
                                        <?= radix_icon('eye-open', '', 14) ?>
                                    </a>
                                    <a href="<?= url('admin/edit-product.php?id=' . $p['id']) ?>" class="btn-icon" title="Edit Product">
                                        <?= radix_icon('pencil', '', 14) ?>
                                    </a>
                                    
                                    <form action="<?= url('admin/delete-product.php') ?>" method="POST" class="confirm-delete-form" data-item="<?= e($p['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn-icon delete" title="Delete Product">
                                            <?= radix_icon('trash', '', 14) ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
