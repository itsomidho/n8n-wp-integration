# Includes Directory

This directory contains the core classes for the n8n WordPress Integration plugin, organized using Object-Oriented Programming principles.

## Files

### `class-plugin.php`
Main plugin orchestrator that manages all components.

**Purpose**: Coordinates Database, Auth, and API classes; handles WordPress hooks.

### `class-database.php`
Database operations handler (CRUD).

**Purpose**: All database queries and data persistence logic.

**Key Methods**:
- `create_table()` - Create the custom table
- `insert()` - Insert new records
- `get()` - Retrieve records with pagination
- `get_by_id()` - Get single record
- `update()` - Update existing record
- `delete()` - Delete record

### `class-auth.php`
Enhanced authentication and authorization handler.

**Purpose**: Validates API keys for REST API requests with advanced security features.

**Key Methods**:
- `check_permission()` - Verify API key with timing-safe comparison
- `get_api_key()` - Get stored API key
- `set_api_key()` - Set API key with validation
- `delete_api_key()` - Remove API key
- `is_valid_api_key_format()` - Validate API key format
- `generate_api_key()` - Generate secure random API key

**Security Features**:
- Timing-safe comparison prevents timing attacks
- API key format validation (minimum 32 characters)
- **Header-only authentication** - Query parameter support removed
- Failed authentication logging (when WP_DEBUG enabled)
- Sanitization of all inputs

### `class-admin-notices.php`
Admin notices handler.

**Purpose**: Display administrative notifications in WordPress admin panel.

**Key Methods**:
- `missing_autoloader_notice()` - Display notice when Composer autoloader is missing

**Features**:
- Separated from main plugin file for better organization
- Reusable notice system
- WordPress-standard notice formatting

### `class-api.php`
REST API endpoints handler.

**Purpose**: Process HTTP requests and return responses.

**Key Methods**:
- `register_routes()` - Register all endpoints
- `insert_data()` - POST /insert
- `get_data()` - GET /data
- `get_single_data()` - GET /data/{id}
- `update_data()` - PUT /update/{id}
- `delete_data()` - DELETE /delete/{id}

## Class Loading

Classes are loaded via **Composer's PSR-4 autoloader**:
- Class: `N8N_WP_ClassName`
- File: `class-classname.php`

Run `composer install --no-dev` to generate the autoloader.

## Adding New Classes

1. Create a new file: `class-your-class.php`
2. Define your class: `class N8N_WP_Your_Class { ... }`
3. Composer's autoloader will handle loading it automatically

Example:
```php
// Create: includes/class-logger.php
class N8N_WP_Logger {
    public function log($message) {
        error_log('[n8n] ' . $message);
    }
}

// Use anywhere:
$logger = new N8N_WP_Logger();
$logger->log('Hello!');
```

After adding a new class, you may need to run:
```bash
composer dump-autoload
```

## Dependencies

Classes use **Dependency Injection** for loose coupling:

```php
// API depends on Database and Auth
class N8N_WP_API {
    public function __construct($database, $auth) {
        $this->database = $database;
        $this->auth = $auth;
    }
}

// Plugin creates and injects dependencies
$database = new N8N_WP_Database();
$auth = new N8N_WP_Auth();
$api = new N8N_WP_API($database, $auth);
```

This makes testing easier and reduces coupling between components.

