<?php
/**
 * MarocShop - Customer Profile & Account Management
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

require_login();
$user = current_user();
$db = get_db();

$errors = [];
$successMessage = '';

$moroccanCities = [
    'Casablanca', 'Rabat', 'Marrakech', 'Tangier', 'Fes', 'Agadir', 
    'Meknes', 'Oujda', 'Kenitra', 'Tetouan', 'Safi', 'Mohammedia', 
    'El Jadida', 'Nador', 'Beni Mellal', 'Taza', 'Khemisset', 'Taroudant'
];

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch. Please try again.";
    }

    $formType = $_POST['form_type'] ?? 'info';

    if ($formType === 'info') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');

        if (empty($name)) {
            $errors[] = "Name cannot be empty.";
        }

        if (empty($errors)) {
            $upStmt = $db->prepare("UPDATE users SET name = :name, phone = :phone, address = :address, city = :city WHERE id = :id");
            $upStmt->execute([
                ':name'    => $name,
                ':phone'   => $phone,
                ':address' => $address,
                ':city'    => $city,
                ':id'      => $user['id']
            ]);
            $_SESSION['user_name'] = $name;
            set_flash('success', 'Profile details updated successfully.');
            redirect('profile.php');
        }
    } elseif ($formType === 'password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Verify current password
        $pStmt = $db->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
        $pStmt->execute(['id' => $user['id']]);
        $currHash = $pStmt->fetchColumn();

        if (!password_verify($currentPassword, $currHash)) {
            $errors[] = "Current password is incorrect.";
        } elseif (strlen($newPassword) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New passwords do not match.";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upPass = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            $upPass->execute([':pass' => $newHash, ':id' => $user['id']]);
            set_flash('success', 'Your password has been changed successfully.');
            redirect('profile.php');
        }
    }
}

// Fetch user statistics
$orderStats = $db->prepare("SELECT COUNT(*) AS total_orders, COALESCE(SUM(total), 0) AS total_spent FROM orders WHERE user_id = :user_id");
$orderStats->execute(['user_id' => $user['id']]);
$stats = $orderStats->fetch();

$pageTitle = "My Profile";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Account Header -->
        <div class="flex-between" style="margin-bottom: 30px; flex-wrap:wrap; gap:15px;">
            <div>
                <h1 style="font-size: 2.2rem; margin-bottom:4px;">Customer Account</h1>
                <p class="text-muted">Welcome back, <?= e($user['name']) ?>.</p>
            </div>
            <div style="display:flex; gap:10px;">
                <a href="<?= url('orders.php') ?>" class="btn btn-outline">📦 View My Orders</a>
                <a href="<?= url('logout.php') ?>" class="btn btn-outline" style="color:var(--color-danger); border-color:var(--color-danger);">Logout</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin-left:15px; list-style-type:disc;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= e($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; margin-bottom:35px;">
            <div style="background:var(--bg-card); padding:24px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); border-left:4px solid var(--color-gold);">
                <div style="font-size:0.85rem; color:var(--color-text-muted); text-transform:uppercase; font-weight:700;">Total Orders</div>
                <div style="font-size:1.8rem; font-weight:800; color:var(--bg-dark); margin-top:4px;"><?= $stats['total_orders'] ?> Orders</div>
            </div>
            <div style="background:var(--bg-card); padding:24px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); border-left:4px solid var(--color-gold);">
                <div style="font-size:0.85rem; color:var(--color-text-muted); text-transform:uppercase; font-weight:700;">Total Invested</div>
                <div style="font-size:1.8rem; font-weight:800; color:var(--bg-dark); margin-top:4px;"><?= format_price($stats['total_spent']) ?></div>
            </div>
            <div style="background:var(--bg-card); padding:24px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); border-left:4px solid var(--color-gold);">
                <div style="font-size:0.85rem; color:var(--color-text-muted); text-transform:uppercase; font-weight:700;">Member Since</div>
                <div style="font-size:1.4rem; font-weight:700; color:var(--bg-dark); margin-top:8px;"><?= date('M Y', strtotime($user['created_at'])) ?></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:30px;" class="profile-forms-grid">
            <!-- Edit Personal Info -->
            <div style="background:var(--bg-card); padding:32px; border-radius:var(--border-radius-md); border:1px solid var(--border-color);">
                <h3 style="font-size:1.3rem; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid var(--border-color);">
                    👤 Personal Information
                </h3>

                <form action="<?= url('profile.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_type" value="info">

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" value="<?= e($user['email']) ?>" disabled style="background:var(--bg-light-gray); cursor:not-allowed;">
                        <small class="text-muted" style="font-size:0.75rem;">Email address cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= e($user['name']) ?>" required>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="+212 600-000000">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="city">City</label>
                            <select name="city" id="city" class="form-control">
                                <option value="">Select city...</option>
                                <?php foreach ($moroccanCities as $c): ?>
                                    <option value="<?= e($c) ?>" <?= (($user['city'] ?? '') === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Delivery Address</label>
                        <textarea name="address" id="address" class="form-control" rows="3"><?= e($user['address'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold">Save Changes</button>
                </form>
            </div>

            <!-- Change Password -->
            <div style="background:var(--bg-card); padding:32px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); height:fit-content;">
                <h3 style="font-size:1.3rem; margin-bottom:20px; padding-bottom:10px; border-bottom:1px solid var(--border-color);">
                    🔒 Security & Password
                </h3>

                <form action="<?= url('profile.php') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_type" value="password">

                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-outline btn-block">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
