<?php
/**
 * Configuration file for Blockchain Lab Logger
 * 
 * IMPORTANT: Copy this file to config.php and set your SECRET_KEY
 * Do not commit config.php to version control
 */

if (!defined('ABSPATH')) {
    exit;
}

// Backend API Secret Key
// This should match the SECRET_KEY in your Node.js backend .env file
define('BLL_SECRET_KEY', 'your-secret-key-here');
