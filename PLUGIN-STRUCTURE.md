# Plugin Structure Documentation

## Architecture Overview

This plugin follows **Object-Oriented Programming (OOP)** principles with a modular architecture:

- **Separation of Concerns**: Each class handles a specific responsibility
- **PSR-4 Autoloading**: Automatic class loading without manual includes
- **Dependency Injection**: Components are injected where needed
- **Maintainability**: Easy to extend and modify individual components

## File Structure

```
n8n-wp-integration/
├── n8n-wp-integration.php    # Main plugin file (bootstrap)
├── uninstall.php              # Cleanup script
├── includes/                  # Core classes directory
│   ├── class-autoloader.php   # PSR-4 autoloader
│   ├── class-plugin.php       # Main plugin orchestrator
│   ├── class-database.php     # Database operations
│   ├── class-api.php          # REST API endpoints
│   └── class-auth.php         # Authentication
├── examples/                  # Usage examples
│   ├── curl-examples.sh
│   ├── test-api.php
│   └── n8n-workflow-example.json
└── documentation files (README.md, etc.)
```

## Core Plugin Files

### `n8n-wp-integration.php` (Main Plugin File)
Bootstrap file that initializes the plugin.

**Responsibilities:**
- Define plugin constants (VERSION, DIR, URL)
- Load autoloader
- Initialize main plugin class

**Code Structure:**
```php
// Define constants
define('N8N_WP_VERSION', '1.0.0');
define('N8N_WP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('N8N_WP_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load autoloader
require_once N8N_WP_PLUGIN_DIR . 'includes/class-autoloader.php';

// Register autoloader
$autoloader = new N8N_WP_Autoloader();
$autoloader->register();

// Initialize plugin
new N8N_WP_Plugin();
```

## Class Documentation

### `N8N_WP_Autoloader` (class-autoloader.php)
PSR-4 compliant autoloader for automatic class loading.

**Properties:**
- `$prefix`: Namespace prefix (`N8N_WP_`)
- `$base_dir`: Base directory for classes (`includes/`)

**Methods:**
- `register()`: Registers the autoloader with PHP
- `autoload($class)`: Loads class files based on naming convention

**Naming Convention:**
- Class: `N8N_WP_Database` → File: `class-database.php`
- Class: `N8N_WP_API` → File: `class-api.php`

### `N8N_WP_Plugin` (class-plugin.php)
Main plugin orchestrator that manages components and hooks.

**Properties:**
- `$database`: Database instance
- `$auth`: Authentication instance
- `$api`: API instance

**Methods:**
- `__construct()`: Initializes hooks and components
- `init_hooks()`: Registers activation/deactivation hooks
- `init_components()`: Creates instances of Database, Auth, and API
- `activate()`: Runs on plugin activation
- `deactivate()`: Runs on plugin deactivation
- `register_rest_routes()`: Registers REST API routes

### `N8N_WP_Database` (class-database.php)
Handles all database operations (CRUD).

**Properties:**
- `$table_name`: Database table name with prefix
- `$wpdb`: WordPress database object

**Methods:**
- `create_table()`: Creates custom database table
- `insert($workflow_id, $data, $metadata)`: Insert new record
- `get($workflow_id, $limit, $offset)`: Get records with pagination
- `get_by_id($id)`: Get single record by ID
- `update($id, $data, $metadata)`: Update existing record
- `delete($id)`: Delete record by ID
- `exists($id)`: Check if record exists
- `get_last_error()`: Get last database error

**Features:**
- Automatic JSON encoding/decoding
- Prepared statements for security
- Error handling

### `N8N_WP_Auth` (class-auth.php)
Handles API authentication and authorization.

**Methods:**
- `check_permission($request)`: Validates API key from header or query parameter

**Authentication Flow:**
1. Check for `X-N8N-API-Key` header
2. Fallback to `api_key` query parameter
3. Compare with stored API key
4. Return true or WP_Error

### `N8N_WP_API` (class-api.php)
Manages REST API endpoints and request handling.

**Properties:**
- `$database`: Database instance (injected)
- `$auth`: Auth instance (injected)

**Methods:**
- `register_routes()`: Registers all REST API endpoints
- `insert_data($request)`: POST endpoint handler
- `get_data($request)`: GET endpoint handler (list)
- `get_single_data($request)`: GET endpoint handler (single)
- `update_data($request)`: PUT endpoint handler
- `delete_data($request)`: DELETE endpoint handler

**Endpoints Registered:**
- `POST /wp-json/n8n/v1/insert`
- `GET /wp-json/n8n/v1/data`
- `GET /wp-json/n8n/v1/data/{id}`
- `PUT /wp-json/n8n/v1/update/{id}`
- `DELETE /wp-json/n8n/v1/delete/{id}`

#### `uninstall.php` (Cleanup Script)
Executed when the plugin is uninstalled through WordPress admin.

**Actions:**
- Drops the custom database table (`wp_n8n_data`)
- Deletes plugin options from wp_options table
- Cleans up transients and cached data

**Note:** This file is NOT part of the autoloader and runs independently.

## OOP Design Patterns Used

### 1. Dependency Injection
The `N8N_WP_API` class receives `Database` and `Auth` instances via constructor:

```php
public function __construct($database, $auth) {
    $this->database = $database;
    $this->auth = $auth;
}
```

**Benefits:**
- Loose coupling between components
- Easy to test and mock dependencies
- Clear dependencies visibility

### 2. Single Responsibility Principle
Each class has one clear responsibility:
- **Database**: Data persistence
- **Auth**: Security and authentication
- **API**: HTTP request/response handling
- **Plugin**: Component orchestration
- **Autoloader**: Class loading

### 3. Encapsulation
Private properties and public methods ensure proper data access:

```php
class N8N_WP_Database {
    private $table_name;  // Encapsulated
    private $wpdb;        // Encapsulated
    
    public function insert($workflow_id, $data, $metadata) {
        // Public interface
    }
}
```

## Component Interaction Flow

### Plugin Initialization
```
1. WordPress loads n8n-wp-integration.php
2. Constants are defined
3. Autoloader is loaded and registered
4. N8N_WP_Plugin is instantiated
5. Plugin creates Database, Auth, and API instances
6. Hooks are registered
```

### API Request Flow (Example: Insert Data)
```
1. Client sends POST to /wp-json/n8n/v1/insert
2. WordPress REST API routes to N8N_WP_API::insert_data()
3. Auth checks permission via N8N_WP_Auth::check_permission()
4. If authorized, API calls Database::insert()
5. Database performs INSERT query
6. Result is returned via WP_REST_Response
```

## Advantages of OOP Structure

### ✅ Maintainability
- Easy to find and fix bugs
- Each class is focused and manageable
- Changes in one class don't affect others

### ✅ Extensibility
- New features can be added as new classes
- Existing classes can be extended via inheritance
- Open/Closed Principle: Open for extension, closed for modification

### ✅ Testability
- Classes can be unit tested independently
- Dependencies can be mocked
- Clear interfaces make testing easier

### ✅ Reusability
- Database class can be reused in other contexts
- Auth mechanism can be extended for different auth types
- API endpoints can be easily added or modified

### ✅ Code Organization
- Related code is grouped together
- Clear file structure
- Easy navigation for developers


## Documentation Files

#### `README.md` (Main Documentation)
Comprehensive documentation including:
- Feature list
- Installation instructions
- API key configuration
- Complete API documentation with examples
- Database schema
- Error handling guide
- Security recommendations
- Requirements and support information

### Example Files

#### `examples/curl-examples.sh` (Bash/cURL Examples)
Executable bash script with cURL commands demonstrating:
- INSERT: Creating new records
- GET: Retrieving all data with pagination
- GET: Filtering by workflow_id
- GET: Retrieving single records
- PUT: Updating records
- DELETE: Removing records

**Usage:**
```bash
# Update configuration variables in the script
bash examples/curl-examples.sh
```

#### `examples/test-api.php` (PHP Examples)
PHP script demonstrating API usage with complete examples:
- Making authenticated requests
- Inserting data with metadata
- Retrieving and filtering data
- Updating records
- Deleting records

**Usage:**
```bash
# Update configuration variables
php examples/test-api.php
```

#### `examples/n8n-workflow-example.json` (n8n Workflow)
Ready-to-import n8n workflow template showing:
- Webhook trigger node
- HTTP Request node configured for WordPress API
- Response node for webhook feedback
- Proper header authentication setup
- Data transformation examples

**Usage:**
1. Import into n8n
2. Update WordPress URL and API key
3. Activate workflow

## Database Schema

### Table: `wp_n8n_data`

```sql
CREATE TABLE wp_n8n_data (
  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  workflow_id varchar(255) NOT NULL,
  data longtext NOT NULL,
  metadata longtext DEFAULT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY workflow_id (workflow_id),
  KEY created_at (created_at)
);
```

**Column Descriptions:**
- `id`: Auto-incrementing primary key
- `workflow_id`: Identifier for the n8n workflow (indexed for fast lookups)
- `data`: JSON-encoded data from the workflow (stored as longtext)
- `metadata`: Optional JSON-encoded metadata (stored as longtext)
- `created_at`: Timestamp when record was created (indexed)
- `updated_at`: Timestamp when record was last modified

## REST API Endpoints

### Base URL
`https://your-site.com/wp-json/n8n/v1/`

### Available Endpoints

1. **POST** `/insert` - Insert new data
2. **GET** `/data` - Get all data (with pagination and filtering)
3. **GET** `/data/{id}` - Get single record
4. **PUT** `/update/{id}` - Update existing record
5. **DELETE** `/delete/{id}` - Delete record

## Authentication

The plugin supports two authentication methods:

### 1. HTTP Header (Recommended)
```
X-N8N-API-Key: your-api-key
```

### 2. Query Parameter
```
?api_key=your-api-key
```

## WordPress Hooks Used

### Activation Hook
```php
register_activation_hook(__FILE__, array($this, 'activate'));
```
Triggers when plugin is activated - creates database table.

### Deactivation Hook
```php
register_deactivation_hook(__FILE__, array($this, 'deactivate'));
```
Triggers when plugin is deactivated - flushes rewrite rules.

### REST API Hook
```php
add_action('rest_api_init', array($this, 'register_rest_routes'));
```
Registers custom REST API endpoints.

## Security Features

1. **API Key Authentication**: Optional but recommended for production
2. **Data Sanitization**: All inputs are sanitized using WordPress functions
3. **SQL Injection Prevention**: Uses prepared statements with `$wpdb->prepare()`
4. **Direct Access Prevention**: Checks for `ABSPATH` constant
5. **REST API Permission Callbacks**: Validates requests before processing
6. **JSON Encoding**: Safely handles complex data structures

## Data Flow

### Insert Data Flow
1. n8n workflow sends POST request to `/insert` endpoint
2. Plugin validates API key (if configured)
3. Plugin sanitizes and validates input data
4. Data is JSON-encoded if necessary
5. Record is inserted into database using prepared statement
6. Response sent back with inserted record ID

### Retrieve Data Flow
1. Client sends GET request to `/data` endpoint
2. Plugin validates API key
3. Query parameters are sanitized
4. SQL query is built with filters and pagination
5. Results are retrieved and JSON-decoded
6. Response sent with data array and count

## Error Handling

The plugin returns standard HTTP status codes:
- **200**: Success (GET, PUT, DELETE)
- **201**: Created (POST)
- **401**: Unauthorized (Invalid API key)
- **404**: Not Found (Record doesn't exist)
- **500**: Server Error (Database errors)

## WordPress Functions Used

### Database Functions
- `$wpdb->insert()`: Insert records
- `$wpdb->update()`: Update records
- `$wpdb->delete()`: Delete records
- `$wpdb->get_results()`: Retrieve multiple records
- `$wpdb->get_row()`: Retrieve single record
- `$wpdb->prepare()`: Prepare SQL statements
- `dbDelta()`: Create/update tables

### Sanitization Functions
- `sanitize_text_field()`: Sanitize text inputs
- `absint()`: Convert to absolute integer

### WordPress Core Functions
- `current_time()`: Get current time in WordPress timezone
- `get_option()`: Retrieve options
- `add_option()`: Add options
- `delete_option()`: Remove options
- `flush_rewrite_rules()`: Refresh permalinks

### REST API Functions
- `register_rest_route()`: Register API endpoints
- `WP_REST_Response()`: Create API responses
- `WP_Error()`: Create error responses

## Best Practices Implemented

1. **WordPress Coding Standards**: Follows WordPress PHP coding standards
2. **Security First**: Multiple layers of security validation
3. **Database Efficiency**: Proper indexing on frequently queried columns
4. **Extensibility**: Clean class structure for easy modifications
5. **Documentation**: Comprehensive inline and external documentation
6. **Error Handling**: Proper error messages and status codes
7. **Internationalization Ready**: Text domain defined for future translation support
8. **Clean Uninstall**: Removes all traces when uninstalled

## Testing Recommendations

1. **Unit Testing**: Test individual methods with various inputs
2. **Integration Testing**: Test complete workflows from n8n to WordPress
3. **Security Testing**: Verify API key validation works correctly
4. **Performance Testing**: Test with large datasets and high request volumes
5. **Edge Cases**: Test with malformed data, missing parameters, invalid IDs

## Customization Options

### Change Table Name
Modify in `__construct()`:
```php
$this->table_name = $wpdb->prefix . 'custom_table_name';
```

### Add Custom Fields to Table
Update `create_table()` method with additional columns.

### Extend API Endpoints
Add new routes in `register_rest_routes()` method.

### Custom Authentication
Modify `check_permission()` method for different auth schemes.

## Performance Considerations

1. **Indexes**: Table has indexes on `workflow_id` and `created_at`
2. **Pagination**: Default limit prevents overwhelming queries
3. **JSON Storage**: Uses longtext for flexible data structures
4. **Prepared Statements**: Optimized query execution

## Maintenance

### Updating the Plugin
1. Test in staging environment first
2. Backup database before major updates
3. Check for WordPress compatibility
4. Update version number in plugin header

### Database Migrations
If table structure changes:
1. Update version constant
2. Add migration logic in `activate()`
3. Use `dbDelta()` for safe schema updates

## How to Extend the Plugin

### Adding a New Class

1. **Create the class file** in `includes/` directory:
   ```php
   // includes/class-logger.php
   <?php
   if (!defined('ABSPATH')) {
       exit;
   }
   
   class N8N_WP_Logger {
       public function log($message) {
           error_log('[n8n-wp-integration] ' . $message);
       }
   }
   ```

2. **The autoloader will automatically load it** when you use:
   ```php
   $logger = new N8N_WP_Logger();
   ```

### Adding a New API Endpoint

1. **Add method in `class-api.php`**:
   ```php
   public function custom_endpoint($request) {
       // Your logic here
       return new WP_REST_Response(array('success' => true), 200);
   }
   ```

2. **Register route in `register_routes()` method**:
   ```php
   register_rest_route('n8n/v1', '/custom', array(
       'methods' => 'POST',
       'callback' => array($this, 'custom_endpoint'),
       'permission_callback' => array($this->auth, 'check_permission'),
   ));
   ```

### Adding Custom Database Methods

Add methods to `class-database.php`:
```php
public function get_by_workflow($workflow_id) {
    return $this->wpdb->get_results(
        $this->wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE workflow_id = %s",
            $workflow_id
        ),
        ARRAY_A
    );
}
```

### Creating a Child Class (Inheritance)

```php
// includes/class-advanced-database.php
class N8N_WP_Advanced_Database extends N8N_WP_Database {
    public function advanced_query($params) {
        // Extended functionality
    }
}
```

### Adding Hooks for Extensibility

In `class-api.php` or other classes:
```php
public function insert_data($request) {
    // Before insert hook
    do_action('n8n_wp_before_insert', $request);
    
    $result = $this->database->insert($workflow_id, $data, $metadata);
    
    // After insert hook
    do_action('n8n_wp_after_insert', $result, $request);
    
    return $response;
}
```

## Migration Guide (From Old to New Structure)

### What Changed?

**Old Structure (Monolithic):**
- Single file with 513 lines
- All logic in one `N8N_WP_Integration` class
- Manual includes required for extensions

**New Structure (OOP):**
- 6 files with clear separation
- 5 specialized classes
- Automatic class loading
- Dependency injection

### Backward Compatibility

The refactored plugin maintains **100% backward compatibility**:
- Same REST API endpoints
- Same database schema
- Same functionality
- Same authentication mechanism

**No changes required** for existing n8n workflows or API clients.

### For Plugin Developers

If you extended the old plugin, update your code:

**Old way:**
```php
$plugin = new N8N_WP_Integration();
$plugin->insert_data($request);
```

**New way:**
```php
// Access via components
$database = new N8N_WP_Database();
$database->insert($workflow_id, $data, $metadata);
```

## Autoloader Details

### How It Works

The autoloader uses PHP's `spl_autoload_register()` to automatically load classes:

1. **Class naming convention**: `N8N_WP_ClassName`
2. **File naming convention**: `class-classname.php`
3. **Location**: `includes/` directory

### Examples

| Class Name | File Path |
|-----------|-----------|
| `N8N_WP_Database` | `includes/class-database.php` |
| `N8N_WP_API` | `includes/class-api.php` |
| `N8N_WP_Auth` | `includes/class-auth.php` |
| `N8N_WP_Custom_Feature` | `includes/class-custom-feature.php` |

### Benefits

- **No manual includes**: Classes are loaded on demand
- **Performance**: Only loads classes when needed
- **Scalability**: Easy to add new classes
- **PSR-4 Compliance**: Follows PHP standards

## Code Quality Improvements

### Before Refactoring
```php
// 513 lines in one file
// Mixed responsibilities
// Hard to test
// Difficult to maintain
class N8N_WP_Integration {
    // Database operations
    // API endpoints
    // Authentication
    // Activation/deactivation
    // All mixed together
}
```

### After Refactoring
```php
// 32 lines bootstrap file
// Clear separation
// Easy to test
// Simple to maintain

// Main file
require_once 'includes/class-autoloader.php';
$autoloader = new N8N_WP_Autoloader();
$autoloader->register();
new N8N_WP_Plugin();

// Each class has single responsibility
// Database.php    - Data operations
// API.php         - REST endpoints
// Auth.php        - Security
// Plugin.php      - Orchestration
// Autoloader.php  - Class loading
```

## Summary

This OOP refactoring provides:
- ✅ Better code organization
- ✅ Improved maintainability
- ✅ Enhanced testability
- ✅ Easier extensibility
- ✅ PSR-4 autoloading
- ✅ Dependency injection
- ✅ Single Responsibility Principle
- ✅ Full backward compatibility
