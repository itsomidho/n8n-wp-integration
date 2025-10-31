<?php
/**
 * Admin Settings Page Template for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 *
 * Available variables:
 * @var string $page_title   The page title
 * @var string $api_key      The current API key
 * @var bool   $has_api_key  Whether an API key exists
 * @var string $rest_url     The REST API URL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
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
                curl -X POST <?php echo esc_html($rest_url); ?> \<br>
                -H "Content-Type: application/json" \<br>
                -H "X-N8N-API-Key: your-api-key-here" \<br>
                -d '{"workflow_id": "test", "data": {"message": "Hello"}}'
            </div>
        </div>
        
    </div>
</div>
