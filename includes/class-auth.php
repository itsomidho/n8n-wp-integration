<?php
/**
 * Authentication for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

namespace N8N_WP;

use WP_Error;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Authentication class
 */
class Auth {
    
    /**
     * API key option name
     */
    const API_KEY_OPTION = 'n8n_wp_api_key';
    
    /**
     * Minimum API key length
     */
    const MIN_API_KEY_LENGTH = 32;
    
    /**
     * Get stored API key
     *
     * @return string|false API key or false if not set
     */
    public function get_api_key() {

        return get_option(self::API_KEY_OPTION, false);
    }
    
    /**
     * Set API key
     *
     * @param string $api_key API key to store
     * @return bool True on success, false on failure
     */
    public function set_api_key($api_key) {

        // Sanitize the API key
        $api_key = sanitize_text_field($api_key);
        
        // Validate minimum length
        if (strlen($api_key) < self::MIN_API_KEY_LENGTH) {
            return false;
        }
        
        return update_option(self::API_KEY_OPTION, $api_key);
    }
    
    /**
     * Delete API key
     *
     * @return bool True on success, false on failure
     */
    public function delete_api_key() {

        return delete_option(self::API_KEY_OPTION);
    }
    
    /**
     * Validate API key format
     *
     * @param string $api_key API key to validate
     * @return bool True if valid, false otherwise
     */
    public function is_valid_api_key_format($api_key) {

        // Check if API key is a string
        if (!is_string($api_key)) {
            return false;
        }
        
        // Check minimum length
        if (strlen($api_key) < self::MIN_API_KEY_LENGTH) {
            return false;
        }
        
        // Check for valid characters (alphanumeric, dash, underscore)
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $api_key)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Extract API key from request
     *
     * @param WP_REST_Request $request Request object
     * @return string|null API key or null if not found
     */
    private function extract_api_key($request) {

        // Check for API key in header only
        $api_key = $request->get_header('X-N8N-API-Key');
        
        if (!empty($api_key)) {
            return sanitize_text_field($api_key);
        }
        
        return null;
    }
    
    /**
     * Check permission for REST API
     *
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if authorized, WP_Error otherwise
     */
    public function check_permission($request) {

        // Get stored API key
        $stored_api_key = $this->get_api_key();
        
        // API key must be configured
        if (empty($stored_api_key)) {
            return new WP_Error(
                'n8n_api_key_not_configured',
                __('API key is not configured. Please set the n8n_wp_api_key option.', 'n8n-wp-integration'),
                array('status' => 401)
            );
        }
        
        // Extract API key from request
        $provided_api_key = $this->extract_api_key($request);
        
        // API key must be provided in request
        if (empty($provided_api_key)) {
            return new WP_Error(
                'n8n_api_key_missing',
                __('API key is required. Please provide X-N8N-API-Key header.', 'n8n-wp-integration'),
                array('status' => 401)
            );
        }
        
        // Verify API key using timing-safe comparison
        if (!hash_equals($stored_api_key, $provided_api_key)) {
            // Log failed authentication attempt
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    '[n8n-wp-integration] Failed authentication attempt from IP: %s',
                    $this->get_client_ip()
                ));
            }
            
            return new WP_Error(
                'n8n_api_key_invalid',
                __('Invalid API key.', 'n8n-wp-integration'),
                array('status' => 401)
            );
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     *
     * @return string Client IP address
     */
    private function get_client_ip() {

        $ip = '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return sanitize_text_field($ip);
    }
    
    /**
     * Generate a secure random API key
     *
     * @param int $length Length of the API key (minimum 32)
     * @return string Generated API key
     */
    public static function generate_api_key($length = 64) {
        
        $length = max($length, self::MIN_API_KEY_LENGTH);
        
        // Use WordPress's wp_generate_password for secure random string
        return wp_generate_password($length, false);
    }
}
