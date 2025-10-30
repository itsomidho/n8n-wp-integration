# Tests Directory

This directory contains tests for the n8n WordPress Integration plugin.

## Available Tests

### `test-structure.php`
Tests the OOP structure and verifies the refactored plugin architecture.

**What it tests:**
- Autoloader file exists
- Autoloader class loads correctly
- Autoloader registers successfully
- All class files exist
- PHP syntax is valid for all files
- Class naming follows PSR-4 convention
- Main file is optimized (~32 lines)
- Directory structure is correct

**Run the test:**
```bash
php tests/test-structure.php
```

**Expected output:**
```
All structure tests passed! ✓
```

## Running Tests

Tests can be run without WordPress installed since they verify the structure and syntax only.

For integration tests with WordPress, the plugin should be installed in a WordPress environment.

## Future Tests

Planned test coverage:
- Unit tests for Database class
- Unit tests for Auth class
- Unit tests for API class
- Integration tests with WordPress REST API
- End-to-end tests with n8n workflows
