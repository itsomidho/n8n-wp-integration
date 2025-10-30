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

/**
 * Main plugin class
 */
class N8N_WP_Integration {
    
    /**
     * Database table name
     */
    private $table_name;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'n8n_data';
        
        // Hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize REST API
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        $this->create_table();
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Create custom database table
     */
    private function create_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            workflow_id varchar(255) NOT NULL,
            data longtext NOT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY workflow_id (workflow_id),
            KEY created_at (created_at)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Store database version
        add_option('n8n_wp_db_version', N8N_WP_VERSION);
    }
    
    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        // Insert data endpoint
        register_rest_route('n8n/v1', '/insert', array(
            'methods' => 'POST',
            'callback' => array($this, 'insert_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'workflow_id' => array(
                    'required' => true,
                    'type' => 'string',
                    'description' => 'The n8n workflow ID',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'data' => array(
                    'required' => true,
                    'description' => 'The data to insert',
                ),
                'metadata' => array(
                    'required' => false,
                    'description' => 'Optional metadata',
                ),
            ),
        ));
        
        // Get data endpoint
        register_rest_route('n8n/v1', '/data', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'workflow_id' => array(
                    'required' => false,
                    'type' => 'string',
                    'description' => 'Filter by workflow ID',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'limit' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 10,
                    'description' => 'Number of records to return',
                    'sanitize_callback' => 'absint',
                ),
                'offset' => array(
                    'required' => false,
                    'type' => 'integer',
                    'default' => 0,
                    'description' => 'Offset for pagination',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Get single record endpoint
        register_rest_route('n8n/v1', '/data/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_single_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'The record ID',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
        
        // Update data endpoint
        register_rest_route('n8n/v1', '/update/(?P<id>\d+)', array(
            'methods' => 'PUT',
            'callback' => array($this, 'update_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'The record ID',
                    'sanitize_callback' => 'absint',
                ),
                'data' => array(
                    'required' => false,
                    'description' => 'The data to update',
                ),
                'metadata' => array(
                    'required' => false,
                    'description' => 'Optional metadata',
                ),
            ),
        ));
        
        // Delete data endpoint
        register_rest_route('n8n/v1', '/delete/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_data'),
            'permission_callback' => array($this, 'check_permission'),
            'args' => array(
                'id' => array(
                    'required' => true,
                    'type' => 'integer',
                    'description' => 'The record ID',
                    'sanitize_callback' => 'absint',
                ),
            ),
        ));
    }
    
    /**
     * Check permission for REST API
     */
    public function check_permission($request) {
        // Check for API key in header or query parameter
        $api_key = $request->get_header('X-N8N-API-Key');
        
        if (empty($api_key)) {
            $api_key = $request->get_param('api_key');
        }
        
        // Get stored API key from options
        $stored_api_key = get_option('n8n_wp_api_key');
        
        // If no API key is set, allow access for backward compatibility
        // In production, you should always set an API key
        if (empty($stored_api_key)) {
            return true;
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
    
    /**
     * Insert data into custom table
     */
    public function insert_data($request) {
        global $wpdb;
        
        $workflow_id = $request->get_param('workflow_id');
        $data = $request->get_param('data');
        $metadata = $request->get_param('metadata');
        
        // Encode data as JSON if it's an array or object
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        
        // Encode metadata as JSON if provided and is an array or object
        if (!empty($metadata) && (is_array($metadata) || is_object($metadata))) {
            $metadata = json_encode($metadata);
        }
        
        // Insert into database
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'workflow_id' => $workflow_id,
                'data' => $data,
                'metadata' => $metadata,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error(
                'insert_failed',
                __('Failed to insert data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $wpdb->last_error)
            );
        }
        
        $insert_id = $wpdb->insert_id;
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __('Data inserted successfully', 'n8n-wp-integration'),
                'id' => $insert_id,
                'data' => array(
                    'id' => $insert_id,
                    'workflow_id' => $workflow_id,
                    'data' => json_decode($data),
                    'metadata' => !empty($metadata) ? json_decode($metadata) : null,
                ),
            ),
            201
        );
    }
    
    /**
     * Get data from custom table
     */
    public function get_data($request) {
        global $wpdb;
        
        $workflow_id = $request->get_param('workflow_id');
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');
        
        // Build query
        $query = "SELECT * FROM {$this->table_name}";
        $where = array();
        $prepare_args = array();
        
        if (!empty($workflow_id)) {
            $where[] = "workflow_id = %s";
            $prepare_args[] = $workflow_id;
        }
        
        if (!empty($where)) {
            $query .= " WHERE " . implode(' AND ', $where);
        }
        
        $query .= " ORDER BY created_at DESC";
        
        // Add limit and offset
        $query .= " LIMIT %d OFFSET %d";
        $prepare_args[] = $limit;
        $prepare_args[] = $offset;
        
        // Execute query
        if (!empty($prepare_args)) {
            $results = $wpdb->get_results($wpdb->prepare($query, $prepare_args), ARRAY_A);
        } else {
            $results = $wpdb->get_results($query, ARRAY_A);
        }
        
        // Decode JSON data
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data']);
            if (!empty($row['metadata'])) {
                $row['metadata'] = json_decode($row['metadata']);
            }
        }
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'count' => count($results),
                'data' => $results,
            ),
            200
        );
    }
    
    /**
     * Get single data record
     */
    public function get_single_data($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
        
        if (empty($result)) {
            return new WP_Error(
                'not_found',
                __('Record not found', 'n8n-wp-integration'),
                array('status' => 404)
            );
        }
        
        // Decode JSON data
        $result['data'] = json_decode($result['data']);
        if (!empty($result['metadata'])) {
            $result['metadata'] = json_decode($result['metadata']);
        }
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'data' => $result,
            ),
            200
        );
    }
    
    /**
     * Update data in custom table
     */
    public function update_data($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        $data = $request->get_param('data');
        $metadata = $request->get_param('metadata');
        
        // Check if record exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
        
        if ($exists == 0) {
            return new WP_Error(
                'not_found',
                __('Record not found', 'n8n-wp-integration'),
                array('status' => 404)
            );
        }
        
        $update_data = array(
            'updated_at' => current_time('mysql'),
        );
        $format = array('%s');
        
        // Update data if provided
        if (!empty($data)) {
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            $update_data['data'] = $data;
            $format[] = '%s';
        }
        
        // Update metadata if provided
        if (!empty($metadata)) {
            if (is_array($metadata) || is_object($metadata)) {
                $metadata = json_encode($metadata);
            }
            $update_data['metadata'] = $metadata;
            $format[] = '%s';
        }
        
        // Update database
        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            $format,
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error(
                'update_failed',
                __('Failed to update data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $wpdb->last_error)
            );
        }
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __('Data updated successfully', 'n8n-wp-integration'),
                'id' => $id,
            ),
            200
        );
    }
    
    /**
     * Delete data from custom table
     */
    public function delete_data($request) {
        global $wpdb;
        
        $id = $request->get_param('id');
        
        // Check if record exists
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
        
        if ($exists == 0) {
            return new WP_Error(
                'not_found',
                __('Record not found', 'n8n-wp-integration'),
                array('status' => 404)
            );
        }
        
        // Delete from database
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error(
                'delete_failed',
                __('Failed to delete data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $wpdb->last_error)
            );
        }
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __('Data deleted successfully', 'n8n-wp-integration'),
            ),
            200
        );
    }
}

// Initialize the plugin
new N8N_WP_Integration();
