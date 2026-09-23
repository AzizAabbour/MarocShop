<?php
/**
 * MarocShop - Customer Registration
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('profile.php');
}

$errors = [];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$city = $_POST['city'] ?? 'Casablanca';

$moroccanCities = [
    'Casablanca', 'Rabat', 'Marrakech', 'Tangier', 'Fes', 'Agadir', 
    'Meknes', 'Oujda', 'Kenitra', 'Tetouan', 'Safi', 'Mohammedia', 
    'El Jadida', 'Nador', 'Beni Mellal', 'Taza', 'Khemisset', 'Taroudant'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token mismatch. Please try again.";
    }

    $name = trim($name);
    $email = trim($email);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors[] = "Please enter your full name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $db = get_db();
        
        // Check if email already exists
        $checkStmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $checkStmt->execute(['email' => $email]);
        if ($checkStmt->fetch()) {
            $errors[] = "This email address is already registered. Please sign in.";
        } else {
            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $db->prepare("INSERT INTO users (name, email, password, phone, address, city, role, created_at) 
                                        VALUES (:name, :email, :password, :phone, :address, :city, 'customer', NOW())");
            $insertStmt->execute([
                ':name'     => $name,
                ':email'    => $email,
                ':password' => $hashedPassword,
                ':phone'    => trim($phone),
                ':address'  => trim($address),
                ':city'     => trim($city)
            ]);

            $newUserId = (int)$db->lastInsertId();

            // Auto login
            user_login([
                'id'    => $newUserId,
                'name'  => $name,
                'email' => $email,
                'role'  => 'customer'
            ]);

            set_flash('success', "Welcome to MarocShop, {$name}! Your account has been created successfully.");
            redirect('profile.php');
        }
    }
}

$pageTitle = "Create an Account";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <div class="auth-card" style="max-width: 580px;">
            <div class="text-center" style="margin-bottom: 25px;">
                <span class="badge badge-gold" style="margin-bottom:8px;">Join MarocShop</span>
                <h1 style="font-size: 1.9rem; margin-bottom: 6px;">Create Customer Account</h1>
                <p class="text-muted" style="font-size:0.9rem;">Enjoy fast checkouts, tracking, and artisan privileges.</p>
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

            <form action="<?= url('register.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Fatima Zahra Alami" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. fatima@example.com" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" name="phone" id="phone" class="form-control" value="<?= e($phone) ?>" placeholder="+212 600-000000">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City</label>
                        <select name="city" id="city" class="form-control">
                            <?php foreach ($moroccanCities as $c): ?>
                                <option value="<?= e($c) ?>" <?= ($city === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">Address (Optional)</label>
                    <input type="text" name="address" id="address" class="form-control" value="<?= e($address) ?>" placeholder="Street, Neighborhood...">
                </div>

                <div style="margin-top:25px;">
                    <button type="submit" class="btn btn-gold btn-block btn-lg">Create Account &rarr;</button>
                </div>
            </form>

            <div style="margin-top: 25px; padding-top:20px; border-top:1px solid var(--border-color); text-align:center; font-size:0.92rem;">
                Already have an account? <a href="<?= url('login.php') ?>" style="color:var(--color-gold); font-weight:700;">Sign In Here</a>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
