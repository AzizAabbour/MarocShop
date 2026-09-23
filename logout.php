<?php
/**
 * MarocShop - Logout Handler
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

user_logout();
set_flash('info', 'You have been successfully logged out.');
redirect('index.php');
