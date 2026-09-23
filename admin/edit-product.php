<?php
/**
 * MarocShop - Edit Existing Product
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$productId = (int)($_GET['id'] ?? 0);
if ($productId <= 0) {
    redirect('admin/products.php');
}

$db = get_db();

$stmt = $db->prepare("SELECT * FROM products WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Product not found.');
    redirect('admin/products.php');
}

$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$name = $_POST['name'] ?? $product['name'];
$categoryId = (int)($_POST['category_id'] ?? $product['category_id']);
$price = $_POST['price'] ?? $product['price'];
$oldPrice = isset($_POST['old_price']) ? $_POST['old_price'] : $product['old_price'];
$stock = $_POST['stock'] ?? $product['stock'];
$description = $_POST['description'] ?? $product['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch. Please try again.";
    }

    $name = trim($name);
    $price = (float)$price;
    $oldPrice = !empty($oldPrice) ? (float)$oldPrice : null;
    $stock = (int)$stock;
    $description = trim($description);

    if (empty($name)) {
        $errors[] = "Product name is required.";
    }

    if ($categoryId <= 0) {
        $errors[] = "Please select a category.";
    }

    if ($price <= 0) {
        $errors[] = "Selling price must be greater than 0.";
    }

    $imageFilename = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = "Invalid image file format. Allowed: JPG, PNG, WEBP, SVG.";
        } else {
            $newImageName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $uploadTarget = __DIR__ . '/../assets/images/' . $newImageName;
            
            if (move_uploaded_file($fileTmp, $uploadTarget)) {
                $imageFilename = $newImageName;
            } else {
                $errors[] = "Failed to upload image file.";
            }
        }
    }

    if (empty($errors)) {
        $upSql = "UPDATE products SET category_id = :cat_id, name = :name, description = :description, 
                  price = :price, old_price = :old_price, stock = :stock, image = :image WHERE id = :id";
        $upStmt = $db->prepare($upSql);
        $upStmt->execute([
            ':cat_id'      => $categoryId,
            ':name'        => $name,
            ':description' => $description,
            ':price'       => $price,
            ':old_price'   => $oldPrice,
            ':stock'       => $stock,
            ':image'       => $imageFilename,
            ':id'          => $productId
        ]);

        set_flash('success', "Product #{$productId} updated successfully!");
        redirect('admin/products.php');
    }
}

$adminPageTitle = "Edit Product #" . $productId;
require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-form-card">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Edit Product #<?= $productId ?></h2>
            <div style="display:flex; gap:8px;">
                <a href="<?= url('product.php?id=' . $productId) ?>" target="_blank" class="btn btn-outline btn-sm">👁️ View in Store</a>
                <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
            </div>
        </div>

        <div style="padding: 30px;">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin-left:15px; list-style-type:disc;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= e($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/edit-product.php?id=' . $productId) ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Product Title *</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= e($name) ?>" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category *</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= ($categoryId == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="stock">Inventory Stock Quantity *</label>
                        <input type="number" name="stock" id="stock" class="form-control" value="<?= e($stock) ?>" min="0" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="price">Selling Price (MAD) *</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= e($price) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="old_price">Original Price (MAD) <small class="text-muted">(Optional discount)</small></label>
                        <input type="number" step="0.01" name="old_price" id="old_price" class="form-control" value="<?= e($oldPrice) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Detailed Description</label>
                    <textarea name="description" id="description" class="form-control" rows="5"><?= e($description) ?></textarea>
                </div>

                <!-- Product Image Upload with Live Preview -->
                <div class="form-group">
                    <label class="form-label" for="product_image">Replace Product Image (Leave blank to keep current)</label>
                    <input type="file" name="image" id="product_image" class="form-control admin-image-upload-input" data-preview="editImgPreview" accept="image/*">
                    <div class="image-preview-box">
                        <img id="editImgPreview" src="<?= get_image_url($product['image']) ?>" alt="Current Product Image">
                    </div>
                </div>

                <div style="margin-top:30px; display:flex; gap:12px;">
                    <button type="submit" class="btn btn-gold btn-lg">Update Product</button>
                    <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
