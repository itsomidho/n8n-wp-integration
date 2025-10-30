<?php
/**
 * Plugin Name: n8n WordPress Integration
 * Plugin URI: https://github.com/itsomidho/n8n-wp-integration
 * Description: Integrates n8n workflows with WordPress via custom REST API to insert data into a custom MySQL table
 * Version: 1.0.0
 * Author: itsomidho
 * Author URI: https://github.com/itsomidho
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: n8n-wp-integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('N8N_WP_VERSION', '1.0.0');
define('N8N_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('N8N_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Composer autoloader
if (file_exists(N8N_WP_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once N8N_WP_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback to manual autoloader if Composer autoloader is not available
    require_once N8N_WP_PLUGIN_DIR . 'includes/class-autoloader.php';
    $autoloader = new N8N_WP_Autoloader();
    $autoloader->register();
}

// Initialize the plugin
new N8N_WP_Plugin();
