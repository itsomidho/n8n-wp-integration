# Template System Documentation

## Overview

The n8n WordPress Integration plugin now uses a template-based approach for rendering admin pages, separating the presentation layer from the business logic.

## Architecture

### Controller: `Admin_Settings` Class
- **Location**: `includes/class-admin-settings.php`
- **Responsibility**: Prepare data and load templates
- **Methods**:
  - `render_settings_page()` - Main entry point
  - `load_template()` - Template loading helper

### View: Template Files
- **Location**: `views/admin/`
- **Responsibility**: HTML presentation and user interface
- **Current Templates**:
  - `settings-page.php` - Main admin settings page

## Template Loading Process

1. **Data Preparation**: Controller prepares variables
2. **Template Resolution**: Template path is constructed
3. **Variable Extraction**: Variables are extracted into template scope
4. **Template Inclusion**: PHP template file is included

```php
// In Admin_Settings::render_settings_page()
$variables = compact('api_key', 'has_api_key', 'page_title', 'rest_url');
$this->load_template('settings-page', $variables);
```

## Template Structure

### File Naming Convention
- Template files use `.php` extension
- Located in `views/admin/` directory
- Named descriptively (e.g., `settings-page.php`)

### Template Variables
Templates receive these variables:

| Variable | Type | Description |
|----------|------|-------------|
| `$api_key` | string | Current API key value |
| `$has_api_key` | bool | Whether API key exists |
| `$page_title` | string | WordPress admin page title |
| `$rest_url` | string | REST API endpoint URL |

### Template Security
- All output is properly escaped using WordPress functions:
  - `esc_html()` for text content
  - `esc_attr()` for attributes
  - `esc_html_e()` for translatable text
  - `esc_attr_e()` for translatable attributes

## Benefits

### 1. Separation of Concerns
- **Business Logic**: Stays in PHP classes
- **Presentation**: Isolated in template files
- **Styling**: Handled by Tailwind CSS classes

### 2. Maintainability
- Easy to modify HTML without touching PHP logic
- Clear template structure
- Reusable template loading system

### 3. Flexibility
- Easy to add new templates
- Template variables are clearly defined
- Fallback handling for missing templates

### 4. WordPress Integration
- Follows WordPress coding standards
- Proper escaping and internationalization
- Compatible with WordPress admin styles

## Template Development

### Creating New Templates

1. **Create Template File**:
   ```php
   // views/admin/my-template.php
   <?php
   // Template header with documentation
   // Available variables: $var1, $var2, etc.
   ?>
   <div class="wrap">
       <h1><?php echo esc_html($page_title); ?></h1>
       <!-- Template content -->
   </div>
   ```

2. **Load Template in Controller**:
   ```php
   public function render_my_page() {
       $variables = array(
           'page_title' => 'My Page',
           'data' => $this->get_data()
       );
       $this->load_template('my-template', $variables);
   }
   ```

### Best Practices

1. **Always Escape Output**:
   ```php
   // Good
   <h1><?php echo esc_html($title); ?></h1>
   <input value="<?php echo esc_attr($value); ?>">
   
   // Bad
   <h1><?php echo $title; ?></h1>
   <input value="<?php echo $value; ?>">
   ```

2. **Use WordPress Functions**:
   ```php
   // Translatable text
   <?php esc_html_e('Text to translate', 'n8n-wp-integration'); ?>
   
   // URLs
   <?php echo esc_url($url); ?>
   ```

3. **Document Template Variables**:
   ```php
   <?php
   /**
    * Template Name: Settings Page
    *
    * Available variables:
    * @var string $api_key      Current API key
    * @var bool   $has_api_key  Whether API key exists
    * @var string $page_title   Page title
    * @var string $rest_url     REST API URL
    */
   ?>
   ```

4. **Keep Logic Minimal**:
   ```php
   // Good - simple conditionals
   <?php if ($has_api_key): ?>
       <span class="configured">Configured</span>
   <?php else: ?>
       <span class="not-configured">Not Configured</span>
   <?php endif; ?>
   
   // Bad - complex logic
   <?php
   $status = calculate_complex_status($api_key, $settings, $options);
   if ($status['is_valid'] && $status['has_permissions']) {
       // Complex logic should be in controller
   }
   ?>
   ```

## File Structure

```
views/
└── admin/
    ├── settings-page.php     # Main settings page template
    └── [future-templates]    # Additional templates as needed

includes/
└── class-admin-settings.php # Controller with template loading
```

## Error Handling

The template system includes error handling:

- **Missing Template**: Shows error message instead of breaking
- **Variable Safety**: Uses `extract($variables, EXTR_SKIP)` to prevent overwriting
- **File Security**: Checks file existence before inclusion

## Integration with Tailwind CSS

Templates use Tailwind CSS classes for styling:
- Utility-first approach
- Responsive design ready
- Consistent with WordPress admin interface
- Local build system for optimized CSS

This template system provides a solid foundation for building maintainable, secure, and flexible WordPress admin interfaces.
