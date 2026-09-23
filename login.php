<?php
/**
 * MarocShop - Customer & User Login
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    }
    redirect('profile.php');
}

$errors = [];
$email = $_POST['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Invalid session token. Please try again.";
    }

    $email = trim($email);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errors[] = "Please enter both email and password.";
    } else {
        $db = get_db();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            user_login($user);

            set_flash('success', "Welcome back, " . htmlspecialchars($user['name']) . "!");

            // Check if there was a redirected target
            $redirectTarget = $_SESSION['redirect_after_login'] ?? '';
            unset($_SESSION['redirect_after_login']);

            if (!empty($redirectTarget)) {
                redirect($redirectTarget);
            }

            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('profile.php');
            }
        } else {
            $errors[] = "Invalid email or password combination.";
        }
    }
}

$pageTitle = "Login to Your Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <div class="auth-card">
            <div class="text-center" style="margin-bottom: 25px;">
                <span class="badge badge-gold" style="margin-bottom:8px;">Welcome Back</span>
                <h1 style="font-size: 1.9rem; margin-bottom: 6px;">Account Login</h1>
                <p class="text-muted" style="font-size:0.9rem;">Sign in to access your orders, profile, and wishlists.
                </p>
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

            <form action="<?= url('login.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>"
                        placeholder="e.g. karim@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••"
                        required>
                </div>

                <div style="margin-top:25px;">
                    <button type="submit" class="btn btn-gold btn-block btn-lg">Sign In &rarr;</button>
                </div>
            </form>

            <div
                style="margin-top: 25px; padding-top:20px; border-top:1px solid var(--border-color); text-align:center; font-size:0.92rem;">
                Don't have an account yet? <a href="<?= url('register.php') ?>"
                    style="color:var(--color-gold); font-weight:700;">Create an Account</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>