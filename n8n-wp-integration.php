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

// Check if Composer autoloader exists
if (!file_exists(N8N_WP_PLUGIN_DIR . 'vendor/autoload.php')) {
    // Show admin notice
    add_action('admin_notices', 'n8n_wp_missing_autoloader_notice');
    
    /**
     * Display admin notice for missing Composer autoloader
     */
    function n8n_wp_missing_autoloader_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php esc_html_e('n8n WordPress Integration:', 'n8n-wp-integration'); ?></strong>
                <?php esc_html_e('Composer autoloader not found. Please run', 'n8n-wp-integration'); ?>
                <code>composer install --no-dev</code>
                <?php esc_html_e('in the plugin directory.', 'n8n-wp-integration'); ?>
            </p>
        </div>
        <?php
    }
    
    // Stop plugin execution
    return;
}

// Load Composer autoloader
require_once N8N_WP_PLUGIN_DIR . 'vendor/autoload.php';

// Initialize the plugin
new N8N_WP_Plugin();
