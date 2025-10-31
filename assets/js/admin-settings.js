/**
 * Admin Settings Page JavaScript for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

jQuery(document).ready(function($) {
    
    // Generate API Key
    $('#n8n-generate-btn').on('click', function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        if (!confirm(n8nAdminSettings.confirmGenerate)) {
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="n8n-spinner"></span> ' + n8nAdminSettings.generating);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'n8n_generate_api_key',
                nonce: n8nAdminSettings.generateNonce
            },
            success: function(response) {
                if (response.success) {
                    $('#n8n-api-key').val(response.data.api_key);
                    $('#n8n-copy-btn').prop('disabled', false);
                    
                    // Show success message
                    var notice = $('<div class="n8n-notice n8n-notice-success" style="display:none;"><span class="n8n-notice-icon">✅</span><div><strong>' + n8nAdminSettings.successTitle + '</strong><p style="margin: 4px 0 0 0;">' + n8nAdminSettings.successMessage + '</p></div></div>');
                    $('.n8n-settings-container').prepend(notice);
                    notice.slideDown();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert(n8nAdminSettings.errorGenerate);
                }
            },
            error: function() {
                alert(n8nAdminSettings.errorGenerate);
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
        
        $btn.html('✅ ' + n8nAdminSettings.copied);
        
        setTimeout(function() {
            $btn.html(originalText);
        }, 2000);
    });
    
    // Delete API Key
    $('#n8n-delete-btn').on('click', function() {
        if (!confirm(n8nAdminSettings.confirmDelete)) {
            return;
        }
        
        var $btn = $(this);
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="n8n-spinner"></span> ' + n8nAdminSettings.deleting);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'n8n_delete_api_key',
                nonce: n8nAdminSettings.deleteNonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(n8nAdminSettings.errorDelete);
                    $btn.prop('disabled', false).html(originalText);
                }
            },
            error: function() {
                alert(n8nAdminSettings.errorDelete);
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
    
});
