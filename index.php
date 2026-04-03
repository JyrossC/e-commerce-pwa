<?php
/**
 * NovaMart — Main Entry Point
 * Redirects to the shop page.
 */
require_once __DIR__ . '/config/config.php';
header('Location: ' . APP_URL . '/pages/shop.php');
exit;
