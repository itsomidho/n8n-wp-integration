/**
 * Admin Settings Page JavaScript for n8n WordPress Integration
 *
 * @package N8N_WP_Integration
 */

jQuery(document).ready(function($) {
    
    // Generate API Key
    $('#n8n-generate-btn').on('click', function () {
        
        const $btn = $(this);
        const originalText = $btn.html();
        
        if (!confirm(n8nAdminSettings.confirmGenerate)) {
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="border-4 border-gray-200 border-t-blue-600 rounded-full w-4 h-4 animate-spin inline-block mr-2"></span> ' + n8nAdminSettings.generating);
        
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
                    var notice = $('<div class="p-3 px-4 rounded mb-5 flex items-start gap-2.5 bg-green-100 border-l-4 border-green-500 text-gray-800" style="display:none;"><span class="flex-shrink-0 text-lg">✅</span><div><strong>' + n8nAdminSettings.successTitle + '</strong><p style="margin: 4px 0 0 0;">' + n8nAdminSettings.successMessage + '</p></div></div>');
                    $('.max-w-4xl').prepend(notice);
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
    $('#n8n-copy-btn').on('click', function () {
        
        const $input = $('#n8n-api-key');
        const $btn = $(this);
        const originalText = $btn.html();
        
        $input.select();
        document.execCommand('copy');
        
        $btn.html('✅ ' + n8nAdminSettings.copied);
        
        setTimeout(function() {
            $btn.html(originalText);
        }, 2000);
    });
    
    // Delete API Key
    $('#n8n-delete-btn').on('click', function () {
        
        if (!confirm(n8nAdminSettings.confirmDelete)) {
            return;
        }
        
        const $btn = $(this);
        const originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="border-4 border-gray-200 border-t-blue-600 rounded-full w-4 h-4 animate-spin inline-block mr-2"></span> ' + n8nAdminSettings.deleting);
        
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
