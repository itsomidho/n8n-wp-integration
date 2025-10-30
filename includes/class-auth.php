<?php
/**
 * Authentication for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authentication class
 */
class N8N_WP_Auth {
    
    /**
     * Check permission for REST API
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if authorized, WP_Error otherwise
     */
    public function check_permission($request) {
        // Check for API key in header or query parameter
        $api_key = $request->get_header('X-N8N-API-Key');
        
        if (empty($api_key)) {
            $api_key = $request->get_param('api_key');
        }
        
        // Get stored API key from options
        $stored_api_key = get_option('n8n_wp_api_key');
        
        // API key is required - no access without it
        if (empty($stored_api_key)) {
            return new WP_Error(
                'rest_forbidden',
                __('API key is not configured. Please set the n8n_wp_api_key option.', 'n8n-wp-integration'),
                array('status' => 401)
            );
        }
        
        // API key must be provided
        if (empty($api_key)) {
            return new WP_Error(
                'rest_forbidden',
                __('API key is required. Please provide X-N8N-API-Key header or api_key parameter.', 'n8n-wp-integration'),
                array('status' => 401)
            );
        }
        
        // Verify API key
        if ($api_key === $stored_api_key) {
            return true;
        }
        
        return new WP_Error(
            'rest_forbidden',
            __('Invalid API key', 'n8n-wp-integration'),
            array('status' => 401)
        );
    }
}
