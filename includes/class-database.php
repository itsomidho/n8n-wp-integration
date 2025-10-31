<?php
/**
 * Database operations for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

namespace N8N_WP;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database class
 */
class Database {
    
    /**
     * Database table name
     */
    private $table_name;
    
    /**
     * WordPress database object
     */
    private $wpdb;
    
    /**
     * Constructor
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'n8n_data';
    }
    
    /**
     * Create custom database table
     */
    public function create_table() {
        $charset_collate = $this->wpdb->get_charset_collate();
        
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
     * Insert data into table
     *
     * @param string $workflow_id Workflow identifier
     * @param mixed $data Data to insert
     * @param mixed $metadata Optional metadata
     * @return int|false Insert ID on success, false on failure
     */
    public function insert($workflow_id, $data, $metadata = null) {
        // Encode data as JSON if it's an array or object
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        
        // Encode metadata as JSON if provided and is an array or object
        if (!empty($metadata) && (is_array($metadata) || is_object($metadata))) {
            $metadata = json_encode($metadata);
        }
        
        // Insert into database
        $result = $this->wpdb->insert(
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
            return false;
        }
        
        return $this->wpdb->insert_id;
    }
    
    /**
     * Get data from table
     *
     * @param string $workflow_id Optional workflow ID filter
     * @param int $limit Number of records to return
     * @param int $offset Offset for pagination
     * @return array Array of records
     */
    public function get($workflow_id = null, $limit = 10, $offset = 0) {
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
            $results = $this->wpdb->get_results($this->wpdb->prepare($query, $prepare_args), ARRAY_A);
        } else {
            $results = $this->wpdb->get_results($query, ARRAY_A);
        }
        
        // Decode JSON data
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data']);
            if (!empty($row['metadata'])) {
                $row['metadata'] = json_decode($row['metadata']);
            }
        }
        
        return $results;
    }
    
    /**
     * Get single record by ID
     *
     * @param int $id Record ID
     * @return array|null Record data or null if not found
     */
    public function get_by_id($id) {
        $result = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            ),
            ARRAY_A
        );
        
        if (empty($result)) {
            return null;
        }
        
        // Decode JSON data
        $result['data'] = json_decode($result['data']);
        if (!empty($result['metadata'])) {
            $result['metadata'] = json_decode($result['metadata']);
        }
        
        return $result;
    }
    
    /**
     * Update record
     *
     * @param int $id Record ID
     * @param mixed $data Optional data to update
     * @param mixed $metadata Optional metadata to update
     * @return bool True on success, false on failure
     */
    public function update($id, $data = null, $metadata = null) {
        // Check if record exists
        if (!$this->exists($id)) {
            return false;
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
        $result = $this->wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            $format,
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Delete record
     *
     * @param int $id Record ID
     * @return bool True on success, false on failure
     */
    public function delete($id) {
        // Check if record exists
        if (!$this->exists($id)) {
            return false;
        }
        
        // Delete from database
        $result = $this->wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        return $result !== false;
    }
    
    /**
     * Check if record exists
     *
     * @param int $id Record ID
     * @return bool True if exists, false otherwise
     */
    public function exists($id) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );
        
        return $count > 0;
    }
    
    /**
     * Get last database error
     *
     * @return string Last error message
     */
    public function get_last_error() {
        return $this->wpdb->last_error;
    }
}
