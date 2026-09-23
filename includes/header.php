<?php
/**
 * Hayz - HTML Header Component
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = isset($pageTitle) ? e($pageTitle) . ' | ' . STORE_NAME : STORE_NAME . ' | ' . STORE_TAGLINE;
$pageDescription = $pageDescription ?? 'Discover authentic Moroccan handcrafted treasures: pure organic Argan oil, Beni Ourain wool rugs, Fez ceramics, handcrafted leather goods, and royal caftans.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <title><?= $pageTitle ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('logo/logo.png') ?>">
    
    <!-- Google Fonts Preconnect & Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Vanilla CSS -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
