<?php
/**
 * MarocShop - Contact Us & Customer Support
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];
$success = false;

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = "Security token verification failed. Please try again.";
    }

    $name = trim($name);
    $email = trim($email);
    $subject = trim($subject);
    $message = trim($message);

    if (empty($name)) {
        $errors[] = "Please enter your name.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please provide a valid email address.";
    }

    if (empty($subject)) {
        $errors[] = "Please specify a subject for your inquiry.";
    }

    if (empty($message)) {
        $errors[] = "Please type your message.";
    }

    if (empty($errors)) {
        $db = get_db();
        try {
            $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:name, :email, :subject, :message, NOW())");
            $stmt->execute([
                ':name'    => $name,
                ':email'   => $email,
                ':subject' => $subject,
                ':message' => $message
            ]);

            set_flash('success', 'Your message has been sent successfully! Our team will respond within 24 hours.');
            redirect('contact.php');
        } catch (Exception $e) {
            $errors[] = "Failed to submit message: " . $e->getMessage();
        }
    }
}

$pageTitle = "Contact Us & Support";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="section-spacing-sm">
    <div class="container">
        <!-- Header -->
        <div class="text-center" style="margin-bottom: 45px;">
            <span class="section-subtitle">We Are Here To Assist You</span>
            <h1 class="section-title">Get in Touch with MarocShop</h1>
            <div class="section-divider"></div>
        </div>

        <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:40px; align-items:flex-start;" class="contact-grid">
            <!-- Contact Form -->
            <div style="background:var(--bg-card); padding:35px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); box-shadow:var(--shadow-subtle);">
                <h2 style="font-size:1.4rem; margin-bottom:20px;">Send Us a Message</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul style="margin-left:15px; list-style-type:disc;">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= url('contact.php') ?>" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="name">Your Name *</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?= e($name) ?>" placeholder="e.g. Yassine Mansouri" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address *</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= e($email) ?>" placeholder="e.g. yassine@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">Subject *</label>
                        <input type="text" name="subject" id="subject" class="form-control" value="<?= e($subject) ?>" placeholder="e.g. Custom Beni Ourain Rug Inquiry" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Your Message *</label>
                        <textarea name="message" id="message" class="form-control" rows="5" placeholder="How can our Moroccan artisan specialists assist you today?" required><?= e($message) ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-gold btn-lg">Send Message &rarr;</button>
                </form>
            </div>

            <!-- Store Locations & Details -->
            <div style="display:flex; flex-direction:column; gap:20px;">
                <!-- Main Atelier Casablanca -->
                <div style="background:var(--bg-card); padding:26px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); border-left:4px solid var(--color-gold);">
                    <h3 style="font-size:1.15rem; margin-bottom:10px; color:var(--bg-dark);">🏛️ Casablanca Boutique & Showroom</h3>
                    <div style="font-size:0.92rem; color:var(--color-text-muted); line-height:1.7;">
                        123 Boulevard d'Anfa, 3rd Floor<br>
                        Casablanca 20000, Morocco<br>
                        📞 <strong>+212 522-001122</strong>
                    </div>
                </div>

                <!-- Marrakech Workshop -->
                <div style="background:var(--bg-card); padding:26px; border-radius:var(--border-radius-md); border:1px solid var(--border-color); border-left:4px solid var(--color-gold);">
                    <h3 style="font-size:1.15rem; margin-bottom:10px; color:var(--bg-dark);">🏺 Marrakech Artisan Atelier</h3>
                    <div style="font-size:0.92rem; color:var(--color-text-muted); line-height:1.7;">
                        45 Rue de la Liberté, Quartier Guéliz<br>
                        Marrakech 40000, Morocco<br>
                        📞 <strong>+212 524-334455</strong>
                    </div>
                </div>

                <!-- Operating Hours & Delivery -->
                <div style="background:var(--bg-card); padding:26px; border-radius:var(--border-radius-md); border:1px solid var(--border-color);">
                    <h3 style="font-size:1.15rem; margin-bottom:10px; color:var(--bg-dark);">⏱️ Customer Service Hours</h3>
                    <div style="font-size:0.92rem; color:var(--color-text-muted); line-height:1.7;">
                        <strong>Monday – Saturday:</strong> 9:00 AM – 8:00 PM (GMT+1)<br>
                        <strong>Sunday:</strong> 10:00 AM – 4:00 PM<br>
                        ✉️ <strong>support@marocshop.ma</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
