<?php
/**
 * MarocShop - Admin Category Management
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$adminPageTitle = "Category Management";
require_once __DIR__ . '/includes/admin_header.php';

$db = get_db();
$errors = [];

// Handle Category Creation or Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch.";
    }

    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $catId = (int)($_POST['id'] ?? 0);

    if ($action === 'create' || $action === 'update') {
        if (empty($name)) {
            $errors[] = "Category name is required.";
        }

        $imageName = 'placeholder.svg';
        if ($action === 'update') {
            $currStmt = $db->prepare("SELECT image FROM categories WHERE id = :id");
            $currStmt->execute(['id' => $catId]);
            $imageName = $currStmt->fetchColumn() ?: 'placeholder.svg';
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['image']['tmp_name'];
            $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                $newImg = 'cat_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $fileExt;
                if (move_uploaded_file($fileTmp, __DIR__ . '/../assets/images/' . $newImg)) {
                    $imageName = $newImg;
                }
            }
        }

        if (empty($errors)) {
            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO categories (name, description, image, created_at) VALUES (:name, :description, :image, NOW())");
                $stmt->execute([':name' => $name, ':description' => $description, ':image' => $imageName]);
                set_flash('success', "Category '{$name}' created successfully!");
            } else {
                $stmt = $db->prepare("UPDATE categories SET name = :name, description = :description, image = :image WHERE id = :id");
                $stmt->execute([':name' => $name, ':description' => $description, ':image' => $imageName, ':id' => $catId]);
                set_flash('success', "Category #{$catId} updated successfully!");
            }
            redirect('admin/categories.php');
        }
    } elseif ($action === 'delete' && $catId > 0) {
        try {
            $stmt = $db->prepare("DELETE FROM categories WHERE id = :id");
            $stmt->execute(['id' => $catId]);
            set_flash('success', "Category #{$catId} deleted successfully.");
        } catch (Exception $e) {
            set_flash('error', "Could not delete category: " . $e->getMessage());
        }
        redirect('admin/categories.php');
    }
}

// Fetch all categories with product count
$categories = $db->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.id ASC")->fetchAll();

// Check if in edit mode
$editCategory = null;
if (!empty($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($categories as $c) {
        if ($c['id'] == $editId) {
            $editCategory = $c;
            break;
        }
    }
}
?>

<div style="display:grid; grid-template-columns: 1fr 1.5fr; gap:30px;" class="admin-dashboard-grid">
    <!-- Category Form (Add or Edit) -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title"><?= $editCategory ? 'Edit Category #' . $editCategory['id'] : 'Add New Category' ?></h2>
            <?php if ($editCategory): ?>
                <a href="<?= url('admin/categories.php') ?>" class="btn btn-outline btn-sm">Cancel Edit</a>
            <?php endif; ?>
        </div>

        <div style="padding: 24px;">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul style="margin-left:15px; list-style-type:disc;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= e($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="<?= url('admin/categories.php') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="<?= $editCategory ? 'update' : 'create' ?>">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?= $editCategory['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="name">Category Name *</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= e($editCategory['name'] ?? '') ?>" placeholder="e.g. Traditional Caftans & Fashion" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief summary of category products..."><?= e($editCategory['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat_image">Category Visual / Icon (SVG, PNG, JPG)</label>
                    <input type="file" name="image" id="cat_image" class="form-control admin-image-upload-input" data-preview="catPreview" accept="image/*">
                    <div class="image-preview-box" style="width:100px; height:100px;">
                        <img id="catPreview" src="<?= get_image_url($editCategory['image'] ?? 'placeholder.svg', 'category') ?>" alt="Category Preview">
                    </div>
                </div>

                <button type="submit" class="btn btn-gold btn-block">
                    <?= $editCategory ? 'Update Category' : 'Create Category' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Category List Table -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">All Categories (<?= count($categories) ?>)</h2>
        </div>

        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Products</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="4" class="text-center text-muted" style="padding:30px;">No categories created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <img src="<?= get_image_url($c['image'], 'category') ?>" alt="" class="table-img" style="width:40px; height:40px; border-radius:50%;">
                                </td>
                                <td>
                                    <strong><?= e($c['name']) ?></strong><br>
                                    <small class="text-muted"><?= e(substr($c['description'] ?? '', 0, 45)) ?>...</small>
                                </td>
                                <td>
                                    <span class="badge badge-gold"><?= $c['product_count'] ?> products</span>
                                </td>
                                <td style="text-align:right;">
                                    <div class="action-buttons" style="justify-content:flex-end;">
                                        <a href="<?= url('admin/categories.php?edit=' . $c['id']) ?>" class="btn-icon" title="Edit Category">✏️</a>
                                        <form action="<?= url('admin/categories.php') ?>" method="POST" class="confirm-delete-form" data-item="<?= e($c['name']) ?>" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                            <button type="submit" class="btn-icon delete" title="Delete Category">🗑️</button>
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
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
