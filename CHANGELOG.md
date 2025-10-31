# Changelog

All notable changes to the n8n WordPress Integration plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.0] - 2025-10-31

### Added - Admin Settings Page

#### Admin Settings Page
- **New admin settings page** for API key management at Settings → n8n Integration
- Modern, clean UI with WordPress design standards
- One-click API key generation with secure random keys (64 characters)
- Copy-to-clipboard functionality for easy key copying
- API key deletion with confirmation dialog
- Real-time status indicator showing if API key is configured
- AJAX-powered interactions for smooth user experience
- Responsive design that works on all devices
- Usage instructions with example cURL command
- Nonce verification for all AJAX requests

#### Features
- Read-only input field displaying current API key
- "Generate API Key" button creates new 64-character secure key
- "Copy" button for one-click clipboard copy
- "Delete API Key" button with confirmation (when key exists)
- Visual status badges (Configured/Not Configured)
- Warning notice when API key is not configured
- Success notifications after key generation
- Inline CSS for modern styling

### Added
- `includes/class-admin-settings.php` - Admin settings page class
- Admin menu item under Settings → n8n Integration
- AJAX handlers for key generation and deletion
- Modern UI with custom CSS styling

### Improved
- User experience for API key management
- Easier onboarding for new users
- Better visibility of API key configuration status

## [1.5.0] - 2025-10-31

### Changed - Added PHP Namespaces

#### Namespace Implementation
- **Added proper PHP namespaces to all classes** using `N8N_WP` namespace
- All classes now follow PSR-4 naming convention with namespaces
- Updated class names:
  - `N8N_WP_Plugin` → `N8N_WP\Plugin`
  - `N8N_WP_Database` → `N8N_WP\Database`
  - `N8N_WP_API` → `N8N_WP\API`
  - `N8N_WP_Auth` → `N8N_WP\Auth`
  - `N8N_WP_Admin_Notices` → `N8N_WP\Admin_Notices`
- Regenerated Composer autoloader for optimized class loading
- Updated all class references throughout the codebase

### Added
- PHP namespace declarations in all class files
- Proper PSR-4 autoloading with namespaces

### Improved
- Better code organization with namespaces
- Follows modern PHP standards
- Cleaner class naming without prefixes

## [1.4.0] - 2025-10-31

### Changed - Admin Notices Separation and Header-Only Authentication

#### Admin Notices Refactoring
- **Separated admin notices into independent file** `includes/class-admin-notices.php`
- Admin notice logic moved out of main plugin file for better organization
- Created admin notices handler class for handling all admin notifications
- Main plugin file now cleaner and more focused

#### Enhanced Authentication Security
- **Removed query parameter authentication** - API key now accepted via header only
- Only `X-N8N-API-Key` header is accepted for authentication
- Removed fallback to `api_key` query parameter for improved security
- Updated error message to reflect header-only requirement
- Prevents API key exposure in server logs and browser history

### Added
- `includes/class-admin-notices.php` - New admin notices handler class

### Removed
- Query parameter API key authentication (`?api_key=...`)

### Security
- API keys no longer accepted via query parameters (prevents exposure in logs)
- Header-only authentication reduces risk of API key leakage

### Breaking Changes
- **Query parameter authentication removed** - All API requests must include `X-N8N-API-Key` header
- **Migration Required**: Update n8n workflows to use header authentication instead of query parameters

## [1.3.0] - 2025-10-31

### Changed - Enhanced Authentication and Composer-Only Approach

#### Enhanced Auth Class
- **Significantly improved Auth class** with additional security features:
  - Added timing-safe comparison using `hash_equals()` to prevent timing attacks
  - Implemented API key sanitization with `sanitize_text_field()`
  - Added API key format validation (minimum 32 characters, alphanumeric with dash/underscore)
  - Extracted API key retrieval logic to separate method for better organization
  - Added failed authentication logging (when WP_DEBUG is enabled)
  - Implemented client IP detection for security logging
  - Added helper methods: `get_api_key()`, `set_api_key()`, `delete_api_key()`, `is_valid_api_key_format()`
  - Added static method `generate_api_key()` for secure key generation
  - Better error codes: `n8n_api_key_not_configured`, `n8n_api_key_missing`, `n8n_api_key_invalid`

#### Composer-Only Autoloading
- **Removed custom autoloader fallback** - Composer is now required
- Plugin displays admin notice if `vendor/autoload.php` is missing
- Clear instructions to run `composer install --no-dev`
- Stops plugin execution gracefully when autoloader is not found
- Deleted `includes/class-autoloader.php` file

### Removed
- Custom PSR-4 autoloader class (now Composer-only)
- Fallback autoloader logic from main plugin file

### Security
- Timing-safe API key comparison prevents timing attack vulnerabilities
- API key validation ensures minimum security standards
- Failed authentication attempts are logged for security monitoring

### Breaking Changes
- **Composer is now mandatory** - The plugin will not run without Composer autoloader
- **Migration Required**: Run `composer install --no-dev` before upgrading

## [1.2.0] - 2025-10-30

### Changed - Security and Autoloading Improvements

#### Security Enhancement
- **API key is now required** - Removed backward compatibility that allowed empty API keys
- All API endpoints now require a configured API key
- Better error messages for missing or invalid API keys:
  - "API key is not configured" when no key is set in WordPress options
  - "API key is required" when request doesn't include the key
  - "Invalid API key" when provided key doesn't match

#### Autoloading Improvement
- **Added Composer support** with `composer.json`
- Plugin now uses Composer's PSR-4 autoloader when available
- Fallback to custom autoloader if Composer is not installed
- Optimized autoloader configuration for better performance

### Added
- `composer.json` with PSR-4 autoload configuration
- Composer installation step in README.md
- Better documentation for required API key setup

### Breaking Changes
- **API key is now mandatory** - Previously, the plugin allowed access without an API key for backward compatibility. This is no longer the case.
- **Migration Required**: Ensure you set the `n8n_wp_api_key` option before upgrading to avoid API access issues.

## [1.1.0] - 2025-10-30

### Changed - Major Refactoring to OOP Architecture
- **Refactored entire plugin to Object-Oriented Programming (OOP)** structure
- Main plugin file reduced from 513 lines to 32 lines (bootstrap only)
- Created modular class structure with separation of concerns
- Implemented PSR-4 compliant autoloader for automatic class loading
- Organized code into 5 specialized classes in `includes/` directory:
  - `N8N_WP_Autoloader` - PSR-4 autoloader
  - `N8N_WP_Plugin` - Main plugin orchestrator
  - `N8N_WP_Database` - Database operations (CRUD)
  - `N8N_WP_API` - REST API endpoints handler
  - `N8N_WP_Auth` - Authentication and authorization
- Implemented dependency injection pattern for loose coupling
- Applied Single Responsibility Principle to all classes
- Improved code maintainability and testability

### Added
- PSR-4 autoloader (`class-autoloader.php`)
- Comprehensive documentation for OOP structure
- `includes/README.md` explaining class organization
- Extension guide in PLUGIN-STRUCTURE.md
- Migration guide for developers

### Technical Improvements
- Better encapsulation with private/public methods
- Cleaner separation between database, API, and auth logic
- Easier to extend and add new features
- More testable code structure
- Follows WordPress and PHP best practices

### Backward Compatibility
- **100% backward compatible** - No breaking changes
- Same REST API endpoints
- Same database schema
- Same functionality
- Existing n8n workflows continue to work without modifications

## [1.0.0] - 2025-10-30

### Added
- Initial release of n8n WordPress Integration plugin
- Custom MySQL table (`wp_n8n_data`) for storing n8n workflow data
- REST API endpoints for full CRUD operations:
  - POST `/wp-json/n8n/v1/insert` - Insert new data
  - GET `/wp-json/n8n/v1/data` - Retrieve data with pagination and filtering
  - GET `/wp-json/n8n/v1/data/{id}` - Get single record by ID
  - PUT `/wp-json/n8n/v1/update/{id}` - Update existing record
  - DELETE `/wp-json/n8n/v1/delete/{id}` - Delete record by ID
- API key authentication via HTTP headers or query parameters
- JSON data storage support for complex data structures
- Metadata field for additional context storage
- Automatic timestamp tracking (created_at, updated_at)
- Database indexes on workflow_id and created_at for performance
- Query filtering by workflow_id
- Pagination support with limit and offset parameters
- Comprehensive error handling with proper HTTP status codes
- Input sanitization and SQL injection prevention
- WordPress coding standards compliance
- Clean uninstall script to remove all plugin data
- Complete documentation:
  - README.md with API documentation and usage examples
  - INSTALL.md with detailed installation guide
  - PLUGIN-STRUCTURE.md with technical documentation
  - CHANGELOG.md (this file)
- Example files:
  - cURL examples script (curl-examples.sh)
  - PHP test script (test-api.php)
  - n8n workflow template (n8n-workflow-example.json)
- .gitignore file for clean repository

### Security
- Optional API key authentication for all endpoints
- Prepared SQL statements to prevent SQL injection
- Input sanitization using WordPress functions
- Direct file access prevention
- REST API permission callbacks

## [Unreleased]

### Planned Features
- Admin dashboard for viewing stored data
- Bulk operations support
- Webhook support for real-time notifications
- Data export functionality (CSV, JSON)
- Advanced filtering options
- Rate limiting for API requests
- Logging system for debugging
- Custom data validation rules
- Multi-site support
- Scheduled data cleanup
- API usage statistics

### Under Consideration
- GraphQL API support
- OAuth2 authentication option
- Data encryption at rest
- Automated backups
- Custom post type integration
- WooCommerce integration
- Contact Form 7 integration
- Gravity Forms integration

---

## Version History

- **1.0.0** - Initial release (2025-10-30)
