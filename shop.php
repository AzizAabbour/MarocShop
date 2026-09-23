<?php
/**
 * MarocShop - Product Catalog & Shop Page
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$db = get_db();

// Filter parameters
$search = trim($_GET['search'] ?? '');
$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 9;
$offset = ($page - 1) * $limit;

// Fetch all categories with counts for the sidebar filter
$categories = [];
try {
    $catStmt = $db->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC");
    $categories = $catStmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

// Build dynamic WHERE clause
$whereClauses = ["1=1"];
$params = [];

if (!empty($search)) {
    $whereClauses[] = "(p.name LIKE :search OR p.description LIKE :search OR c.name LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

if ($categoryId > 0) {
    $whereClauses[] = "p.category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

if ($minPrice > 0) {
    $whereClauses[] = "p.price >= :min_price";
    $params[':min_price'] = $minPrice;
}

if ($maxPrice > 0) {
    $whereClauses[] = "p.price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

$whereSql = implode(" AND ", $whereClauses);

// Determine Order By
$orderBySql = "p.id DESC"; // default newest
if ($sort === 'price_low') {
    $orderBySql = "p.price ASC";
} elseif ($sort === 'price_high') {
    $orderBySql = "p.price DESC";
} elseif ($sort === 'name_asc') {
    $orderBySql = "p.name ASC";
}

// Count total matching products for pagination
$totalCount = 0;
try {
    $countSql = "SELECT COUNT(DISTINCT p.id) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereSql";
    $countStmt = $db->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = (int)$countStmt->fetchColumn();
} catch (Exception $e) {
    $totalCount = 0;
}

$totalPages = max(1, ceil($totalCount / $limit));

// Fetch paginated products
$products = [];
try {
    $prodSql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereSql ORDER BY $orderBySql LIMIT :limit OFFSET :offset";
    $prodStmt = $db->prepare($prodSql);
    
    // Bind all dynamic filters
    foreach ($params as $key => $val) {
        $prodStmt->bindValue($key, $val);
    }
    $prodStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $prodStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $prodStmt->execute();
    $products = $prodStmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}

// Find active category name
$activeCategoryName = 'All Products';
if ($categoryId > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $activeCategoryName = $cat['name'];
            break;
        }
    }
}

$pageTitle = "Shop Catalog - " . $activeCategoryName;
$pageDescription = "Browse Moroccan handcrafted treasures, pure Argan beauty oils, ceramics, rugs, leather, and caftans.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Breadcrumb & Header -->
        <div style="margin-bottom: 25px;">
            <div style="font-size:0.88rem; color:var(--color-text-muted); margin-bottom:8px;">
                <a href="<?= url('index.php') ?>">Home</a> / 
                <a href="<?= url('shop.php') ?>">Shop</a> 
                <?php if ($categoryId > 0): ?>
                    / <span><?= e($activeCategoryName) ?></span>
                <?php endif; ?>
                <?php if (!empty($search)): ?>
                    / <span>Search: "<?= e($search) ?>"</span>
                <?php endif; ?>
            </div>
            <h1 style="font-size:2.2rem;"><?= e($activeCategoryName) ?></h1>
            <p class="text-muted" style="font-size:0.95rem;">Showing <?= count($products) ?> of <?= $totalCount ?> authentic Moroccan items</p>
        </div>

        <div class="shop-layout">
            <!-- Sidebar Filters -->
            <aside class="shop-sidebar">
                <!-- Search Filter Form -->
                <div class="filter-group">
                    <h3 class="filter-title">Search</h3>
                    <form action="<?= url('shop.php') ?>" method="GET" style="display:flex; gap:6px;">
                        <?php if ($categoryId > 0): ?>
                            <input type="hidden" name="category_id" value="<?= $categoryId ?>">
                        <?php endif; ?>
                        <input type="text" name="search" class="form-control" placeholder="Keywords..." value="<?= e($search) ?>" style="padding:8px 12px; font-size:0.88rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Go</button>
                    </form>
                </div>

                <!-- Category Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Categories</h3>
                    <ul class="category-filter-list">
                        <li class="category-filter-item <?= ($categoryId === 0) ? 'active' : '' ?>">
                            <a href="<?= url('shop.php' . (!empty($search) ? '?search='.urlencode($search) : '')) ?>">
                                <span>All Categories</span>
                                <small>(<?= array_sum(array_column($categories, 'product_count')) ?>)</small>
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li class="category-filter-item <?= ($categoryId == $cat['id']) ? 'active' : '' ?>">
                                <a href="<?= url('shop.php?category_id=' . $cat['id'] . (!empty($search) ? '&search='.urlencode($search) : '')) ?>">
                                    <span><?= e($cat['name']) ?></span>
                                    <small>(<?= $cat['product_count'] ?>)</small>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price Filter -->
                <div class="filter-group">
                    <h3 class="filter-title">Filter by Price</h3>
                    <form action="<?= url('shop.php') ?>" method="GET">
                        <?php if ($categoryId > 0): ?><input type="hidden" name="category_id" value="<?= $categoryId ?>"><?php endif; ?>
                        <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                        <?php if (!empty($sort)): ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><?php endif; ?>
                        
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px; margin-bottom:12px;">
                            <div>
                                <label style="font-size:0.78rem; color:var(--color-text-muted);">Min (MAD)</label>
                                <input type="number" name="min_price" class="form-control" value="<?= $minPrice > 0 ? $minPrice : '' ?>" placeholder="0" style="padding:6px 10px; font-size:0.85rem;">
                            </div>
                            <div>
                                <label style="font-size:0.78rem; color:var(--color-text-muted);">Max (MAD)</label>
                                <input type="number" name="max_price" class="form-control" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>" placeholder="3000" style="padding:6px 10px; font-size:0.85rem;">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-gold btn-sm btn-block">Apply Price</button>
                    </form>
                </div>

                <!-- Reset Filters Button -->
                <?php if ($categoryId > 0 || !empty($search) || $minPrice > 0 || $maxPrice > 0): ?>
                    <a href="<?= url('shop.php') ?>" class="btn btn-outline btn-sm btn-block" style="margin-top:10px;">Clear All Filters</a>
                <?php endif; ?>
            </aside>

            <!-- Products Column -->
            <div>
                <!-- Shop Top Bar / Sorting Toolbar -->
                <div class="shop-toolbar">
                    <div style="font-size:0.9rem; color:var(--color-text-muted);">
                        <?php if (!empty($search)): ?>
                            Results for <strong style="color:var(--bg-dark);">"<?= e($search) ?>"</strong>
                        <?php else: ?>
                            Showing page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong>
                        <?php endif; ?>
                    </div>

                    <!-- Sort Dropdown -->
                    <form action="<?= url('shop.php') ?>" method="GET" id="sortForm" style="display:flex; align-items:center; gap:8px;">
                        <?php if ($categoryId > 0): ?><input type="hidden" name="category_id" value="<?= $categoryId ?>"><?php endif; ?>
                        <?php if (!empty($search)): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                        <?php if ($minPrice > 0): ?><input type="hidden" name="min_price" value="<?= $minPrice ?>"><?php endif; ?>
                        <?php if ($maxPrice > 0): ?><input type="hidden" name="max_price" value="<?= $maxPrice ?>"><?php endif; ?>
                        
                        <label for="sortSelect" style="font-size:0.88rem; font-weight:600;">Sort By:</label>
                        <select name="sort" id="sortSelect" class="shop-sorting-select" onchange="document.getElementById('sortForm').submit();">
                            <option value="newest" <?= ($sort === 'newest') ? 'selected' : '' ?>>Newest Arrivals</option>
                            <option value="price_low" <?= ($sort === 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= ($sort === 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>Product Name (A-Z)</option>
                        </select>
                    </form>
                </div>

                <!-- Product Grid -->
                <?php if (empty($products)): ?>
                    <div style="background:var(--bg-card); padding:60px; text-align:center; border-radius:var(--border-radius-md); border:1px solid var(--border-color);">
                        <div style="font-size:3rem; margin-bottom:15px;">🔍</div>
                        <h3 style="font-size:1.4rem; margin-bottom:10px;">No Products Found</h3>
                        <p class="text-muted" style="margin-bottom:20px;">We couldn't find any products matching your filter criteria.</p>
                        <a href="<?= url('shop.php') ?>" class="btn btn-gold">Reset Catalog</a>
                    </div>
                <?php else: ?>
                    <div class="products-grid">
                        <?php foreach ($products as $product): ?>
                            <?php include __DIR__ . '/includes/product_card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $queryParams = $_GET;
                            ?>
                            <?php if ($page > 1): ?>
                                <?php $queryParams['page'] = $page - 1; ?>
                                <a href="<?= url('shop.php?' . http_build_query($queryParams)) ?>" class="page-link">&laquo; Prev</a>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php $queryParams['page'] = $i; ?>
                                <a href="<?= url('shop.php?' . http_build_query($queryParams)) ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <?php $queryParams['page'] = $page + 1; ?>
                                <a href="<?= url('shop.php?' . http_build_query($queryParams)) ?>" class="page-link">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
