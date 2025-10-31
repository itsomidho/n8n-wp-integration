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
        
        // Enqueue Tailwind CSS (locally built)
        wp_enqueue_style(
            'n8n-admin-tailwind',
            N8N_WP_PLUGIN_URL . 'assets/css/admin-tailwind.css',
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
            
            <div class="max-w-4xl mx-0 my-10">
                
                <?php if (!$has_api_key): ?>
                <div class="p-3 px-4 rounded mb-5 flex items-start gap-2.5 bg-orange-50 border-l-4 border-orange-400 text-gray-800">
                    <span class="flex-shrink-0 text-lg">⚠️</span>
                    <div>
                        <strong><?php esc_html_e('API Key Not Configured', 'n8n-wp-integration'); ?></strong>
                        <p style="margin: 4px 0 0 0;"><?php esc_html_e('Generate an API key to enable the n8n integration.', 'n8n-wp-integration'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="bg-white border border-gray-300 rounded-lg p-8 mb-5 shadow-sm">
                    <h2 class="mt-0 text-xl font-semibold text-gray-800"><?php esc_html_e('API Key Configuration', 'n8n-wp-integration'); ?></h2>
                    
                    <div style="margin-bottom: 20px;">
                        <strong><?php esc_html_e('Status:', 'n8n-wp-integration'); ?></strong>
                        <?php if ($has_api_key): ?>
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-sm font-medium bg-green-100 text-green-700">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                <?php esc_html_e('Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-sm font-medium bg-orange-50 text-orange-700">
                                <span class="w-2 h-2 rounded-full bg-current"></span>
                                <?php esc_html_e('Not Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-gray-600 mb-5"><?php esc_html_e('The API key is required for all REST API requests. Generate a secure key below or use an existing one.', 'n8n-wp-integration'); ?></p>
                    
                    <div class="mb-6">
                        <label for="n8n-api-key" class="block mb-2 font-medium text-gray-800">
                            <?php esc_html_e('API Key', 'n8n-wp-integration'); ?>
                        </label>
                        <div class="flex gap-3 items-center">
                            <input 
                                type="text" 
                                id="n8n-api-key" 
                                class="flex-1 py-2.5 px-3.5 border border-gray-400 rounded font-mono text-sm bg-gray-50 text-gray-800 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600" 
                                value="<?php echo esc_attr($api_key); ?>" 
                                readonly
                                placeholder="<?php esc_attr_e('Click "Generate API Key" to create a new key', 'n8n-wp-integration'); ?>"
                            >
                            <button 
                                type="button" 
                                id="n8n-copy-btn" 
                                class="py-2.5 px-5 rounded text-sm font-medium cursor-pointer transition-all duration-200 no-underline inline-flex items-center gap-2 bg-gray-50 text-gray-800 border border-gray-400 hover:bg-gray-100 <?php echo !$has_api_key ? 'opacity-50 cursor-not-allowed' : ''; ?>"
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
                            class="py-2.5 px-5 rounded text-sm font-medium cursor-pointer transition-all duration-200 no-underline inline-flex items-center gap-2 bg-blue-600 text-white hover:bg-blue-700"
                        >
                            🔑 <?php esc_html_e('Generate API Key', 'n8n-wp-integration'); ?>
                        </button>
                        
                        <?php if ($has_api_key): ?>
                        <button 
                            type="button" 
                            id="n8n-delete-btn" 
                            class="py-2.5 px-5 rounded text-sm font-medium cursor-pointer transition-all duration-200 no-underline inline-flex items-center gap-2 bg-gray-50 text-gray-800 border border-gray-400 hover:bg-gray-100"
                        >
                            🗑️ <?php esc_html_e('Delete API Key', 'n8n-wp-integration'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-300 rounded-lg p-8 mb-5 shadow-sm">
                    <h2 class="mt-0 text-xl font-semibold text-gray-800"><?php esc_html_e('Usage Instructions', 'n8n-wp-integration'); ?></h2>
                    <p class="text-gray-600 mb-5"><?php esc_html_e('Include your API key in all REST API requests using the X-N8N-API-Key header:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="bg-gray-50 border border-gray-300 rounded p-3 px-4 font-mono text-sm overflow-x-auto my-2.5">
                        X-N8N-API-Key: your-api-key-here
                    </div>
                    
                    <p class="text-gray-600 mb-5"><?php esc_html_e('Example cURL request:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="bg-gray-50 border border-gray-300 rounded p-3 px-4 font-mono text-sm overflow-x-auto my-2.5">
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
