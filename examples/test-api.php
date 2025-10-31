<?php
/**
 * Example PHP script to test the n8n WordPress Integration API
 * 
 * This file demonstrates how to use the REST API endpoints with WordPress HTTP API functions
 * You can run this script from command line or include it in your PHP application
 * 
 * Note: This script requires WordPress to be loaded (uses wp_remote_request functions)
 * To run standalone, you need to load WordPress:
 * require_once('/path/to/wordpress/wp-load.php');
 */

// Configuration
$wordpress_url = 'https://your-wordpress-site.com';
$api_key = 'your-secure-api-key-here';

/**
 * Make API request using WordPress HTTP API
 */
function make_api_request($url, $method = 'GET', $data = null, $api_key = null) {
    // Prepare headers
    $headers = array(
        'Content-Type' => 'application/json',
    );
    
    if ($api_key) {
        $headers['X-N8N-API-Key'] = $api_key;
    }
    
    // Prepare arguments for wp_remote_request
    $args = array(
        'method' => $method,
        'headers' => $headers,
        'timeout' => 30,
        'sslverify' => true,
    );
    
    // Add body data for POST/PUT/PATCH requests
    if ($data !== null) {
        $args['body'] = json_encode($data);
    }
    
    // Make the request
    $response = wp_remote_request($url, $args);
    
    // Check for errors
    if (is_wp_error($response)) {
        return array(
            'status' => 0,
            'body' => array(
                'error' => true,
                'message' => $response->get_error_message()
            )
        );
    }
    
    // Get response code and body
    $http_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    
    return array(
        'status' => $http_code,
        'body' => json_decode($response_body, true)
    );
}

// Example 1: Insert Data
echo "Example 1: Inserting data...\n";
$insert_data = array(
    'workflow_id' => 'example-workflow-001',
    'data' => array(
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'message' => 'Hello from PHP test script!',
        'timestamp' => date('Y-m-d H:i:s')
    ),
    'metadata' => array(
        'source' => 'php-test-script',
        'environment' => 'development'
    )
);

$result = make_api_request(
    $wordpress_url . '/wp-json/n8n/v1/insert',
    'POST',
    $insert_data,
    $api_key
);

echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

$inserted_id = $result['body']['id'] ?? null;

// Example 2: Get All Data
echo "Example 2: Getting all data...\n";
$result = make_api_request(
    $wordpress_url . '/wp-json/n8n/v1/data?limit=5',
    'GET',
    null,
    $api_key
);

echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

// Example 3: Get Single Record
if ($inserted_id) {
    echo "Example 3: Getting single record (ID: {$inserted_id})...\n";
    $result = make_api_request(
        $wordpress_url . '/wp-json/n8n/v1/data/' . $inserted_id,
        'GET',
        null,
        $api_key
    );
    
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";
}

// Example 4: Update Data
if ($inserted_id) {
    echo "Example 4: Updating record (ID: {$inserted_id})...\n";
    $update_data = array(
        'data' => array(
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Updated from PHP test script!',
            'timestamp' => date('Y-m-d H:i:s')
        ),
        'metadata' => array(
            'source' => 'php-test-script',
            'environment' => 'development',
            'updated' => true
        )
    );
    
    $result = make_api_request(
        $wordpress_url . '/wp-json/n8n/v1/update/' . $inserted_id,
        'PUT',
        $update_data,
        $api_key
    );
    
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";
}

// Example 5: Get Data Filtered by Workflow ID
echo "Example 5: Getting data filtered by workflow_id...\n";
$result = make_api_request(
    $wordpress_url . '/wp-json/n8n/v1/data?workflow_id=example-workflow-001&limit=10',
    'GET',
    null,
    $api_key
);

echo "Status: " . $result['status'] . "\n";
echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";

// Example 6: Delete Data (commented out by default)
/*
if ($inserted_id) {
    echo "Example 6: Deleting record (ID: {$inserted_id})...\n";
    $result = make_api_request(
        $wordpress_url . '/wp-json/n8n/v1/delete/' . $inserted_id,
        'DELETE',
        null,
        $api_key
    );
    
    echo "Status: " . $result['status'] . "\n";
    echo "Response: " . json_encode($result['body'], JSON_PRETTY_PRINT) . "\n\n";
}
*/

echo "All examples completed!\n";
