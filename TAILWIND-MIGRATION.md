# Tailwind CSS Migration

This document explains the migration from custom CSS to Tailwind CSS for the n8n WordPress Integration plugin.

## Changes Made

### 1. CSS Framework Switch
- **Before**: Custom CSS file (`assets/css/admin-settings.css`)
- **After**: Local Tailwind CSS build (`assets/css/admin-tailwind.css`)

### 2. Build System Setup

#### Dependencies
```json
{
  "devDependencies": {
    "tailwindcss": "^3.3.0"
  }
}
```

#### Build Scripts
```json
{
  "scripts": {
    "build-css": "tailwindcss -i ./assets/css/input.css -o ./assets/css/admin-tailwind.css --watch",
    "build-css-prod": "tailwindcss -i ./assets/css/input.css -o ./assets/css/admin-tailwind.css --minify",
    "build-css-dev": "tailwindcss -i ./assets/css/input.css -o ./assets/css/admin-tailwind.css"
  }
}
```

#### Configuration
- `tailwind.config.js` - Tailwind configuration with custom WordPress admin colors
- `assets/css/input.css` - Input file with Tailwind directives and custom components

### 3. Updated Files

#### `/includes/class-admin-settings.php`
- Replaced CDN CSS with local build file (`admin-tailwind.css`)
- Updated all HTML classes from custom classes to Tailwind utility classes
- Maintained the same visual design and functionality

#### `/assets/js/admin-settings.js`
- Updated spinner classes to use Tailwind CSS utilities
- Updated success notice classes to use Tailwind CSS utilities
- Updated container selector from `.n8n-settings-container` to `.max-w-4xl`

### 3. Class Mapping

| Old Custom Class | New Tailwind Classes |
|------------------|----------------------|
| `.n8n-settings-container` | `max-w-4xl mx-0 my-10` |
| `.n8n-card` | `bg-white border border-gray-300 rounded-lg p-8 mb-5 shadow-sm` |
| `.n8n-card h2` | `mt-0 text-xl font-semibold text-gray-800` |
| `.n8n-card p` | `text-gray-600 mb-5` |
| `.n8n-form-group` | `mb-6` |
| `.n8n-form-group label` | `block mb-2 font-medium text-gray-800` |
| `.n8n-input-wrapper` | `flex gap-3 items-center` |
| `.n8n-api-key-input` | `flex-1 py-2.5 px-3.5 border border-gray-400 rounded font-mono text-sm bg-gray-50 text-gray-800 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600` |
| `.n8n-btn` | `py-2.5 px-5 rounded text-sm font-medium cursor-pointer transition-all duration-200 no-underline inline-flex items-center gap-2` |
| `.n8n-btn-primary` | `bg-blue-600 text-white hover:bg-blue-700` |
| `.n8n-btn-secondary` | `bg-gray-50 text-gray-800 border border-gray-400 hover:bg-gray-100` |
| `.n8n-notice` | `p-3 px-4 rounded mb-5 flex items-start gap-2.5` |
| `.n8n-notice-success` | `bg-green-100 border-l-4 border-green-500 text-gray-800` |
| `.n8n-notice-warning` | `bg-orange-50 border-l-4 border-orange-400 text-gray-800` |
| `.n8n-api-status` | `inline-flex items-center gap-1.5 py-1 px-3 rounded-full text-sm font-medium` |
| `.n8n-api-status.configured` | `bg-green-100 text-green-700` |
| `.n8n-api-status.not-configured` | `bg-orange-50 text-orange-700` |
| `.n8n-status-dot` | `w-2 h-2 rounded-full bg-current` |
| `.n8n-code-block` | `bg-gray-50 border border-gray-300 rounded p-3 px-4 font-mono text-sm overflow-x-auto my-2.5` |
| `.n8n-spinner` | `border-4 border-gray-200 border-t-blue-600 rounded-full w-4 h-4 animate-spin inline-block mr-2` |

### 4. Benefits

1. **Optimized Bundle Size**: Only used CSS classes are included (purged build)
2. **Consistent Design System**: Using Tailwind's utility classes ensures consistency
3. **Better Maintainability**: Easier to modify styles using utility classes
4. **Responsive Design**: Built-in responsive utilities available
5. **Performance**: Local CSS file with versioning for cache busting
6. **Development Workflow**: Watch mode for real-time CSS rebuilding

### 5. Development Workflow

#### Installation
```bash
npm install
```

#### Development Mode (Watch)
```bash
npm run build-css
```
This will watch for changes in `input.css` and rebuild automatically.

#### Production Build
```bash
npm run build-css-prod
```
This creates a minified version for production.

#### Development Build (One-time)
```bash
npm run build-css-dev
```

### 6. Preview Files

- `admin-preview.html` - Original design with custom CSS
- `admin-preview-tailwind.html` - New design with Tailwind CSS (CDN version)
- `admin-preview-local.html` - New design with local Tailwind CSS build

### 7. Backup

The original CSS file has been backed up as `assets/css/admin-settings.css.bak` in case you need to reference the original styles.

## Usage

The plugin now uses locally built Tailwind CSS. After making changes to the input CSS file or Tailwind classes:

1. Run `npm run build-css-prod` to build the production CSS
2. The built CSS file (`admin-tailwind.css`) is automatically loaded when accessing the n8n Integration settings page

## File Structure

```
assets/css/
├── input.css              # Source file with Tailwind directives
├── admin-tailwind.css     # Built CSS file (generated)
└── admin-settings.css.bak # Backup of original CSS
```

## Customization

The build system includes:

- **Custom WordPress Admin Colors**: Defined in `tailwind.config.js`
- **Component Classes**: Defined in `input.css` using `@layer components`
- **Purge Configuration**: Automatically removes unused CSS classes
- **Minification**: Production builds are automatically minified
