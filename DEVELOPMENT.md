# Development Guide - Tailwind CSS

This guide explains how to work with the Tailwind CSS setup in the n8n WordPress Integration plugin.

## Quick Start

1. **Install dependencies**:
   ```bash
   npm install
   ```

2. **Start development mode** (watches for changes):
   ```bash
   npm run build-css
   ```

3. **Make changes** to `assets/css/input.css` or any PHP files with Tailwind classes

4. **Build for production** when ready:
   ```bash
   npm run build-css-prod
   ```

## File Structure

```
├── package.json              # NPM configuration and scripts
├── tailwind.config.js        # Tailwind configuration
├── assets/css/
│   ├── input.css            # Source CSS with Tailwind directives
│   └── admin-tailwind.css   # Generated CSS file (don't edit directly)
└── includes/
    └── class-admin-settings.php  # PHP file that loads the CSS
```

## Making Style Changes

### Option 1: Using Tailwind Utility Classes (Recommended)
Edit the PHP file directly and add/modify Tailwind utility classes:

```php
// Example: Change button color
<button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
    Button
</button>
```

### Option 2: Custom Component Classes
Add custom styles to `assets/css/input.css`:

```css
@layer components {
  .my-custom-button {
    @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-medium;
  }
}
```

## Available NPM Scripts

| Script | Description | When to Use |
|--------|-------------|-------------|
| `npm run build-css` | Watch mode - rebuilds on changes | During development |
| `npm run build-css-dev` | Single build (unminified) | Testing changes |
| `npm run build-css-prod` | Production build (minified) | Before committing/deploying |

## Tailwind Configuration

The `tailwind.config.js` includes:

### Custom Colors
WordPress admin-specific colors are defined for consistency:

```javascript
colors: {
  'wp-admin': {
    'text': '#1d2327',
    'text-light': '#50575e',
    'border': '#dcdcde',
    'primary': '#2271b1',
    'success': '#00a32a',
    // ... more colors
  }
}
```

### Content Sources
Tailwind scans these files for class usage:

```javascript
content: [
  "./includes/**/*.php",
  "./views/**/*.html", 
  "./admin-preview.html",
  "./assets/js/**/*.js"
]
```

## Best Practices

### 1. Use Semantic Class Names
Instead of inline utilities everywhere, create component classes:

```css
/* Good - in input.css */
@layer components {
  .api-key-input {
    @apply flex-1 py-2.5 px-3.5 border border-gray-400 rounded font-mono text-sm;
  }
}
```

### 2. Maintain Consistent Spacing
Use Tailwind's spacing scale consistently:
- `gap-2` (0.5rem), `gap-3` (0.75rem), `gap-4` (1rem)
- `p-2` (0.5rem), `p-4` (1rem), `p-6` (1.5rem)

### 3. Follow WordPress Design Patterns
Use the custom WordPress admin colors:
- `text-wp-admin-text` for primary text
- `bg-wp-admin-primary` for primary buttons
- `border-wp-admin-border` for borders

### 4. Test Responsiveness
Use Tailwind's responsive prefixes:
```html
<div class="flex flex-col md:flex-row gap-4">
  <!-- Stacks on mobile, side-by-side on medium+ screens -->
</div>
```

## Debugging

### Check Generated CSS
Look at `assets/css/admin-tailwind.css` to see what was generated.

### Verify Class Usage
If a class isn't appearing, check:
1. Is the file included in `tailwind.config.js` content array?
2. Is the class name spelled correctly?
3. Did you run the build command?

### Watch Mode Not Working
If watch mode stops working:
1. Stop the process (Ctrl+C)
2. Restart: `npm run build-css`

## Production Deployment

Before deploying or committing:

1. **Build production CSS**:
   ```bash
   npm run build-css-prod
   ```

2. **Commit the built file**:
   The `admin-tailwind.css` file should be committed to the repository.

3. **Verify file size**:
   Production builds should be much smaller due to purging unused classes.

## Troubleshooting

### Classes Not Applying
- Ensure the class exists in Tailwind
- Check browser dev tools for CSS loading
- Verify the CSS file was rebuilt after changes

### Large CSS File Size
- Run production build: `npm run build-css-prod`
- Check that your content paths in `tailwind.config.js` are correct

### WordPress Admin Styling Conflicts
- Use higher specificity or `!important` if needed
- Test in actual WordPress admin environment

## Adding New Components

When adding new UI components:

1. **Plan the structure** with utility classes
2. **Create component class** in `input.css` if needed
3. **Test in browser** using preview files
4. **Build production CSS** before committing

## Integration with WordPress

The CSS is loaded in WordPress via:

```php
wp_enqueue_style(
    'n8n-admin-tailwind',
    N8N_WP_PLUGIN_URL . 'assets/css/admin-tailwind.css',
    array(),
    N8N_WP_VERSION
);
```

This ensures:
- Proper cache busting with version number
- Correct URL resolution
- WordPress admin compatibility
