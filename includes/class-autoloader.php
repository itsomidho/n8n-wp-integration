<?php
/**
 * Autoloader for n8n WordPress Integration
 * 
 * PSR-4 compliant autoloader for plugin classes
 *
 * @package N8N_WP_Integration
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader class
 */
class N8N_WP_Autoloader {
    
    /**
     * Namespace prefix
     */
    private $prefix = 'N8N_WP_';
    
    /**
     * Base directory for the namespace prefix
     */
    private $base_dir;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->base_dir = N8N_WP_PLUGIN_DIR . 'includes/';
    }
    
    /**
     * Register autoloader
     */
    public function register() {
        spl_autoload_register(array($this, 'autoload'));
    }
    
    /**
     * Autoload class files
     *
     * @param string $class The fully-qualified class name
     */
    private function autoload($class) {
        // Check if the class uses the namespace prefix
        $len = strlen($this->prefix);
        if (strncmp($this->prefix, $class, $len) !== 0) {
            return;
        }
        
        // Get the relative class name
        $relative_class = substr($class, $len);
        
        // Convert class name to file name
        // N8N_WP_Database -> class-database.php
        $file_name = 'class-' . strtolower(str_replace('_', '-', $relative_class)) . '.php';
        
        // Build the full path
        $file = $this->base_dir . $file_name;
        
        // If the file exists, require it
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
