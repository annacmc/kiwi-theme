# Kiwi Theme

**Contributors:** Anna McPhee  
**Tags:** blog, one-column, two-columns, custom-colors, custom-menu, editor-style, featured-images, full-site-editing, block-patterns, rtl-language-support, sticky-post, translation-ready, accessibility-ready  
**Requires at least:** 6.0  
**Tested up to:** 6.4  
**Requires PHP:** 7.4  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html

A modern WordPress Full Site Editing (FSE) theme designed for flexible content publishing with multiple content types.

## Description

Kiwi Theme is a modern, flexible WordPress FSE theme that features a fixed sidebar navigation, RSS-first content strategy, and adaptive publishing workflows. The theme emphasizes clean design and technical excellence while supporting diverse content formats.

### Key Features

- **Full Site Editing (FSE)** - Complete block-based theme customization
- **Fixed Sidebar Layout** - Elegant navigation with responsive mobile menu
- **Multiple Content Types** - Support for standard posts, micro posts, and photo posts
- **Modern CSS** - Uses CSS Grid, Container Queries, and CSS custom properties
- **Performance Optimized** - Meets Core Web Vitals standards
- **Accessibility Ready** - WCAG 2.1 AA compliant
- **Translation Ready** - Fully internationalized with RTL support
- **Dark/Light Mode** - Automatic color scheme switching
- **Block Patterns** - Pre-designed patterns for different content types
- **RSS-First Strategy** - Multiple feed types for content syndication

### Design System

- **Color Palette**: Primary purple (#7b68ee), secondary teal (#20c997), accent orange (#fd7e14)
- **Typography**: System font stack with fluid typography scaling
- **Spacing**: Consistent 8-step spacing scale with logical properties
- **Layout**: CSS Grid-based responsive design

### Content Types

1. **Standard Posts** - Traditional blog posts with reading time calculation
2. **Micro Posts** - Short-form content similar to social media posts
3. **Photo Posts** - Image-focused content with caption support
4. **Timeline Feed** - Mixed content type display in chronological order

## Installation

### Manual Installation

1. Download the theme files
2. Upload the `kiwi-theme` folder to `/wp-content/themes/`
3. Activate the theme through the WordPress admin dashboard
4. Go to Appearance → Editor to start customizing

### WordPress.org Installation

1. Go to Appearance → Themes in your WordPress admin
2. Click "Add New"
3. Search for "Kiwi Theme"
4. Install and activate

## Quick Start

### Basic Setup

1. **Set up menus**: Go to Appearance → Menus and assign menus to "Primary Navigation"
2. **Configure sidebar**: Customize the sidebar content in Appearance → Editor → Template Parts
3. **Add content**: Start creating posts using the different block patterns
4. **Customize colors**: Use the Site Editor to adjust the color palette

### Using Block Patterns

The theme includes several pre-designed block patterns:

- **Standard Post Pattern** - For traditional blog posts
- **Micro Post Pattern** - For short-form content
- **Photo Post Pattern** - For image-focused posts
- **Timeline Feed Pattern** - For mixed content display

Access these patterns in the Block Editor by clicking the "Patterns" tab.

### Customization

#### Colors

The theme uses CSS custom properties for easy color customization:

```css
--kiwi-primary: #7b68ee;
--kiwi-secondary: #20c997;
--kiwi-accent: #fd7e14;
--kiwi-text: light-dark(#2c3e50, #e5e5e5);
--kiwi-background: light-dark(#fefefe, #1a1a1a);
```

#### Typography

Fluid typography scales automatically across devices using `clamp()`:

```css
font-size: clamp(1rem, 2.5vw, 1.125rem);
```

#### Layout

The theme uses CSS Grid for the main layout:

- **Desktop**: Fixed 240px sidebar with flexible main content
- **Tablet**: 200px sidebar with adjusted content padding
- **Mobile**: Hidden sidebar with toggle button overlay

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers with CSS Grid support

## Performance

Kiwi Theme is optimized for performance:

- **Core Web Vitals**: Meets Google's performance standards
- **Lighthouse Score**: 90+ performance score
- **Critical CSS**: Inlined for faster rendering
- **Lazy Loading**: Built-in image lazy loading
- **Modern CSS**: Efficient CSS Grid and custom properties

## Accessibility

The theme is designed with accessibility in mind:

- **WCAG 2.1 AA Compliant**: Meets accessibility standards
- **Keyboard Navigation**: Full keyboard accessibility
- **Screen Reader Support**: Proper ARIA landmarks and labels
- **Color Contrast**: 4.5:1 minimum contrast ratios
- **Focus Management**: Clear focus indicators
- **Skip Links**: Direct navigation to main content

## Internationalization

- **Translation Ready**: All strings are translatable
- **RTL Support**: Right-to-left language compatibility
- **Text Domain**: `kiwi-theme`
- **POT File**: Included for translators

## Developer Information

### Hooks and Filters

The theme provides several hooks for customization:

```php
// Customize reading time calculation
add_filter('kiwi_theme_reading_time', 'your_custom_function');

// Modify performance optimizations
add_filter('kiwi_theme_performance_features', 'your_custom_function');

// Customize block patterns
add_action('kiwi_theme_register_patterns', 'your_custom_function');
```

### CSS Custom Properties

Override theme styles using CSS custom properties:

```css
:root {
    --kiwi-primary: your-color;
    --kiwi-font-family: your-font;
    --kiwi-spacing-lg: your-spacing;
}
```

### File Structure

```
kiwi-theme/
├── style.css              # Theme headers and critical CSS
├── theme.json             # Design system configuration
├── functions.php          # Theme functionality
├── screenshot.png         # Theme preview image
├── templates/             # Block templates
│   ├── index.html
│   ├── single.html
│   ├── archive.html
│   └── page.html
├── parts/                 # Template parts
│   ├── header.html
│   ├── footer.html
│   └── sidebar.html
├── patterns/              # Block patterns
└── assets/               # Theme assets
    ├── css/
    ├── js/
    └── images/
```

## Contributing

Contributions are welcome! Please follow these guidelines:

1. **Code Standards**: Follow WordPress Coding Standards
2. **Testing**: Ensure all changes pass theme tests
3. **Accessibility**: Maintain WCAG 2.1 AA compliance
4. **Performance**: Keep performance optimizations intact

### Development Setup

1. Clone the repository
2. Install development dependencies
3. Run the test suite: `npm run test`
4. Build for production: `npm run build`

## Support

- **Documentation**: [Theme documentation](https://github.com/your-repo/kiwi-theme/wiki)
- **Issues**: [Report bugs](https://github.com/your-repo/kiwi-theme/issues)
- **Support Forum**: [WordPress.org support](https://wordpress.org/support/theme/kiwi-theme)

## Changelog

### 1.0.0
- Initial release
- Full Site Editing support
- Responsive sidebar layout
- Multiple content type patterns
- Performance optimizations
- Accessibility compliance
- Dark/light mode support

## Credits

- **Theme Design**: Modern minimalist approach
- **Typography**: System font stack for optimal performance
- **Icons**: Minimal emoji-based indicators
- **Color Palette**: Carefully selected for accessibility and aesthetics

## License

This theme is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

---

**Kiwi Theme** - A modern WordPress FSE theme for flexible content publishing, by Anna McPhee