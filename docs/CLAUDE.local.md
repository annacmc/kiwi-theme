# CLAUDE.local.md - Development Implementation Guide

## 🚨 CRITICAL: WordPress.org Compliance Rules

### NEVER DO THESE THINGS (Instant Rejection)
- ❌ **Custom post types in themes** - Use plugins for custom functionality
- ❌ **External API calls** - No curl, wp_remote_get to external services
- ❌ **Database operations** - No custom tables, direct SQL queries
- ❌ **Plugin functionality** - No admin menus, settings pages, or CRON jobs  
- ❌ **Hardcoded text** - All strings must use translation functions
- ❌ **!important in CSS** - Use proper specificity instead
- ❌ **jQuery or external libraries** - Vanilla JavaScript only
- ❌ **eval(), exec(), system()** - Security violations
- ❌ **Compressed/encoded files** - All code must be readable

### ALWAYS DO THESE THINGS (Required for Approval)
- ✅ **Escape all outputs** - `esc_html()`, `esc_attr()`, `esc_url()`
- ✅ **Sanitize all inputs** - `sanitize_text_field()`, etc.
- ✅ **Use text domain consistently** - 'kiwi-theme' everywhere
- ✅ **Include GPL v2+ license** - Header and LICENSE file
- ✅ **Semantic HTML5** - Proper landmarks and heading hierarchy
- ✅ **WCAG 2.1 AA compliance** - 4.5:1 contrast, keyboard navigation
- ✅ **WordPress naming conventions** - `kiwi_theme_` prefix for all functions
- ✅ **Theme support features** - Required WordPress features enabled

## File Structure & Naming Rules

### Required Files (Exact Names)
```
kiwi-theme/
├── style.css                 # Theme headers + minimal CSS
├── theme.json               # Complete design system
├── functions.php            # Theme setup only
├── README.txt              # WordPress.org format
├── screenshot.png          # 1200x900px, showing theme design
├── templates/
│   ├── index.html          # Required: main template
│   ├── single.html         # Required: single post
│   ├── page.html           # Required: single page
│   ├── archive.html        # Required: archive pages
│   └── 404.html            # Required: error page
├── parts/
│   ├── header.html         # Site header
│   ├── footer.html         # Site footer
│   └── sidebar.html        # Navigation sidebar
└── patterns/               # Block patterns
    ├── micro-post.php
    ├── photo-post.php
    └── timeline-feed.php
```

### File Naming Conventions
- **Kebab-case**: `mobile-menu.html`, not `mobileMenu.html`
- **Descriptive**: `sidebar-navigation.html`, not `sidebar.html`
- **WordPress standard**: Follow template hierarchy exactly

## Code Standards & Patterns

### PHP Security Patterns
```php
// ✅ ALWAYS: Escape outputs
echo esc_html($user_content);
echo esc_attr($attribute_value);
echo esc_url($link_url);

// ✅ ALWAYS: Sanitize inputs
$clean_input = sanitize_text_field($_POST['user_input']);
$clean_email = sanitize_email($_POST['email']);

// ✅ ALWAYS: Use nonces for forms
wp_nonce_field('kiwi_theme_action', 'kiwi_theme_nonce');
if (!wp_verify_nonce($_POST['kiwi_theme_nonce'], 'kiwi_theme_action')) {
    wp_die('Security check failed');
}

// ❌ NEVER: Direct database queries
// global $wpdb; $wpdb->query("SELECT..."); // DON'T DO THIS

// ❌ NEVER: Unescaped output
// echo $user_content; // DANGEROUS
```

### CSS Architecture Rules
```css
/* ✅ GOOD: Proper specificity */
.wp-block-group.sidebar {
    position: fixed;
    background: var(--wp--preset--color--kiwi-background);
}

/* ✅ GOOD: Modern CSS with fallbacks */
.content-area {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: var(--wp--preset--spacing--lg);
}

@supports (container-type: inline-size) {
    .content-area { container-type: inline-size; }
}

/* ❌ BAD: Using !important */
.sidebar { display: block !important; } /* NEVER */

/* ❌ BAD: Hardcoded values */
.content { width: 800px; } /* Use theme.json custom properties */
```

### JavaScript Progressive Enhancement
```javascript
// ✅ GOOD: Progressive enhancement
function enhanceMobileMenu() {
    const toggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    // Graceful degradation - return if elements don't exist
    if (!toggle || !sidebar) return;
    
    toggle.addEventListener('click', function() {
        const isOpen = sidebar.classList.contains('open');
        sidebar.classList.toggle('open');
        
        // Accessibility enhancements
        toggle.setAttribute('aria-expanded', !isOpen);
        sidebar.setAttribute('aria-hidden', isOpen);
        
        // Focus management
        if (!isOpen) {
            const firstLink = sidebar.querySelector('a');
            if (firstLink) firstLink.focus();
        }
    });
}

// Initialize only when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enhanceMobileMenu);
} else {
    enhanceMobileMenu();
}

// ❌ BAD: Depending on jQuery
// $('#menu-toggle').click(function() { ... }); // NO JQUERY
```

## Accessibility Implementation Checklist

### WCAG 2.1 AA Requirements
- [ ] **Color contrast 4.5:1** - Text against background
- [ ] **Color contrast 3:1** - Large text (18pt+) against background  
- [ ] **Keyboard navigation** - All interactive elements accessible via Tab
- [ ] **Focus indicators** - Visible focus outlines on all interactive elements
- [ ] **Skip links** - Direct navigation to main content
- [ ] **Semantic HTML** - Proper headings (h1-h6), landmarks, lists
- [ ] **Alt text** - All images have descriptive alternative text
- [ ] **Form labels** - All inputs have associated labels
- [ ] **ARIA landmarks** - header, main, nav, aside, footer roles

### Implementation Patterns
```html
<!-- ✅ GOOD: Semantic structure with landmarks -->
<header role="banner">
    <nav role="navigation" aria-label="Main navigation">
        <ul role="menubar">
            <li role="menuitem"><a href="/about">About</a></li>
        </ul>
    </nav>
</header>

<main id="main" role="main" tabindex="-1">
    <h1>Page Title</h1>
    <article>
        <h2>Article Title</h2>
    </article>
</main>

<!-- ✅ GOOD: Skip link -->
<a class="skip-link screen-reader-text" href="#main">
    <?php esc_html_e('Skip to main content', 'kiwi-theme'); ?>
</a>

<!-- ✅ GOOD: Form accessibility -->
<label for="email"><?php esc_html_e('Email Address', 'kiwi-theme'); ?></label>
<input type="email" id="email" name="email" required 
       aria-describedby="email-error">
<div id="email-error" aria-live="polite"></div>
```

## Performance Requirements

### Core Web Vitals Targets (Non-Negotiable)
- **LCP (Largest Contentful Paint)**: < 2.5 seconds
- **FID (First Input Delay)**: < 100 milliseconds
- **CLS (Cumulative Layout Shift)**: < 0.1
- **Lighthouse Performance**: 90+ score

### Optimization Checklist
- [ ] **Critical CSS inlined** - Above-the-fold styles in `<head>`
- [ ] **Non-critical CSS deferred** - Load after page render
- [ ] **JavaScript deferred** - Non-blocking script loading
- [ ] **Images optimized** - Proper sizing, lazy loading, WebP format
- [ ] **Font optimization** - System fonts or optimized web fonts
- [ ] **Minimal HTTP requests** - Combine and minimize resources

### Implementation Example
```php
// ✅ Critical CSS inlining
function anna_kiwi_inline_critical_css() {
    ?>
    <style id="anna-critical-css">
    /* Only above-the-fold styles here */
    .container { display: flex; min-height: 100vh; }
    .sidebar { position: fixed; width: 240px; }
    .main-content { margin-left: 240px; }
    </style>
    <?php
}
add_action('wp_head', 'anna_kiwi_inline_critical_css', 1);

// ✅ Defer non-critical CSS
function anna_kiwi_defer_css($tag, $handle) {
    if ('kiwi-theme-style' === $handle) {
        return str_replace("rel='stylesheet'", 
            "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", 
            $tag);
    }
    return $tag;
}
add_filter('style_loader_tag', 'anna_kiwi_defer_css', 10, 2);
```

## Testing Commands & Workflows

### WordPress CLI Commands
```bash
# Theme management
wp theme activate kiwi-theme
wp theme list --status=active
wp theme status kiwi-theme

# Theme Check validation
wp plugin install theme-check --activate
wp theme check kiwi-theme

# Content testing
wp post generate --count=20 --post_type=post
wp media import theme-unit-test-data.xml
wp user create testuser test@example.com --role=author

# Site health
wp site health check
wp db check
wp core verify-checksums
```

### Testing Pipeline (Run Before Each Commit)
```bash
# 1. WordPress standards
./vendor/bin/phpcs --standard=WordPress .

# 2. Theme Check
wp theme check kiwi-theme

# 3. Accessibility testing
axe --tags wcag2a,wcag2aa http://localhost:8000
pa11y http://localhost:8000

# 4. Performance testing
lighthouse http://localhost:8000 --output html --output-path ./reports/

# 5. Cross-browser testing
npx playwright test

# 6. Visual regression
npx playwright test --update-snapshots
```

### Debugging Commands
```bash
# WordPress debug log
tail -f wp-content/debug.log

# PHP syntax check
find . -name "*.php" -exec php -l {} \;

# JavaScript validation
npx eslint assets/js/**/*.js

# CSS validation
npx stylelint assets/css/**/*.css

# HTML validation
curl http://localhost:8000 | tidy -errors
```

## Internationalization (i18n) Rules

### Translation Function Usage
```php
// ✅ Text output
esc_html_e('Subscribe to newsletter', 'kiwi-theme');

// ✅ Text return
$text = __('Read more about this topic', 'kiwi-theme');

// ✅ Context-specific translation
$button_text = _x('Submit', 'contact form button', 'kiwi-theme');

// ✅ Plural forms
$comment_text = sprintf(
    _n('One comment', '%s comments', $comment_count, 'kiwi-theme'),
    number_format_i18n($comment_count)
);

// ❌ Hardcoded strings
// echo 'Subscribe to newsletter'; // NEVER
```

### POT File Generation
```bash
# Generate translation template
wp i18n make-pot . languages/kiwi-theme.pot --domain=kiwi-theme

# Update existing translations
wp i18n update-po languages/kiwi-theme.pot languages/

# Generate JSON files for block editor
wp i18n make-json languages/ --no-purge
```

## Common Issues & Solutions

### Theme Check Failures
```php
// ❌ FAIL: Hardcoded links
echo '<a href="https://example.com">External Link</a>';

// ✅ PASS: Customizer option
$external_link = get_theme_mod('anna_kiwi_external_link', '');
if ($external_link) {
    echo '<a href="' . esc_url($external_link) . '">External Link</a>';
}

// ❌ FAIL: Including admin functionality
add_action('admin_menu', 'my_admin_menu'); // Theme can't do this

// ✅ PASS: Theme customization only
add_action('customize_register', 'anna_kiwi_customize_register');
```

### Performance Issues
```css
/* ❌ SLOW: Complex selectors */
div.container > div.content > article.post > div.meta > span.date {
    color: #333;
}

/* ✅ FAST: Simple, specific selectors */
.post-date {
    color: var(--wp--preset--color--anna-text);
}
```

### Accessibility Failures
```html
<!-- ❌ FAIL: Missing alt text -->
<img src="photo.jpg">

<!-- ✅ PASS: Descriptive alt text -->
<img src="photo.jpg" alt="Anna working in her garden, planting tomatoes">

<!-- ❌ FAIL: Color-only information -->
<span style="color: red;">Required field</span>

<!-- ✅ PASS: Multiple indicators -->
<span class="required" aria-label="Required field">
    Email Address <span aria-hidden="true">*</span>
</span>
```

## Pre-Submission Checklist

### WordPress.org Validation
- [ ] Theme Check plugin: 0 errors, 0 warnings
- [ ] PHPCS WordPress standards: No violations
- [ ] GPL v2+ license: Properly declared
- [ ] README.txt: WordPress.org format
- [ ] Screenshot: 1200x900px, accurate representation
- [ ] All strings internationalized: 'kiwi-theme' text domain
- [ ] No external dependencies: Self-contained theme
- [ ] Semantic versioning: Proper version numbering

### Quality Assurance
- [ ] Fresh WordPress install test: Theme activates cleanly
- [ ] Theme Unit Test data: All content displays correctly  
- [ ] Plugin compatibility: Works with common plugins
- [ ] Multisite compatibility: No fatal errors
- [ ] Child theme support: Proper template hierarchy
- [ ] Customizer integration: All options functional
- [ ] Widget areas: Properly registered and displayed
- [ ] Menu support: Primary and footer menus working

### Final Validation Commands
```bash
# Complete validation suite
npm run validate:wordpress-org

# Individual checks
npm run test:theme-check
npm run test:accessibility  
npm run test:performance
npm run test:security
npm run test:i18n

# Package for submission
npm run build:submission
```

Remember: Every line of code must serve users while respecting WordPress standards, accessibility requirements, and performance expectations. When in doubt, choose the more accessible, more secure, more performant option.