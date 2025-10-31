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
        add_action('wp_ajax_n8n_delete_api_key', array($this, 'ajax_delete_api_key'));
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
            'generating'      => __('Generating...', 'n8n-wp-integration'),
            'successTitle'    => __('Success!', 'n8n-wp-integration'),
            'successMessage'  => __('New API key generated successfully.', 'n8n-wp-integration'),
            'errorGenerate'   => __('Error generating API key. Please try again.', 'n8n-wp-integration'),
            'copied'          => __('Copied!', 'n8n-wp-integration'),
            'confirmDelete'   => __('Are you sure you want to delete the API key? This will break existing integrations.', 'n8n-wp-integration'),
            'deleting'        => __('Deleting...', 'n8n-wp-integration'),
            'errorDelete'     => __('Error deleting API key. Please try again.', 'n8n-wp-integration'),
            'generateNonce'   => wp_create_nonce('n8n_generate_api_key'),
            'deleteNonce'     => wp_create_nonce('n8n_delete_api_key'),
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
        
        // Prepare template variables
        $api_key = $this->auth->get_api_key();
        $has_api_key = !empty($api_key);
        $page_title = get_admin_page_title();
        $rest_url = get_rest_url(null, 'n8n/v1/insert');
        
        // Load template
        $this->load_template('settings-page', compact('api_key', 'has_api_key', 'page_title', 'rest_url'));
    }
    
    /**
     * Load template file with variables
     *
     * @param string $template_name Template name (without .php extension)
     * @param array  $variables     Variables to extract into template scope
     */
    private function load_template($template_name, $variables = array()) {
        $template_path = N8N_WP_PLUGIN_DIR . 'views/admin/' . $template_name . '.php';
        
        if (!file_exists($template_path)) {
            echo '<div class="error"><p>' . esc_html__('Template file not found.', 'n8n-wp-integration') . '</p></div>';
            return;
        }
        
        // Extract variables into current scope
        extract($variables, EXTR_SKIP);
        
        // Include template
        include $template_path;
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
     * AJAX handler for deleting API key
     */
    public function ajax_delete_api_key() {
        // Check nonce
        check_ajax_referer('n8n_delete_api_key', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'n8n-wp-integration')));
        }
        
        // Delete API key
        if ($this->auth->delete_api_key()) {
            wp_send_json_success(array('message' => __('API key deleted successfully', 'n8n-wp-integration')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete API key', 'n8n-wp-integration')));
        }
    }
}
