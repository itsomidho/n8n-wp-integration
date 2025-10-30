# Plugin Structure Documentation

## File Overview

### Core Plugin Files

#### `n8n-wp-integration.php` (Main Plugin File)
The main plugin file containing all the core functionality:

**Key Components:**
- **Plugin Headers**: WordPress plugin metadata and information
- **Constants**: Plugin version, directory paths, and URL definitions
- **Main Class** (`N8N_WP_Integration`): Handles all plugin operations

**Main Class Methods:**
- `__construct()`: Initializes the plugin, sets up hooks
- `activate()`: Runs on plugin activation, creates database table
- `deactivate()`: Runs on plugin deactivation, flushes rewrite rules
- `create_table()`: Creates the custom MySQL table for storing n8n data
- `register_rest_routes()`: Registers all REST API endpoints
- `check_permission()`: Validates API key for authentication
- `insert_data()`: Handles POST requests to insert data
- `get_data()`: Handles GET requests to retrieve data (with pagination)
- `get_single_data()`: Retrieves a single record by ID
- `update_data()`: Handles PUT requests to update existing records
- `delete_data()`: Handles DELETE requests to remove records

#### `uninstall.php` (Cleanup Script)
Executed when the plugin is uninstalled through WordPress admin.

**Actions:**
- Drops the custom database table (`wp_n8n_data`)
- Deletes plugin options from wp_options table
- Cleans up transients and cached data

### Documentation Files

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
