# Includes Directory

This directory contains the core classes for the n8n WordPress Integration plugin, organized using Object-Oriented Programming principles.

## Files

### `class-autoloader.php`
PSR-4 compliant autoloader that automatically loads classes when needed.

**Purpose**: Eliminates the need for manual `require` statements.

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
Authentication and authorization handler.

**Purpose**: Validates API keys for REST API requests.

**Key Methods**:
- `check_permission()` - Verify API key

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

Classes follow the naming convention:
- Class: `N8N_WP_ClassName`
- File: `class-classname.php`

The autoloader automatically loads classes when they are first used, so no manual `require` statements are needed in your code.

## Adding New Classes

1. Create a new file: `class-your-class.php`
2. Define your class: `class N8N_WP_Your_Class { ... }`
3. The autoloader will handle loading it automatically

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
