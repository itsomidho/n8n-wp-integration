<?php
/**
 * REST API endpoints for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * API class
 */
class N8N_WP_API {
    
    /**
     * Database instance
     */
    private $database;
    
    /**
     * Auth instance
     */
    private $auth;
    
    /**
     * Constructor
     *
     * @param N8N_WP_Database $database Database instance
     * @param N8N_WP_Auth $auth Auth instance
     */
    public function __construct($database, $auth) {
        $this->database = $database;
        $this->auth = $auth;
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        // Insert data endpoint
        register_rest_route('n8n/v1', '/insert', array(
            'methods' => 'POST',
            'callback' => array($this, 'insert_data'),
            'permission_callback' => array($this->auth, 'check_permission'),
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
            'permission_callback' => array($this->auth, 'check_permission'),
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
            'permission_callback' => array($this->auth, 'check_permission'),
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
            'permission_callback' => array($this->auth, 'check_permission'),
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
            'permission_callback' => array($this->auth, 'check_permission'),
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
     * Insert data into custom table
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function insert_data($request) {
        $workflow_id = $request->get_param('workflow_id');
        $data = $request->get_param('data');
        $metadata = $request->get_param('metadata');
        
        $insert_id = $this->database->insert($workflow_id, $data, $metadata);
        
        if ($insert_id === false) {
            return new WP_Error(
                'insert_failed',
                __('Failed to insert data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $this->database->get_last_error())
            );
        }
        
        // Get the inserted record
        $record = $this->database->get_by_id($insert_id);
        
        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __('Data inserted successfully', 'n8n-wp-integration'),
                'id' => $insert_id,
                'data' => $record,
            ),
            201
        );
    }
    
    /**
     * Get data from custom table
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response Response object
     */
    public function get_data($request) {
        $workflow_id = $request->get_param('workflow_id');
        $limit = $request->get_param('limit');
        $offset = $request->get_param('offset');
        
        $results = $this->database->get($workflow_id, $limit, $offset);
        
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
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function get_single_data($request) {
        $id = $request->get_param('id');
        
        $result = $this->database->get_by_id($id);
        
        if (empty($result)) {
            return new WP_Error(
                'not_found',
                __('Record not found', 'n8n-wp-integration'),
                array('status' => 404)
            );
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
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function update_data($request) {
        $id = $request->get_param('id');
        $data = $request->get_param('data');
        $metadata = $request->get_param('metadata');
        
        $result = $this->database->update($id, $data, $metadata);
        
        if (!$result) {
            if (!$this->database->exists($id)) {
                return new WP_Error(
                    'not_found',
                    __('Record not found', 'n8n-wp-integration'),
                    array('status' => 404)
                );
            }
            
            return new WP_Error(
                'update_failed',
                __('Failed to update data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $this->database->get_last_error())
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
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error Response object
     */
    public function delete_data($request) {
        $id = $request->get_param('id');
        
        $result = $this->database->delete($id);
        
        if (!$result) {
            if (!$this->database->exists($id)) {
                return new WP_Error(
                    'not_found',
                    __('Record not found', 'n8n-wp-integration'),
                    array('status' => 404)
                );
            }
            
            return new WP_Error(
                'delete_failed',
                __('Failed to delete data', 'n8n-wp-integration'),
                array('status' => 500, 'error' => $this->database->get_last_error())
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
