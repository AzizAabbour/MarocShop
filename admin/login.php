<?php
/**
 * MarocShop - Dedicated Admin Login
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in() && is_admin()) {
    redirect('admin/index.php');
}

$errors = [];
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch. Please try again.";
    }

    $email = trim($email);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Please provide both admin email and password.";
    } else {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['role'] !== 'admin') {
                $errors[] = "Access denied: Account is not configured with administrator privileges.";
            } else {
                user_login($user);
                set_flash('success', "Welcome to the MarocShop Administration Portal.");
                redirect('admin/index.php');
            }
        } else {
            $errors[] = "Invalid credentials. Please verify your admin email and password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login | MarocShop</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body style="background:#0E0E0E; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;">

    <div style="background:#161616; border:1px solid rgba(201, 162, 39, 0.3); border-radius:16px; padding:45px; width:100%; max-width:440px; box-shadow:0 25px 50px rgba(0,0,0,0.5);">
        <div class="text-center" style="margin-bottom:30px;">
            <div class="brand-logo" style="justify-content:center; color:#FFFFFF; margin-bottom:10px;">
                <span class="logo-badge">M</span>arocShop
                <span class="tag">ADMIN</span>
            </div>
            <p style="color:#888888; font-size:0.9rem;">Sign in to manage your store, inventory, and orders.</p>
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

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <span><?= e($flash['message']) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('admin/login.php') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="email" style="color:#CCCCCC;">Admin Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="admin@marocshop.ma" required style="background:#222; border-color:#333; color:#FFF;">
            </div>

            <div class="form-group">
                <label class="form-label" for="password" style="color:#CCCCCC;">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required style="background:#222; border-color:#333; color:#FFF;">
            </div>

            <div style="margin-top:25px;">
                <button type="submit" class="btn btn-gold btn-block btn-lg">Access Dashboard &rarr;</button>
            </div>
        </form>

        <div style="margin-top:25px; padding-top:20px; border-top:1px solid #282828; text-align:center;">
            <a href="<?= url('index.php') ?>" style="color:#888888; font-size:0.85rem;">&larr; Back to Storefront</a>
        </div>
    </div>

</body>
</html>
