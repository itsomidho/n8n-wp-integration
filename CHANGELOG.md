# Changelog

All notable changes to the n8n WordPress Integration plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
