# n8n WordPress Integration Plugin

A WordPress plugin that integrates n8n workflows with WordPress through a custom REST API. This plugin creates a custom MySQL table and provides RESTful endpoints for inserting, retrieving, updating, and deleting data from n8n workflows.

**Built with Object-Oriented Programming (OOP)** principles featuring modular architecture, PSR-4 autoloading, and dependency injection.

## Features

- ✅ Custom MySQL table for storing n8n workflow data
- ✅ RESTful API endpoints for full CRUD operations
- ✅ API key authentication for security
- ✅ JSON data support
- ✅ Metadata storage for additional context
- ✅ Automatic timestamp tracking (created_at, updated_at)
- ✅ Query filtering and pagination
- ✅ Clean uninstall (removes table and options)
- ✅ **OOP Architecture** with separation of concerns
- ✅ **PSR-4 Autoloading** for automatic class loading
- ✅ **Modular Design** easy to extend and maintain

## Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/itsomidho/n8n-wp-integration.git
   ```

2. Install Composer dependencies:
   ```bash
   cd n8n-wp-integration
   composer install --no-dev
   ```

3. Activate the plugin through the WordPress admin panel:
   - Navigate to **Plugins** → **Installed Plugins**
   - Find "n8n WordPress Integration"
   - Click **Activate**

4. The plugin will automatically create the custom database table `wp_n8n_data` (prefix may vary based on your WordPress configuration)

## Architecture

This plugin follows **modern OOP principles** with a clean, modular structure:

```
n8n-wp-integration/
├── composer.json              # Composer configuration
├── n8n-wp-integration.php     # Bootstrap with autoloader check
├── includes/
│   ├── class-plugin.php       # Main orchestrator
│   ├── class-database.php     # Database operations
│   ├── class-api.php          # REST API endpoints
│   └── class-auth.php         # Enhanced authentication
└── uninstall.php              # Cleanup script
```

**Key Benefits:**
- **Separation of Concerns**: Each class has a single responsibility
- **Composer PSR-4 Autoloading**: Professional dependency management
- **Enhanced Security**: Improved Auth class with timing-safe comparisons
- **Admin Notices**: Clear error messages if Composer autoloader is missing
- **Composer PSR-4 Autoloading**: Automatic class loading with Composer
- **Dependency Injection**: Loose coupling for better testability
- **Easy to Extend**: Add new features without modifying existing code
- **Maintainable**: Clear structure makes updates simple

For detailed architecture documentation, see [PLUGIN-STRUCTURE.md](PLUGIN-STRUCTURE.md).

## Configuration

### Setting up API Key (Required)

**API key authentication is required** for all API endpoints. Set up your API key:

1. Add this line to your `wp-config.php` file or use the WordPress options:
   ```php
   update_option('n8n_wp_api_key', 'your-secure-api-key-here');
   ```

2. Or run this in WordPress admin via a plugin like Code Snippets:
   ```php
   update_option('n8n_wp_api_key', 'your-secure-api-key-here');
   ```

Replace `your-secure-api-key-here` with a strong, random string.

**Note:** Without a configured API key, all API requests will be rejected with a 401 Unauthorized error.

## API Documentation

### Base URL

All endpoints are prefixed with: `https://your-site.com/wp-json/n8n/v1/`

### Authentication

Include the API key in one of two ways:

1. **HTTP Header** (recommended):
   ```
   X-N8N-API-Key: your-secure-api-key-here
   ```

2. **Query Parameter**:
   ```
   ?api_key=your-secure-api-key-here
   ```

### Endpoints

#### 1. Insert Data

**POST** `/wp-json/n8n/v1/insert`

Insert new data from an n8n workflow.

**Request Body:**
```json
{
  "workflow_id": "my-workflow-123",
  "data": {
    "name": "John Doe",
    "email": "john@example.com",
    "message": "Hello from n8n!"
  },
  "metadata": {
    "source": "contact-form",
    "ip": "192.168.1.1"
  }
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Data inserted successfully",
  "id": 1,
  "data": {
    "id": 1,
    "workflow_id": "my-workflow-123",
    "data": {
      "name": "John Doe",
      "email": "john@example.com",
      "message": "Hello from n8n!"
    },
    "metadata": {
      "source": "contact-form",
      "ip": "192.168.1.1"
    }
  }
}
```

#### 2. Get Data

**GET** `/wp-json/n8n/v1/data`

Retrieve data with optional filtering and pagination.

**Query Parameters:**
- `workflow_id` (optional): Filter by workflow ID
- `limit` (optional, default: 10): Number of records to return
- `offset` (optional, default: 0): Offset for pagination

**Example:**
```
GET /wp-json/n8n/v1/data?workflow_id=my-workflow-123&limit=20&offset=0
```

**Response (200 OK):**
```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": "2",
      "workflow_id": "my-workflow-123",
      "data": {...},
      "metadata": {...},
      "created_at": "2025-10-30 19:21:00",
      "updated_at": "2025-10-30 19:21:00"
    },
    {
      "id": "1",
      "workflow_id": "my-workflow-123",
      "data": {...},
      "metadata": {...},
      "created_at": "2025-10-30 19:20:00",
      "updated_at": "2025-10-30 19:20:00"
    }
  ]
}
```

#### 3. Get Single Record

**GET** `/wp-json/n8n/v1/data/{id}`

Retrieve a specific record by ID.

**Example:**
```
GET /wp-json/n8n/v1/data/1
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": "1",
    "workflow_id": "my-workflow-123",
    "data": {...},
    "metadata": {...},
    "created_at": "2025-10-30 19:20:00",
    "updated_at": "2025-10-30 19:20:00"
  }
}
```

#### 4. Update Data

**PUT** `/wp-json/n8n/v1/update/{id}`

Update an existing record.

**Request Body:**
```json
{
  "data": {
    "name": "Jane Doe",
    "email": "jane@example.com"
  },
  "metadata": {
    "updated": true
  }
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Data updated successfully",
  "id": 1
}
```

#### 5. Delete Data

**DELETE** `/wp-json/n8n/v1/delete/{id}`

Delete a record by ID.

**Example:**
```
DELETE /wp-json/n8n/v1/delete/1
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Data deleted successfully"
}
```

## Usage Examples

### Using cURL

**Insert Data:**
```bash
curl -X POST https://your-site.com/wp-json/n8n/v1/insert \
  -H "Content-Type: application/json" \
  -H "X-N8N-API-Key: your-secure-api-key-here" \
  -d '{
    "workflow_id": "my-workflow-123",
    "data": {
      "name": "John Doe",
      "email": "john@example.com"
    }
  }'
```

**Get Data:**
```bash
curl -X GET "https://your-site.com/wp-json/n8n/v1/data?workflow_id=my-workflow-123&limit=10" \
  -H "X-N8N-API-Key: your-secure-api-key-here"
```

### Using n8n HTTP Request Node

1. Add an **HTTP Request** node to your workflow
2. Configure the node:
   - **Method**: POST (or GET, PUT, DELETE as needed)
   - **URL**: `https://your-site.com/wp-json/n8n/v1/insert`
   - **Authentication**: None (we use custom header)
   - **Headers**:
     - Name: `X-N8N-API-Key`
     - Value: `your-secure-api-key-here`
   - **Body**:
     ```json
     {
       "workflow_id": "{{$node["Trigger"].json["workflow_id"]}}",
       "data": "{{$json}}"
     }
     ```

### Using JavaScript (Fetch API)

```javascript
fetch('https://your-site.com/wp-json/n8n/v1/insert', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-N8N-API-Key': 'your-secure-api-key-here'
  },
  body: JSON.stringify({
    workflow_id: 'my-workflow-123',
    data: {
      name: 'John Doe',
      email: 'john@example.com'
    },
    metadata: {
      source: 'web-form'
    }
  })
})
.then(response => response.json())
.then(data => console.log(data))
.catch(error => console.error('Error:', error));
```

## Database Schema

The plugin creates a table with the following structure:

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

## Error Handling

The API returns appropriate HTTP status codes:

- **200 OK**: Successful GET, PUT, DELETE operations
- **201 Created**: Successful POST (insert) operation
- **401 Unauthorized**: Invalid or missing API key
- **404 Not Found**: Record not found
- **500 Internal Server Error**: Database or server error

Error response format:
```json
{
  "code": "rest_forbidden",
  "message": "Invalid API key",
  "data": {
    "status": 401
  }
}
```

## Security Considerations

1. **Always set an API key** in production environments
2. Use **HTTPS** for all API requests to encrypt data in transit
3. Store API keys securely (environment variables, secret managers)
4. Regularly rotate API keys
5. Monitor API usage for unusual patterns
6. Consider rate limiting for production use

## Uninstalling

When you uninstall the plugin through WordPress:

1. The custom database table `wp_n8n_data` will be deleted
2. All stored data will be permanently removed
3. Plugin options will be cleaned up

**Warning**: This action cannot be undone. Make sure to backup your data before uninstalling.

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/itsomidho/n8n-wp-integration).

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### Version 1.0.0
- Initial release
- Custom REST API endpoints for CRUD operations
- Custom MySQL table for data storage
- API key authentication
- JSON data support
- Metadata support
- Automatic timestamp tracking
