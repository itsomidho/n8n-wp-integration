<?php
/**
 * Main plugin class for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class N8N_WP_Plugin {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Auth instance
     */
    private $auth;
    
    /**
     * API instance
     */
    private $api;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->init_components();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        register_activation_hook(N8N_WP_PLUGIN_DIR . 'n8n-wp-integration.php', array($this, 'activate'));
        register_deactivation_hook(N8N_WP_PLUGIN_DIR . 'n8n-wp-integration.php', array($this, 'deactivate'));
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init_components() {
        $this->database = new N8N_WP_Database();
        $this->auth = new N8N_WP_Auth();
        $this->api = new N8N_WP_API($this->database, $this->auth);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->database->create_table();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        $this->api->register_routes();
    }
}
