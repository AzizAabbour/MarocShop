<?php
/**
 * MarocShop - Add New Product
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$adminPageTitle = "Add New Product";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();
$categories = $db->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$errors = [];
$name = $_POST['name'] ?? '';
$categoryId = (int)($_POST['category_id'] ?? 0);
$price = $_POST['price'] ?? '';
$oldPrice = $_POST['old_price'] ?? '';
$stock = $_POST['stock'] ?? '10';
$description = $_POST['description'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token validation failed. Please try again.";
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
        $errors[] = "Please enter a valid selling price greater than 0.";
    }

    // Image Upload Handling
    $imageFilename = 'placeholder.svg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (!in_array($fileExt, $allowedExts)) {
            $errors[] = "Invalid image file format. Allowed formats: JPG, PNG, WEBP, SVG.";
        } else {
            $newImageName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $fileExt;
            $uploadTarget = __DIR__ . '/../assets/images/' . $newImageName;
            
            if (move_uploaded_file($fileTmp, $uploadTarget)) {
                $imageFilename = $newImageName;
            } else {
                $errors[] = "Failed to upload product image to server.";
            }
        }
    }

    if (empty($errors)) {
        $insertSql = "INSERT INTO products (category_id, name, description, price, old_price, stock, image, created_at) 
                      VALUES (:cat_id, :name, :description, :price, :old_price, :stock, :image, NOW())";
        $insertStmt = $db->prepare($insertSql);
        $insertStmt->execute([
            ':cat_id'      => $categoryId,
            ':name'        => $name,
            ':description' => $description,
            ':price'       => $price,
            ':old_price'   => $oldPrice,
            ':stock'       => $stock,
            ':image'       => $imageFilename
        ]);

        set_flash('success', "Product '{$name}' created successfully!");
        redirect('admin/products.php');
    }
}
?>

<div class="admin-form-card">
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Product Details</h2>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm">&larr; Back to Catalog</a>
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

            <form action="<?= url('admin/add-product.php') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Product Title *</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Organic Moroccan Argan Oil (100ml)" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Category *</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select category...</option>
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
                        <input type="number" step="0.01" name="price" id="price" class="form-control" value="<?= e($price) ?>" placeholder="180.00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="old_price">Original / Old Price (MAD) <small class="text-muted">(Optional discount)</small></label>
                        <input type="number" step="0.01" name="old_price" id="old_price" class="form-control" value="<?= e($oldPrice) ?>" placeholder="240.00">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Detailed Description</label>
                    <textarea name="description" id="description" class="form-control" rows="5" placeholder="Highlight Moroccan artisan heritage, materials, dimensions, and usage instructions..."><?= e($description) ?></textarea>
                </div>

                <!-- Product Image Upload with Live Preview -->
                <div class="form-group">
                    <label class="form-label" for="product_image">Product Image (JPG, PNG, WEBP, SVG)</label>
                    <input type="file" name="image" id="product_image" class="form-control admin-image-upload-input" data-preview="imgPreview" accept="image/*">
                    <div class="image-preview-box">
                        <img id="imgPreview" src="<?= asset('images/placeholder.svg') ?>" alt="Image Preview">
                    </div>
                </div>

                <div style="margin-top:30px; display:flex; gap:12px;">
                    <button type="submit" class="btn btn-gold btn-lg">Publish Product</button>
                    <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
