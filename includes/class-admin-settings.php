<?php
/**
 * Admin Settings Page for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

namespace N8N_WP;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Settings Page class
 */
class Admin_Settings {
    
    /**
     * Auth instance
     */
    private $auth;
    
    /**
     * Page slug
     */
    const PAGE_SLUG = 'n8n-wp-settings';
    
    /**
     * Constructor
     *
     * @param Auth $auth Auth instance
     */
    public function __construct($auth) {
        $this->auth = $auth;
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_n8n_generate_api_key', array($this, 'ajax_generate_api_key'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('n8n WordPress Integration', 'n8n-wp-integration'),
            __('n8n Integration', 'n8n-wp-integration'),
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our settings page
        if ('settings_page_' . self::PAGE_SLUG !== $hook) {
            return;
        }
        
        // Enqueue CSS
        wp_enqueue_style(
            'n8n-admin-settings',
            N8N_WP_PLUGIN_URL . 'assets/css/admin-settings.css',
            array(),
            N8N_WP_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'n8n-admin-settings',
            N8N_WP_PLUGIN_URL . 'assets/js/admin-settings.js',
            array('jquery'),
            N8N_WP_VERSION,
            true
        );
        
        // Localize script with translatable strings and nonces
        wp_localize_script('n8n-admin-settings', 'n8nAdminSettings', array(
            'confirmGenerate' => __('Generate a new API key? This will replace any existing key.', 'n8n-wp-integration'),
            'generating' => __('Generating...', 'n8n-wp-integration'),
            'successTitle' => __('Success!', 'n8n-wp-integration'),
            'successMessage' => __('New API key generated successfully.', 'n8n-wp-integration'),
            'errorGenerate' => __('Error generating API key. Please try again.', 'n8n-wp-integration'),
            'copied' => __('Copied!', 'n8n-wp-integration'),
            'confirmDelete' => __('Are you sure you want to delete the API key? This will break existing integrations.', 'n8n-wp-integration'),
            'deleting' => __('Deleting...', 'n8n-wp-integration'),
            'errorDelete' => __('Error deleting API key. Please try again.', 'n8n-wp-integration'),
            'generateNonce' => wp_create_nonce('n8n_generate_api_key'),
            'deleteNonce' => wp_create_nonce('n8n_delete_api_key'),
        ));
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'n8n-wp-integration'));
        }
        
        $api_key = $this->auth->get_api_key();
        $has_api_key = !empty($api_key);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="n8n-settings-container">
                
                <?php if (!$has_api_key): ?>
                <div class="n8n-notice n8n-notice-warning">
                    <span class="n8n-notice-icon">⚠️</span>
                    <div>
                        <strong><?php esc_html_e('API Key Not Configured', 'n8n-wp-integration'); ?></strong>
                        <p style="margin: 4px 0 0 0;"><?php esc_html_e('Generate an API key to enable the n8n integration.', 'n8n-wp-integration'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="n8n-card">
                    <h2><?php esc_html_e('API Key Configuration', 'n8n-wp-integration'); ?></h2>
                    
                    <div style="margin-bottom: 20px;">
                        <strong><?php esc_html_e('Status:', 'n8n-wp-integration'); ?></strong>
                        <?php if ($has_api_key): ?>
                            <span class="n8n-api-status configured">
                                <span class="n8n-status-dot"></span>
                                <?php esc_html_e('Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php else: ?>
                            <span class="n8n-api-status not-configured">
                                <span class="n8n-status-dot"></span>
                                <?php esc_html_e('Not Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <p><?php esc_html_e('The API key is required for all REST API requests. Generate a secure key below or use an existing one.', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-form-group">
                        <label for="n8n-api-key">
                            <?php esc_html_e('API Key', 'n8n-wp-integration'); ?>
                        </label>
                        <div class="n8n-input-wrapper">
                            <input 
                                type="text" 
                                id="n8n-api-key" 
                                class="n8n-api-key-input" 
                                value="<?php echo esc_attr($api_key); ?>" 
                                readonly
                                placeholder="<?php esc_attr_e('Click "Generate API Key" to create a new key', 'n8n-wp-integration'); ?>"
                            >
                            <button 
                                type="button" 
                                id="n8n-copy-btn" 
                                class="n8n-btn n8n-btn-secondary"
                                <?php echo !$has_api_key ? 'disabled' : ''; ?>
                            >
                                📋 <?php esc_html_e('Copy', 'n8n-wp-integration'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 12px;">
                        <button 
                            type="button" 
                            id="n8n-generate-btn" 
                            class="n8n-btn n8n-btn-primary"
                        >
                            🔑 <?php esc_html_e('Generate API Key', 'n8n-wp-integration'); ?>
                        </button>
                        
                        <?php if ($has_api_key): ?>
                        <button 
                            type="button" 
                            id="n8n-delete-btn" 
                            class="n8n-btn n8n-btn-secondary"
                        >
                            🗑️ <?php esc_html_e('Delete API Key', 'n8n-wp-integration'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="n8n-card">
                    <h2><?php esc_html_e('Usage Instructions', 'n8n-wp-integration'); ?></h2>
                    <p><?php esc_html_e('Include your API key in all REST API requests using the X-N8N-API-Key header:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-code-block">
                        X-N8N-API-Key: your-api-key-here
                    </div>
                    
                    <p><?php esc_html_e('Example cURL request:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-code-block">
curl -X POST <?php echo esc_html(get_rest_url(null, 'n8n/v1/insert')); ?> \<br>
  -H "Content-Type: application/json" \<br>
  -H "X-N8N-API-Key: your-api-key-here" \<br>
  -d '{"workflow_id": "test", "data": {"message": "Hello"}}'
                    </div>
                </div>
                
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for generating API key
     */
    public function ajax_generate_api_key() {
        // Check nonce
        check_ajax_referer('n8n_generate_api_key', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'n8n-wp-integration')));
        }
        
        // Generate new API key
        $api_key = Auth::generate_api_key(64);
        
        // Save API key
        if ($this->auth->set_api_key($api_key)) {
            wp_send_json_success(array(
                'api_key' => $api_key,
                'message' => __('API key generated successfully', 'n8n-wp-integration')
            ));
        } else {
            wp_send_json_error(array('message' => __('Failed to save API key', 'n8n-wp-integration')));
        }
    }
    
    /**
     * AJAX handler for deleting API key (registered separately)
     */
    public static function ajax_delete_api_key() {
        // Check nonce
        check_ajax_referer('n8n_delete_api_key', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'n8n-wp-integration')));
        }
        
        // Delete API key
        $auth = new Auth();
        if ($auth->delete_api_key()) {
            wp_send_json_success(array('message' => __('API key deleted successfully', 'n8n-wp-integration')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete API key', 'n8n-wp-integration')));
        }
    }
}
