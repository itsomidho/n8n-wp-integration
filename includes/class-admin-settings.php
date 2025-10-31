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
        
        // Enqueue inline CSS for modern UI
        wp_add_inline_style('wp-admin', $this->get_admin_css());
    }
    
    /**
     * Get admin CSS
     *
     * @return string CSS styles
     */
    private function get_admin_css() {
        return '
            .n8n-settings-container {
                max-width: 800px;
                margin: 40px 0;
            }
            .n8n-card {
                background: #fff;
                border: 1px solid #dcdcde;
                border-radius: 8px;
                padding: 30px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            }
            .n8n-card h2 {
                margin-top: 0;
                font-size: 20px;
                font-weight: 600;
                color: #1d2327;
            }
            .n8n-card p {
                color: #50575e;
                margin-bottom: 20px;
            }
            .n8n-form-group {
                margin-bottom: 24px;
            }
            .n8n-form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
                color: #1d2327;
            }
            .n8n-input-wrapper {
                display: flex;
                gap: 12px;
                align-items: center;
            }
            .n8n-api-key-input {
                flex: 1;
                padding: 10px 14px;
                border: 1px solid #8c8f94;
                border-radius: 4px;
                font-family: "Monaco", "Menlo", "Ubuntu Mono", "Consolas", monospace;
                font-size: 13px;
                background: #f6f7f7;
                color: #2c3338;
            }
            .n8n-api-key-input:focus {
                border-color: #2271b1;
                outline: 2px solid transparent;
                box-shadow: 0 0 0 1px #2271b1;
            }
            .n8n-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                font-size: 14px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .n8n-btn-primary {
                background: #2271b1;
                color: #fff;
            }
            .n8n-btn-primary:hover {
                background: #135e96;
                color: #fff;
            }
            .n8n-btn-secondary {
                background: #f6f7f7;
                color: #2c3338;
                border: 1px solid #8c8f94;
            }
            .n8n-btn-secondary:hover {
                background: #f0f0f1;
                color: #2c3338;
            }
            .n8n-btn:disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }
            .n8n-notice {
                padding: 12px 16px;
                border-radius: 4px;
                margin-bottom: 20px;
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }
            .n8n-notice-success {
                background: #d7f0dc;
                border-left: 4px solid #00a32a;
                color: #1d2327;
            }
            .n8n-notice-warning {
                background: #fcf3e6;
                border-left: 4px solid #dba617;
                color: #1d2327;
            }
            .n8n-notice-icon {
                flex-shrink: 0;
                font-size: 18px;
            }
            .n8n-api-status {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 13px;
                font-weight: 500;
            }
            .n8n-api-status.configured {
                background: #d7f0dc;
                color: #00a32a;
            }
            .n8n-api-status.not-configured {
                background: #fcf3e6;
                color: #b96800;
            }
            .n8n-status-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: currentColor;
            }
            .n8n-code-block {
                background: #f6f7f7;
                border: 1px solid #dcdcde;
                border-radius: 4px;
                padding: 12px 16px;
                font-family: "Monaco", "Menlo", "Ubuntu Mono", "Consolas", monospace;
                font-size: 13px;
                overflow-x: auto;
                margin: 10px 0;
            }
            .n8n-spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #2271b1;
                border-radius: 50%;
                width: 16px;
                height: 16px;
                animation: n8n-spin 1s linear infinite;
            }
            @keyframes n8n-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        ';
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'n8n-wp-integration'));
        }
        
        $api_key = $this->auth->get_api_key();
        $has_api_key = !empty($api_key);
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="n8n-settings-container">
                
                <?php if (!$has_api_key): ?>
                <div class="n8n-notice n8n-notice-warning">
                    <span class="n8n-notice-icon">⚠️</span>
                    <div>
                        <strong><?php esc_html_e('API Key Not Configured', 'n8n-wp-integration'); ?></strong>
                        <p style="margin: 4px 0 0 0;"><?php esc_html_e('Generate an API key to enable the n8n integration.', 'n8n-wp-integration'); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="n8n-card">
                    <h2><?php esc_html_e('API Key Configuration', 'n8n-wp-integration'); ?></h2>
                    
                    <div style="margin-bottom: 20px;">
                        <strong><?php esc_html_e('Status:', 'n8n-wp-integration'); ?></strong>
                        <?php if ($has_api_key): ?>
                            <span class="n8n-api-status configured">
                                <span class="n8n-status-dot"></span>
                                <?php esc_html_e('Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php else: ?>
                            <span class="n8n-api-status not-configured">
                                <span class="n8n-status-dot"></span>
                                <?php esc_html_e('Not Configured', 'n8n-wp-integration'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <p><?php esc_html_e('The API key is required for all REST API requests. Generate a secure key below or use an existing one.', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-form-group">
                        <label for="n8n-api-key">
                            <?php esc_html_e('API Key', 'n8n-wp-integration'); ?>
                        </label>
                        <div class="n8n-input-wrapper">
                            <input 
                                type="text" 
                                id="n8n-api-key" 
                                class="n8n-api-key-input" 
                                value="<?php echo esc_attr($api_key); ?>" 
                                readonly
                                placeholder="<?php esc_attr_e('Click "Generate API Key" to create a new key', 'n8n-wp-integration'); ?>"
                            >
                            <button 
                                type="button" 
                                id="n8n-copy-btn" 
                                class="n8n-btn n8n-btn-secondary"
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
                            class="n8n-btn n8n-btn-primary"
                        >
                            🔑 <?php esc_html_e('Generate API Key', 'n8n-wp-integration'); ?>
                        </button>
                        
                        <?php if ($has_api_key): ?>
                        <button 
                            type="button" 
                            id="n8n-delete-btn" 
                            class="n8n-btn n8n-btn-secondary"
                        >
                            🗑️ <?php esc_html_e('Delete API Key', 'n8n-wp-integration'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="n8n-card">
                    <h2><?php esc_html_e('Usage Instructions', 'n8n-wp-integration'); ?></h2>
                    <p><?php esc_html_e('Include your API key in all REST API requests using the X-N8N-API-Key header:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-code-block">
                        X-N8N-API-Key: your-api-key-here
                    </div>
                    
                    <p><?php esc_html_e('Example cURL request:', 'n8n-wp-integration'); ?></p>
                    
                    <div class="n8n-code-block">
curl -X POST <?php echo esc_html(get_rest_url(null, 'n8n/v1/insert')); ?> \<br>
  -H "Content-Type: application/json" \<br>
  -H "X-N8N-API-Key: your-api-key-here" \<br>
  -d '{"workflow_id": "test", "data": {"message": "Hello"}}'
                    </div>
                </div>
                
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            
            // Generate API Key
            $('#n8n-generate-btn').on('click', function() {
                var $btn = $(this);
                var originalText = $btn.html();
                
                if (!confirm('<?php echo esc_js(__('Generate a new API key? This will replace any existing key.', 'n8n-wp-integration')); ?>')) {
                    return;
                }
                
                $btn.prop('disabled', true).html('<span class="n8n-spinner"></span> <?php echo esc_js(__('Generating...', 'n8n-wp-integration')); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'n8n_generate_api_key',
                        nonce: '<?php echo wp_create_nonce('n8n_generate_api_key'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#n8n-api-key').val(response.data.api_key);
                            $('#n8n-copy-btn').prop('disabled', false);
                            
                            // Show success message
                            var notice = $('<div class="n8n-notice n8n-notice-success" style="display:none;"><span class="n8n-notice-icon">✅</span><div><strong><?php echo esc_js(__('Success!', 'n8n-wp-integration')); ?></strong><p style="margin: 4px 0 0 0;"><?php echo esc_js(__('New API key generated successfully.', 'n8n-wp-integration')); ?></p></div></div>');
                            $('.n8n-settings-container').prepend(notice);
                            notice.slideDown();
                            
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            alert('<?php echo esc_js(__('Error generating API key. Please try again.', 'n8n-wp-integration')); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Error generating API key. Please try again.', 'n8n-wp-integration')); ?>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
            // Copy API Key
            $('#n8n-copy-btn').on('click', function() {
                var $input = $('#n8n-api-key');
                var $btn = $(this);
                var originalText = $btn.html();
                
                $input.select();
                document.execCommand('copy');
                
                $btn.html('✅ <?php echo esc_js(__('Copied!', 'n8n-wp-integration')); ?>');
                
                setTimeout(function() {
                    $btn.html(originalText);
                }, 2000);
            });
            
            // Delete API Key
            $('#n8n-delete-btn').on('click', function() {
                if (!confirm('<?php echo esc_js(__('Are you sure you want to delete the API key? This will break existing integrations.', 'n8n-wp-integration')); ?>')) {
                    return;
                }
                
                var $btn = $(this);
                var originalText = $btn.html();
                
                $btn.prop('disabled', true).html('<span class="n8n-spinner"></span> <?php echo esc_js(__('Deleting...', 'n8n-wp-integration')); ?>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'n8n_delete_api_key',
                        nonce: '<?php echo wp_create_nonce('n8n_delete_api_key'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert('<?php echo esc_js(__('Error deleting API key. Please try again.', 'n8n-wp-integration')); ?>');
                            $btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function() {
                        alert('<?php echo esc_js(__('Error deleting API key. Please try again.', 'n8n-wp-integration')); ?>');
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
            
        });
        </script>
        <?php
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
     * AJAX handler for deleting API key (registered separately)
     */
    public static function ajax_delete_api_key() {
        // Check nonce
        check_ajax_referer('n8n_delete_api_key', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions', 'n8n-wp-integration')));
        }
        
        // Delete API key
        $auth = new Auth();
        if ($auth->delete_api_key()) {
            wp_send_json_success(array('message' => __('API key deleted successfully', 'n8n-wp-integration')));
        } else {
            wp_send_json_error(array('message' => __('Failed to delete API key', 'n8n-wp-integration')));
        }
    }
}
