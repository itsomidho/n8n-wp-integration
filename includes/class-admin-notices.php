<?php
/**
 * Admin Notices for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Notices class
 */
class N8N_WP_Admin_Notices {
    
    /**
     * Display admin notice for missing Composer autoloader
     */
    public static function missing_autoloader_notice() {
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
}
