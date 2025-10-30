<?php
/**
 * Uninstall script for n8n WordPress Integration
 * 
 * This file is executed when the plugin is uninstalled via WordPress admin
 */

// Exit if accessed directly or if uninstall is not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Define table name
$table_name = $wpdb->prefix . 'n8n_data';

// Drop the custom table
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// Delete plugin options
delete_option('n8n_wp_db_version');
delete_option('n8n_wp_api_key');

// Clean up any transients or cached data
delete_transient('n8n_wp_cache');
