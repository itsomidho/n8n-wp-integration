# Quick Start Installation Guide

## Prerequisites

Before installing the n8n WordPress Integration plugin, ensure you have:

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Access to WordPress admin panel
- (Optional) n8n instance for workflow automation

## Installation Methods

### Method 1: WordPress Admin Upload (Recommended for beginners)

1. **Download the plugin**
   - Download the plugin as a ZIP file from GitHub
   - Or create a ZIP of the plugin directory

2. **Upload via WordPress Admin**
   - Log in to your WordPress admin panel
   - Navigate to **Plugins** → **Add New**
   - Click **Upload Plugin** button
   - Choose the ZIP file
   - Click **Install Now**

3. **Activate the plugin**
   - Click **Activate Plugin** after installation
   - The plugin will automatically create the database table

### Method 2: FTP/SFTP Upload

1. **Download the plugin files**
   ```bash
   git clone https://github.com/itsomidho/n8n-wp-integration.git
   ```

2. **Upload to WordPress**
   - Connect to your server via FTP/SFTP
   - Navigate to `wp-content/plugins/`
   - Upload the entire `n8n-wp-integration` folder

3. **Activate the plugin**
   - Go to WordPress Admin → **Plugins**
   - Find "n8n WordPress Integration"
   - Click **Activate**

### Method 3: Command Line (for developers)

1. **Navigate to plugins directory**
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   ```

2. **Clone the repository**
   ```bash
   git clone https://github.com/itsomidho/n8n-wp-integration.git
   ```

3. **Install Composer dependencies**
   ```bash
   cd n8n-wp-integration
   composer install --no-dev
   ```

4. **Set proper permissions**
   ```bash
   chmod 755 .
   chmod 644 *.php
   ```

5. **Activate via WP-CLI** (if available)
   ```bash
   wp plugin activate n8n-wp-integration
   ```

## Post-Installation Configuration

### Step 1: Verify Database Table Creation

After activation, verify the table was created:

```sql
SHOW TABLES LIKE '%n8n_data';
```

You should see a table named `wp_n8n_data` (or with your custom prefix).

### Step 2: Set Up API Key (**REQUIRED**)

**API key authentication is now required** for all API endpoints. Set up your API key using one of these methods:

#### Option A: Using WordPress Admin (with Code Snippets plugin)

1. Install and activate the "Code Snippets" plugin
2. Create a new snippet with this code:
   ```php
   update_option('n8n_wp_api_key', 'your-secure-random-api-key-here');
   ```
3. Run it once, then delete or deactivate the snippet

#### Option B: Using functions.php

Add to your theme's `functions.php` file (or create a custom plugin):

```php
// Set n8n API Key (run once, then remove)
add_action('init', function() {
    if (!get_option('n8n_wp_api_key')) {
        update_option('n8n_wp_api_key', 'your-secure-random-api-key-here');
    }
});
```

**Note**: Remove this code after the API key is set to avoid running it on every page load.

#### Option C: Using WP-CLI

```bash
wp option update n8n_wp_api_key 'your-secure-random-api-key-here'
```

#### Option D: Direct Database Update

```sql
INSERT INTO wp_options (option_name, option_value, autoload) 
VALUES ('n8n_wp_api_key', 'your-secure-random-api-key-here', 'yes')
ON DUPLICATE KEY UPDATE option_value = 'your-secure-random-api-key-here';
```

**Generating a Secure API Key:**

Use one of these methods to generate a strong random key:

```bash
# Linux/Mac
openssl rand -base64 32

# Or
cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1

# Or use an online generator
# https://www.random.org/strings/
```

### Step 3: Test the API

Test the API is working with a simple GET request:

```bash
curl -X GET "https://your-site.com/wp-json/n8n/v1/data?limit=1" \
  -H "X-N8N-API-Key: your-secure-random-api-key-here"
```

Expected response:
```json
{
  "success": true,
  "count": 0,
  "data": []
}
```

### Step 4: Insert Test Data

```bash
curl -X POST "https://your-site.com/wp-json/n8n/v1/insert" \
  -H "Content-Type: application/json" \
  -H "X-N8N-API-Key: your-secure-random-api-key-here" \
  -d '{
    "workflow_id": "test-workflow",
    "data": {
      "message": "Hello from n8n!"
    }
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Data inserted successfully",
  "id": 1,
  "data": {...}
}
```

## Integrating with n8n

### Step 1: Import Example Workflow

1. Open your n8n instance
2. Click **Workflows** → **Import from File**
3. Select `examples/n8n-workflow-example.json`
4. Update the workflow settings:
   - Replace `your-wordpress-site.com` with your actual domain
   - Replace `your-secure-api-key-here` with your actual API key

### Step 2: Create a Custom Workflow

1. Create a new workflow in n8n
2. Add an **HTTP Request** node
3. Configure it:
   - **Method**: POST
   - **URL**: `https://your-site.com/wp-json/n8n/v1/insert`
   - **Headers**:
     - Name: `X-N8N-API-Key`
     - Value: `your-api-key`
     - Name: `Content-Type`
     - Value: `application/json`
   - **Body**: 
     ```json
     {
       "workflow_id": "my-workflow",
       "data": "={{ $json }}"
     }
     ```

### Step 3: Test the Integration

1. Activate your n8n workflow
2. Trigger it manually or via webhook
3. Check WordPress database for new records:
   ```sql
   SELECT * FROM wp_n8n_data ORDER BY created_at DESC LIMIT 5;
   ```

## Verifying Installation

### Check Plugin is Active

```bash
wp plugin list
```

Look for `n8n-wp-integration` with status `active`.

### Check Database Table

```sql
DESCRIBE wp_n8n_data;
```

Should show columns: `id`, `workflow_id`, `data`, `metadata`, `created_at`, `updated_at`

### Check REST API Endpoints

Visit in your browser (replace with your domain):
```
https://your-site.com/wp-json/n8n/v1/
```

### Check API Key Protection

Test without API key (should fail if key is set):
```bash
curl -X GET "https://your-site.com/wp-json/n8n/v1/data"
```

Expected response (if API key is configured):
```json
{
  "code": "rest_forbidden",
  "message": "Invalid API key",
  "data": {
    "status": 401
  }
}
```

## Troubleshooting

### Plugin Won't Activate

**Problem**: Error activating plugin

**Solutions**:
1. Check PHP version: `php -v` (must be 7.4+)
2. Check file permissions: `chmod -R 755 n8n-wp-integration`
3. Check error logs: `wp-content/debug.log`
4. Enable WordPress debugging in `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

### Database Table Not Created

**Problem**: Table `wp_n8n_data` doesn't exist

**Solutions**:
1. Deactivate and reactivate the plugin
2. Check database user has CREATE TABLE permissions
3. Manually create table using SQL from README.md
4. Check error logs for database errors

### API Returns 404 Error

**Problem**: Endpoints return "No route was found"

**Solutions**:
1. Flush permalinks: **Settings** → **Permalinks** → **Save Changes**
2. Or use WP-CLI: `wp rewrite flush`
3. Check .htaccess file has WordPress rewrite rules
4. Ensure pretty permalinks are enabled

### API Key Authentication Fails

**Problem**: Getting 401 errors even with correct key

**Solutions**:
1. Verify API key is set: `wp option get n8n_wp_api_key`
2. Check header name is exact: `X-N8N-API-Key`
3. Try query parameter instead: `?api_key=your-key`
4. Check for extra spaces in the key value

### Data Not Inserting

**Problem**: Insert request succeeds but data not in database

**Solutions**:
1. Check database table exists: `SHOW TABLES LIKE '%n8n_data%'`
2. Verify database user has INSERT permissions
3. Check WordPress database connection
4. Review error logs for database errors
5. Test with simple data first

## Security Checklist

After installation, ensure:

- [ ] API key is set and secure (32+ random characters)
- [ ] HTTPS is enabled on your WordPress site
- [ ] WordPress and PHP are up to date
- [ ] File permissions are correct (755 for directories, 644 for files)
- [ ] WordPress debug mode is disabled in production
- [ ] Database user has minimum required permissions
- [ ] Regular backups are configured

## Next Steps

1. ✅ Test all API endpoints with the example scripts
2. ✅ Create your first n8n workflow
3. ✅ Set up monitoring for API requests
4. ✅ Configure regular database backups
5. ✅ Review security settings
6. ✅ Read the full API documentation in README.md

## Getting Help

If you encounter issues:

1. Check the [README.md](README.md) for detailed documentation
2. Review [PLUGIN-STRUCTURE.md](PLUGIN-STRUCTURE.md) for technical details
3. Check WordPress error logs
4. Visit the [GitHub repository](https://github.com/itsomidho/n8n-wp-integration) for support

## Uninstalling

To completely remove the plugin:

1. **Backup your data first!**
   ```sql
   SELECT * FROM wp_n8n_data;
   ```

2. **Deactivate and delete** via WordPress admin
   - Go to **Plugins**
   - Deactivate "n8n WordPress Integration"
   - Click **Delete**

3. **Verify cleanup**
   - Table should be dropped automatically
   - Options should be removed
   - Check: `SHOW TABLES LIKE '%n8n_data%'` (should return empty)

The `uninstall.php` script handles cleanup automatically.
