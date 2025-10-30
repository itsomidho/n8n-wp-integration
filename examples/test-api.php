<?php
/**
 * Example PHP script to test the n8n WordPress Integration API
 * 
 * This file demonstrates how to use the REST API endpoints
 * You can run this script from command line or include it in your PHP application
 */

// Configuration
$wordpress_url = 'https://your-wordpress-site.com';
$api_key = 'your-secure-api-key-here';

/**
 * Make API request
 */
function make_api_request($url, $method = 'GET', $data = null, $api_key = null) {
    $ch = curl_init();
    
    $headers = array(
        'Content-Type: application/json',
    );
    
    if ($api_key) {
        $headers[] = 'X-N8N-API-Key: ' . $api_key;
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'status' => $http_code,
        'body' => json_decode($response, true)
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
