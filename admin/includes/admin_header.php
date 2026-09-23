<?php
/**
 * MarocShop - Admin Panel Header & Sidebar Navigation with Radix UI Icons
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/icons.php';

require_admin();
$adminUser = current_user();

$currentScript = basename($_SERVER['PHP_SELF']);
$adminPageTitle = $adminPageTitle ?? 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle) ?> | Hayz Admin</title>
    
    <!-- Fonts & Admin CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <img src="<?= asset('logo/logo.png') ?>" alt="Hayz" style="height:34px; width:auto; object-fit:contain;">
            <span style="font-size:1.1rem; font-weight:800; letter-spacing:-0.02em;">Hayz <small style="font-size:0.65rem; background:var(--admin-gold); color:#111; padding:2px 6px; border-radius:4px; margin-left:4px; font-weight:800;">ADMIN</small></span>
        </div>

        <nav class="admin-nav">
            <a href="<?= url('admin/index.php') ?>" class="admin-nav-item <?= ($currentScript === 'index.php') ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('dashboard', '', 16) ?></span>
                <span>Dashboard</span>
            </a>
            <a href="<?= url('admin/products.php') ?>" class="admin-nav-item <?= (in_array($currentScript, ['products.php', 'add-product.php', 'edit-product.php'])) ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('backpack', '', 16) ?></span>
                <span>Products</span>
            </a>
            <a href="<?= url('admin/categories.php') ?>" class="admin-nav-item <?= ($currentScript === 'categories.php') ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('layers', '', 16) ?></span>
                <span>Categories</span>
            </a>
            <a href="<?= url('admin/orders.php') ?>" class="admin-nav-item <?= (in_array($currentScript, ['orders.php', 'order-details.php'])) ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('cube', '', 16) ?></span>
                <span>Orders</span>
            </a>
            <a href="<?= url('admin/users.php') ?>" class="admin-nav-item <?= ($currentScript === 'users.php') ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('person', '', 16) ?></span>
                <span>Customers</span>
            </a>
            <a href="<?= url('admin/messages.php') ?>" class="admin-nav-item <?= ($currentScript === 'messages.php') ? 'active' : '' ?>">
                <span class="icon"><?= radix_icon('envelope-closed', '', 16) ?></span>
                <span>Messages</span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-outline-gold btn-sm btn-block" style="margin-bottom:8px;">
                <?= radix_icon('eye-open', '', 14) ?> View Storefront
            </a>
            <a href="<?= url('logout.php') ?>" class="btn btn-outline btn-sm btn-block" style="color:#ef4444; border-color:rgba(239, 68, 68, 0.3);">
                <?= radix_icon('exit', '', 14) ?> Logout
            </a>
        </div>
    </aside>

    <!-- Main Container -->
    <div class="admin-main">
        <!-- Topbar -->
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button class="sidebar-toggle-btn" id="adminSidebarToggle" aria-label="Toggle Navigation">☰</button>
                <h1 class="admin-page-title"><?= e($adminPageTitle) ?></h1>
            </div>

            <div class="admin-topbar-right">
                <div class="admin-user-info">
                    <div class="admin-avatar"><?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 1)) ?></div>
                    <div>
                        <strong style="font-size:0.88rem; display:block; line-height:1.2;"><?= e($adminUser['name']) ?></strong>
                        <small style="color:var(--admin-text-muted); font-size:0.75rem;">Administrator</small>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="admin-content">
            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>" style="margin-bottom: 25px;">
                    <?= ($flash['type'] === 'success') ? radix_icon('check-circled', '', 18) : radix_icon('cross-1', '', 18) ?>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endif; ?>
