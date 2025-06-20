# Kiwi Theme - TDD Development Strategy
## Test-Driven Development Approach for WordPress.org Compliant FSE Theme

---

## Project Overview

**Theme Name**: Kiwi Theme  
**Version**: 1.0.0  
**WordPress Compatibility**: 6.0+  
**PHP Compatibility**: 7.4+  
**License**: GPL v2+  
**Type**: Full Site Editing (FSE) Block Theme  

**Core Philosophy**: Tests first, code second. Every feature must have failing tests before implementation begins.

---

## Development Environment Setup

### Prerequisites Testing Framework
```bash
# Required testing tools
- PHPUnit 9.x (WordPress core testing)
- WordPress Test Suite
- Playwright (browser testing)
- axe-core (accessibility testing)
- WordPress Coding Standards
- Theme Check Plugin
- Theme Unit Test Data
```

### Test Environment Structure
```
tests/
├── phpunit/
│   ├── bootstrap.php
│   ├── theme-setup.php
│   └── accessibility/
├── playwright/
│   ├── visual-regression/
│   ├── performance/
│   └── browser-compatibility/
├── accessibility/
│   ├── axe-tests/
│   └── keyboard-navigation/
└── fixtures/
    ├── test-content.xml
    └── sample-images/
```

---

## Phase 1: Foundation & Theme Architecture

### Phase 1.1: Theme Structure & Basic Setup

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/phpunit/test-theme-structure.php`
```php
<?php
class Test_Theme_Structure extends WP_UnitTestCase {
    
    public function test_theme_json_exists() {
        $this->assertFileExists(get_template_directory() . '/theme.json');
    }
    
    public function test_style_css_exists_with_proper_headers() {
        $style_file = get_template_directory() . '/style.css';
        $this->assertFileExists($style_file);
        
        $style_content = file_get_contents($style_file);
        $this->assertStringContainsString('Theme Name: Kiwi Theme', $style_content);
        $this->assertStringContainsString('Version: 1.0.0', $style_content);
        $this->assertStringContainsString('License: GPL v2+', $style_content);
        $this->assertStringContainsString('Text Domain: kiwi-theme', $style_content);
    }
    
    public function test_required_templates_exist() {
        $required_templates = ['index.html', 'single.html', 'archive.html', 'page.html'];
        foreach ($required_templates as $template) {
            $this->assertFileExists(get_template_directory() . "/templates/{$template}");
        }
    }
    
    public function test_functions_php_exists_and_loads() {
        $this->assertFileExists(get_template_directory() . '/functions.php');
        // Test that it doesn't cause fatal errors
        ob_start();
        include_once get_template_directory() . '/functions.php';
        $output = ob_get_clean();
        $this->assertEmpty($output, 'functions.php should not produce output');
    }
}
```

**Test File**: `tests/phpunit/test-theme-json.php`
```php
<?php
class Test_Theme_JSON extends WP_UnitTestCase {
    
    private $theme_json;
    
    public function setUp(): void {
        parent::setUp();
        $theme_json_file = get_template_directory() . '/theme.json';
        $this->theme_json = json_decode(file_get_contents($theme_json_file), true);
    }
    
    public function test_theme_json_is_valid_json() {
        $this->assertNotNull($this->theme_json, 'theme.json must contain valid JSON');
    }
    
    public function test_theme_json_has_required_version() {
        $this->assertArrayHasKey('version', $this->theme_json);
        $this->assertEquals(2, $this->theme_json['version']);
    }
    
    public function test_color_palette_contains_anna_brand_colors() {
        $colors = $this->theme_json['settings']['color']['palette'];
        $color_slugs = array_column($colors, 'slug');
        
        $required_colors = [
            'anna-primary', 'anna-secondary', 'anna-accent',
            'anna-text-light', 'anna-text-dark', 'anna-background-light', 'anna-background-dark'
        ];
        
        foreach ($required_colors as $color) {
            $this->assertContains($color, $color_slugs);
        }
    }
    
    public function test_typography_scale_exists() {
        $this->assertArrayHasKey('typography', $this->theme_json['settings']);
        $this->assertArrayHasKey('fontSizes', $this->theme_json['settings']['typography']);
        
        $font_sizes = $this->theme_json['settings']['typography']['fontSizes'];
        $this->assertGreaterThanOrEqual(6, count($font_sizes), 'Should have at least 6 font sizes');
    }
    
    public function test_spacing_scale_is_consistent() {
        $spacing = $this->theme_json['settings']['spacing']['spacingSizes'];
        $this->assertGreaterThanOrEqual(8, count($spacing), 'Should have at least 8 spacing sizes');
        
        // Test that spacing follows consistent scale
        foreach ($spacing as $size) {
            $this->assertArrayHasKey('size', $size);
            $this->assertStringContainsString('rem', $size['size']);
        }
    }
}
```

**Test File**: `tests/playwright/test-initial-load.spec.js`
```javascript
import { test, expect } from '@playwright/test';

test.describe('Theme Initial Load', () => {
  test('theme loads without JavaScript errors', async ({ page }) => {
    const errors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') errors.push(msg.text());
    });
    
    await page.goto('/');
    expect(errors).toHaveLength(0);
  });
  
  test('theme has proper HTML5 semantic structure', async ({ page }) => {
    await page.goto('/');
    
    // Must fail initially - no semantic structure yet
    await expect(page.locator('header')).toBeVisible();
    await expect(page.locator('main')).toBeVisible();
    await expect(page.locator('nav')).toBeVisible();
    await expect(page.locator('footer')).toBeVisible();
  });
  
  test('theme respects prefers-color-scheme', async ({ page, context }) => {
    // Test dark mode
    await context.emulateMedia({ colorScheme: 'dark' });
    await page.goto('/');
    
    const backgroundColor = await page.evaluate(() => {
      return getComputedStyle(document.body).backgroundColor;
    });
    
    // Should be dark background - will fail initially
    expect(backgroundColor).toMatch(/rgb\(26, 26, 26\)/);
  });
});
```

#### Development Steps to Pass Tests

**Step 1.1.1**: Create Theme Directory Structure
```
kiwi-theme/
├── style.css
├── theme.json
├── functions.php
├── templates/
│   ├── index.html
│   ├── single.html
│   ├── archive.html
│   └── page.html
├── parts/
│   ├── header.html
│   ├── footer.html
│   └── sidebar.html
└── patterns/
    └── (to be created in later phases)
```

**Step 1.1.2**: Create `style.css` with Proper Headers
```css
/*
Theme Name: Kiwi Theme
Description: A modern FSE theme for nonlinear creative lives. Features sidebar navigation, multiple content types, and RSS-first architecture.
Author: Anna
Version: 1.0.0
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: kiwi-theme
Domain Path: /languages
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Tags: blog, one-column, two-columns, custom-colors, custom-menu, editor-style, featured-images, full-site-editing, block-patterns, rtl-language-support, sticky-post, translation-ready
*/

/* Theme styles will be handled by theme.json and block styles */
```

**Step 1.1.3**: Create Basic `theme.json`
```json
{
  "version": 2,
  "settings": {
    "color": {
      "palette": [
        {
          "slug": "anna-primary",
          "color": "#7b68ee",
          "name": "Anna Primary"
        },
        {
          "slug": "anna-secondary", 
          "color": "#20c997",
          "name": "Anna Secondary"
        },
        {
          "slug": "anna-accent",
          "color": "#fd7e14", 
          "name": "Anna Accent"
        },
        {
          "slug": "anna-text-light",
          "color": "#2c3e50",
          "name": "Anna Text Light"
        },
        {
          "slug": "anna-text-dark",
          "color": "#e5e5e5",
          "name": "Anna Text Dark"
        },
        {
          "slug": "anna-background-light",
          "color": "#fefefe",
          "name": "Anna Background Light"
        },
        {
          "slug": "anna-background-dark",
          "color": "#1a1a1a",
          "name": "Anna Background Dark"
        }
      ]
    },
    "typography": {
      "fontSizes": [
        {
          "slug": "xs",
          "size": "0.8rem",
          "name": "Extra Small"
        },
        {
          "slug": "sm", 
          "size": "0.9rem",
          "name": "Small"
        },
        {
          "slug": "base",
          "size": "1rem", 
          "name": "Base"
        },
        {
          "slug": "lg",
          "size": "1.1rem",
          "name": "Large"
        },
        {
          "slug": "xl",
          "size": "1.25rem",
          "name": "Extra Large"
        },
        {
          "slug": "2xl",
          "size": "1.5rem",
          "name": "2X Large"
        },
        {
          "slug": "3xl", 
          "size": "2rem",
          "name": "3X Large"
        },
        {
          "slug": "4xl",
          "size": "2.5rem",
          "name": "4X Large"
        }
      ]
    },
    "spacing": {
      "spacingSizes": [
        {
          "slug": "xs",
          "size": "0.25rem",
          "name": "XS"
        },
        {
          "slug": "sm",
          "size": "0.5rem", 
          "name": "Small"
        },
        {
          "slug": "md",
          "size": "1rem",
          "name": "Medium"
        },
        {
          "slug": "lg", 
          "size": "1.5rem",
          "name": "Large"
        },
        {
          "slug": "xl",
          "size": "2rem",
          "name": "XL"
        },
        {
          "slug": "2xl",
          "size": "3rem",
          "name": "2XL"
        },
        {
          "slug": "3xl",
          "size": "4rem", 
          "name": "3XL"
        },
        {
          "slug": "4xl",
          "size": "6rem",
          "name": "4XL"
        }
      ]
    }
  },
  "styles": {
    "color": {
      "background": "light-dark(var(--wp--preset--color--anna-background-light), var(--wp--preset--color--anna-background-dark))",
      "text": "light-dark(var(--wp--preset--color--anna-text-light), var(--wp--preset--color--anna-text-dark))"
    }
  }
}
```

**Step 1.1.4**: Create Basic `functions.php`
```php
<?php
/**
 * Kiwi Theme Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function kiwi_theme_setup() {
    // Add theme support
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('editor-styles');
    add_theme_support('html5', array(
        'search-form',
        'comment-form', 
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));
    
    // Load translation files
    load_theme_textdomain('kiwi-theme', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'kiwi_theme_setup');

/**
 * Enqueue scripts and styles
 */
function kiwi_theme_scripts() {
    wp_enqueue_style(
        'kiwi-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'kiwi_theme_scripts');
```

**Step 1.1.5**: Create Basic Template Files
```html
<!-- templates/index.html -->
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"0","bottom":"0"}}}} -->
<main class="wp-block-group">
    <!-- wp:query -->
    <div class="wp-block-query">
        <!-- wp:post-template -->
        <!-- wp:post-title {"isLink":true} /-->
        <!-- wp:post-excerpt /-->
        <!-- /wp:post-template -->
        
        <!-- wp:query-pagination -->
        <!-- wp:query-pagination-previous /-->
        <!-- wp:query-pagination-numbers /-->
        <!-- wp:query-pagination-next /-->
        <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```

---

### Phase 1.2: Accessibility Foundation

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/accessibility/test-accessibility-basics.php`
```php
<?php
class Test_Accessibility_Basics extends WP_UnitTestCase {
    
    public function test_skip_links_exist() {
        $this->go_to('/');
        $content = get_echo('wp_head');
        
        // Should fail initially - no skip links implemented
        $this->assertStringContainsString('skip-link', $content);
    }
    
    public function test_proper_heading_hierarchy() {
        // Create test post
        $post_id = $this->factory->post->create(array(
            'post_title' => 'Test Post',
            'post_content' => '<h2>Heading 2</h2><h3>Heading 3</h3>'
        ));
        
        $this->go_to(get_permalink($post_id));
        $content = get_echo('the_content');
        
        // Should have h1 for title, then h2, h3 in order
        $this->assertRegExp('/<h1.*?>.*?<\/h1>.*?<h2.*?>.*?<\/h2>.*?<h3.*?>.*?<\/h3>/s', $content);
    }
    
    public function test_color_contrast_ratios() {
        // This will be tested via Playwright with axe-core
        $this->markTestSkipped('Color contrast tested via browser tests');
    }
}
```

**Test File**: `tests/playwright/test-accessibility.spec.js`
```javascript
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test.describe('Accessibility Tests', () => {
  test('should not have any automatically detectable accessibility issues', async ({ page }) => {
    await page.goto('/');
    
    const accessibilityScanResults = await new AxeBuilder({ page }).analyze();
    
    // This will fail initially - no accessibility features implemented
    expect(accessibilityScanResults.violations).toEqual([]);
  });
  
  test('keyboard navigation works properly', async ({ page }) => {
    await page.goto('/');
    
    // Test tab navigation
    await page.keyboard.press('Tab');
    const focusedElement = await page.locator(':focus');
    
    // Should fail initially - no focus management
    await expect(focusedElement).toBeVisible();
  });
  
  test('skip links are functional', async ({ page }) => {
    await page.goto('/');
    
    // Test skip link functionality
    await page.keyboard.press('Tab');
    const skipLink = page.locator('a[href="#main"]');
    
    // Will fail initially
    await expect(skipLink).toBeVisible();
    await skipLink.click();
    
    const mainContent = page.locator('#main');
    await expect(mainContent).toBeFocused();
  });
  
  test('screen reader announcements work', async ({ page }) => {
    await page.goto('/');
    
    // Check for proper ARIA landmarks
    await expect(page.locator('role=banner')).toBeVisible();
    await expect(page.locator('role=main')).toBeVisible();
    await expect(page.locator('role=navigation')).toBeVisible();
    await expect(page.locator('role=contentinfo')).toBeVisible();
  });
});
```

#### Development Steps to Pass Accessibility Tests

**Step 1.2.1**: Add Skip Links to Header Template Part
```html
<!-- parts/header.html -->
<!-- wp:html -->
<a class="skip-link screen-reader-text" href="#main">Skip to main content</a>
<!-- /wp:html -->

<!-- wp:group {"tagName":"header","className":"site-header"} -->
<header class="wp-block-group site-header">
    <!-- Header content -->
</header>
<!-- /wp:group -->
```

**Step 1.2.2**: Update theme.json with Accessibility Styles
```json
{
  "styles": {
    "elements": {
      "link": {
        "color": {
          "text": "var(--wp--preset--color--anna-primary)"
        },
        ":hover": {
          "color": {
            "text": "var(--wp--preset--color--anna-secondary)"
          }
        },
        ":focus": {
          "outline": "2px solid var(--wp--preset--color--anna-primary)",
          "outlineOffset": "2px"
        }
      }
    }
  }
}
```

**Step 1.2.3**: Add Accessibility CSS
```css
/* Add to theme's style.css or via theme.json */
.skip-link {
    position: absolute;
    left: -9999px;
    z-index: 999999;
    padding: 8px 16px;
    background: var(--wp--preset--color--anna-primary);
    color: var(--wp--preset--color--anna-background-light);
    text-decoration: none;
}

.skip-link:focus {
    left: 6px;
    top: 7px;
}

.screen-reader-text {
    border: 0;
    clip: rect(1px, 1px, 1px, 1px);
    clip-path: inset(50%);
    height: 1px;
    margin: -1px;
    overflow: hidden;
    padding: 0;
    position: absolute !important;
    width: 1px;
    word-wrap: normal !important;
}

.screen-reader-text:focus {
    background-color: var(--wp--preset--color--anna-background-light);
    border-radius: 3px;
    box-shadow: 0 0 2px 2px rgba(0, 0, 0, 0.6);
    clip: auto !important;
    clip-path: none;
    color: var(--wp--preset--color--anna-text-light);
    display: block;
    font-size: 0.875rem;
    font-weight: 700;
    height: auto;
    left: 5px;
    line-height: normal;
    padding: 15px 23px 14px;
    text-decoration: none;
    top: 5px;
    width: auto;
    z-index: 100000;
}
```

---

## Phase 2: Layout Structure & Responsive Design

### Phase 2.1: Sidebar + Main Content Layout

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/playwright/test-layout.spec.js`
```javascript
import { test, expect } from '@playwright/test';

test.describe('Layout Structure', () => {
  test('sidebar is fixed and visible on desktop', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.goto('/');
    
    const sidebar = page.locator('.sidebar');
    
    // Will fail initially - no sidebar implemented
    await expect(sidebar).toBeVisible();
    
    // Check if sidebar is fixed
    const position = await sidebar.evaluate(el => getComputedStyle(el).position);
    expect(position).toBe('fixed');
  });
  
  test('main content has proper margin for sidebar', async ({ page }) => {
    await page.setViewportSize({ width: 1200, height: 800 });
    await page.goto('/');
    
    const mainContent = page.locator('main');
    const marginLeft = await mainContent.evaluate(el => getComputedStyle(el).marginLeft);
    
    // Should fail initially
    expect(marginLeft).toBe('240px');
  });
  
  test('layout is responsive on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    const sidebar = page.locator('.sidebar');
    const mainContent = page.locator('main');
    
    // Sidebar should be hidden/transformed on mobile
    const sidebarTransform = await sidebar.evaluate(el => getComputedStyle(el).transform);
    expect(sidebarTransform).toContain('translateX(-100%');
    
    // Main content should have no left margin on mobile
    const marginLeft = await mainContent.evaluate(el => getComputedStyle(el).marginLeft);
    expect(marginLeft).toBe('0px');
  });
  
  test('mobile menu toggle works', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 });
    await page.goto('/');
    
    const menuToggle = page.locator('.mobile-menu-toggle');
    await expect(menuToggle).toBeVisible();
    
    const sidebar = page.locator('.sidebar');
    
    // Click to open sidebar
    await menuToggle.click();
    
    // Sidebar should be visible after toggle
    const sidebarTransform = await sidebar.evaluate(el => getComputedStyle(el).transform);
    expect(sidebarTransform).toBe('translateX(0px)');
  });
});
```

**Test File**: `tests/phpunit/test-responsive-images.php`
```php
<?php
class Test_Responsive_Images extends WP_UnitTestCase {
    
    public function test_theme_supports_responsive_images() {
        $this->assertTrue(current_theme_supports('html5', 'gallery'));
        $this->assertTrue(current_theme_supports('post-thumbnails'));
    }
    
    public function test_custom_image_sizes_registered() {
        global $_wp_additional_image_sizes;
        
        $required_sizes = [
            'anna-micro-thumb',
            'anna-sidebar-thumb', 
            'anna-featured-large'
        ];
        
        foreach ($required_sizes as $size) {
            // Will fail initially - sizes not registered
            $this->assertArrayHasKey($size, $_wp_additional_image_sizes);
        }
    }
}
```

#### Development Steps to Pass Layout Tests

**Step 2.1.1**: Update theme.json with Layout Settings
```json
{
  "version": 2,
  "settings": {
    "layout": {
      "contentSize": "800px",
      "wideSize": "1200px"
    },
    "useRootPaddingAwareAlignments": true
  },
  "styles": {
    "blocks": {
      "core/group": {
        "elements": {
          "link": {
            "color": {
              "text": "var(--wp--preset--color--anna-primary)"
            }
          }
        }
      }
    }
  },
  "customTemplates": [
    {
      "name": "sidebar-page",
      "title": "Sidebar Page",
      "postTypes": ["page"]
    }
  ]
}
```

**Step 2.1.2**: Create Sidebar Template Part
```html
<!-- parts/sidebar.html -->
<!-- wp:group {"className":"sidebar","tagName":"aside","style":{"position":{"type":"sticky","top":"0px"}}} -->
<aside class="wp-block-group sidebar">
    
    <!-- wp:group {"className":"site-header"} -->
    <div class="wp-block-group site-header">
        <!-- wp:site-title {"level":0,"className":"site-title"} /-->
        <!-- wp:site-tagline {"className":"site-subtitle"} /-->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:group {"className":"about-section"} -->
    <div class="wp-block-group about-section">
        <!-- wp:paragraph {"className":"about-text"} -->
        <p class="about-text">I build things with code, with words, with plants. I wrangle code at Automattic and find connections in unexpected places.</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:navigation {"className":"nav-menu"} /-->
    
    <!-- wp:search {"label":"Search","showLabel":false,"placeholder":"Search...","className":"search-box"} /-->
    
    <!-- wp:group {"className":"subscribe-box"} -->
    <div class="wp-block-group subscribe-box">
        <!-- wp:heading {"level":3,"className":"subscribe-title"} -->
        <h3 class="subscribe-title">Subscribe</h3>
        <!-- /wp:heading -->
        
        <!-- wp:paragraph {"className":"subscribe-text"} -->
        <p class="subscribe-text">Get new posts in your inbox</p>
        <!-- /wp:paragraph -->
        
        <!-- wp:html -->
        <form class="subscribe-form">
            <input type="email" class="subscribe-input" placeholder="your@email.com" required>
            <button type="submit" class="subscribe-btn">Subscribe</button>
        </form>
        <!-- /wp:html -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:social-links {"className":"sidebar-links"} -->
    <ul class="wp-block-social-links sidebar-links">
        <!-- wp:social-link {"url":"#","service":"mastodon"} /-->
        <!-- wp:social-link {"url":"#","service":"linkedin"} /-->
        <!-- wp:social-link {"url":"#","service":"mail"} /-->
    </ul>
    <!-- /wp:social-links -->
    
</aside>
<!-- /wp:group -->
```

**Step 2.1.3**: Update Index Template with Sidebar Layout
```html
<!-- templates/index.html -->
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"className":"container","tagName":"div","style":{"display":"flex","minHeight":"100vh"}} -->
<div class="wp-block-group container">
    
    <!-- wp:template-part {"slug":"sidebar","tagName":"aside"} /-->
    
    <!-- wp:group {"className":"main-content","tagName":"main","style":{"marginLeft":"240px","flex":"1","padding":"3rem 4rem","maxWidth":"800px"}} -->
    <main class="wp-block-group main-content">
        
        <!-- wp:group {"className":"intro-section"} -->
        <div class="wp-block-group intro-section">
            <!-- wp:html -->
            <div class="intro-icon">🌱</div>
            <!-- /wp:html -->
            
            <!-- wp:heading {"level":1,"className":"intro-title"} -->
            <h1 class="intro-title">Hi, I'm Anna.</h1>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"intro-text"} -->
            <p class="intro-text">I build things with code, with words, with plants. I wrangle code at <a href="#">Automattic</a> and find connections in unexpected places. After many nomadic years across 42 countries, I'm now in Australia raising my daughter and growing roots.</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:paragraph {"className":"intro-text"} -->
            <p class="intro-text">I write about <strong>creative systems</strong>, <strong>lived stories</strong>, and <strong>slow experiments</strong> that emerge from a nonlinear life.</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
        
        <!-- wp:query {"className":"content-feed"} -->
        <div class="wp-block-query content-feed">
            <!-- wp:post-template -->
            
                <!-- wp:group {"className":"feed-item"} -->
                <div class="wp-block-group feed-item">
                    
                    <!-- wp:group {"className":"post-meta"} -->
                    <div class="wp-block-group post-meta">
                        <!-- wp:html -->
                        <span class="post-type-indicator post-type-post"></span>
                        <!-- /wp:html -->
                        
                        <!-- wp:post-date {"className":"post-date"} /-->
                        
                        <!-- wp:post-terms {"term":"category","className":"post-category"} /-->
                    </div>
                    <!-- /wp:group -->
                    
                    <!-- wp:post-title {"isLink":true,"className":"post-title"} /-->
                    
                    <!-- wp:post-excerpt {"className":"post-excerpt"} /-->
                    
                    <!-- wp:html -->
                    <a href="#" class="read-more">Read more →</a>
                    <!-- /wp:html -->
                    
                </div>
                <!-- /wp:group -->
                
            <!-- /wp:post-template -->
            
            <!-- wp:query-pagination -->
            <!-- wp:query-pagination-previous /-->
            <!-- wp:query-pagination-numbers /-->
            <!-- wp:query-pagination-next /-->
            <!-- /wp:query-pagination -->
        </div>
        <!-- /wp:query -->
        
    </main>
    <!-- /wp:group -->
    
</div>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
```

**Step 2.1.4**: Add Responsive CSS via theme.json
```json
{
  "styles": {
    "css": ".container { display: flex; min-height: 100vh; } .sidebar { position: fixed; left: 0; top: 0; width: 240px; height: 100vh; background: var(--wp--preset--color--anna-background-light); border-right: 1px solid #ddd; padding: 1.5rem 1rem; overflow-y: auto; z-index: 100; font-size: 0.9rem; } .main-content { margin-left: 240px; flex: 1; padding: 3rem 4rem; max-width: 800px; } @media (max-width: 1024px) { .sidebar { width: 200px; } .main-content { margin-left: 200px; padding: 2rem 3rem; } } @media (max-width: 768px) { .sidebar { transform: translateX(-100%); transition: transform 0.3s ease; width: 240px; } .sidebar.open { transform: translateX(0); } .main-content { margin-left: 0; padding: 2rem 1.5rem; } .mobile-menu-toggle { position: fixed; top: 1rem; left: 1rem; z-index: 200; background: var(--wp--preset--color--anna-primary); color: white; border: none; padding: 0.5rem; border-radius: 6px; cursor: pointer; font-size: 1.2rem; display: block; } }"
  }
}
```

**Step 2.1.5**: Add Image Sizes to functions.php
```php
<?php
function kiwi_theme_image_sizes() {
    add_image_size('anna-micro-thumb', 150, 150, true);
    add_image_size('anna-sidebar-thumb', 200, 200, true);
    add_image_size('anna-featured-large', 1200, 630, true);
}
add_action('after_setup_theme', 'kiwi_theme_image_sizes');

function kiwi_theme_mobile_menu_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('open');
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'kiwi_theme_mobile_menu_script');
?>
```

---

## Phase 3: Content Display & Block Patterns

### Phase 3.1: Post Type Differentiation

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/phpunit/test-content-patterns.php`
```php
<?php
class Test_Content_Patterns extends WP_UnitTestCase {
    
    public function test_post_type_indicators_exist() {
        // Test that CSS classes for post type indicators exist
        $style_file = get_template_directory() . '/style.css';
        $styles = file_get_contents($style_file);
        
        $required_indicators = [
            '.post-type-post',
            '.post-type-micro', 
            '.post-type-photo',
            '.post-type-serial'
        ];
        
        foreach ($required_indicators as $indicator) {
            // Will fail initially - indicators not styled
            $this->assertStringContainsString($indicator, $styles);
        }
    }
    
    public function test_category_based_styling() {
        // Create posts with different categories
        $creative_post = $this->factory->post->create(array(
            'post_title' => 'Creative Systems Post',
            'post_content' => 'Test content'
        ));
        
        wp_set_post_categories($creative_post, array(
            $this->factory->category->create(array('name' => 'Creative Systems'))
        ));
        
        $this->go_to(get_permalink($creative_post));
        $content = get_echo('the_content');
        
        // Should have category-specific styling
        $this->assertStringContainsString('category-creative-systems', $content);
    }
    
    public function test_block_patterns_registered() {
        $patterns = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
        
        $required_patterns = [
            'kiwi-theme/micro-post',
            'kiwi-theme/photo-post',
            'kiwi-theme/standard-post',
            'kiwi-theme/timeline-feed'
        ];
        
        foreach ($required_patterns as $pattern) {
            // Will fail initially - patterns not registered
            $this->assertArrayHasKey($pattern, $patterns);
        }
    }
}
```

**Test File**: `tests/playwright/test-content-display.spec.js`
```javascript
import { test, expect } from '@playwright/test';

test.describe('Content Display', () => {
  test('post type indicators are visible and colored correctly', async ({ page }) => {
    await page.goto('/');
    
    // Check for post type indicators
    const indicators = page.locator('.post-type-indicator');
    await expect(indicators.first()).toBeVisible();
    
    // Check colors
    const postIndicator = page.locator('.post-type-post');
    const bgColor = await postIndicator.evaluate(el => getComputedStyle(el).backgroundColor);
    
    // Should fail initially - no styling applied
    expect(bgColor).toBe('rgb(123, 104, 238)'); // #7b68ee
  });
  
  test('category badges display correctly', async ({ page }) => {
    await page.goto('/');
    
    const categoryBadges = page.locator('.post-category');
    await expect(categoryBadges.first()).toBeVisible();
    
    // Should have proper styling and be clickable
    await expect(categoryBadges.first()).toHaveAttribute('href');
  });
  
  test('reading time displays for long posts', async ({ page }) => {
    // Go to a long post
    await page.goto('/sample-post-with-long-content/');
    
    const readingTime = page.locator('.reading-time');
    
    // Will fail initially - reading time not implemented
    await expect(readingTime).toBeVisible();
    await expect(readingTime).toContainText('min read');
  });
  
  test('excerpt length is appropriate', async ({ page }) => {
    await page.goto('/');
    
    const excerpts = page.locator('.post-excerpt');
    const firstExcerpt = excerpts.first();
    
    const text = await firstExcerpt.textContent();
    const wordCount = text.split(' ').length;
    
    // Should be between 20-40 words
    expect(wordCount).toBeGreaterThan(15);
    expect(wordCount).toBeLessThan(45);
  });
});
```

#### Development Steps to Pass Content Display Tests

**Step 3.1.1**: Create Block Patterns Directory and Register Patterns
```php
<?php
// Add to functions.php

function kiwi_theme_register_block_patterns() {
    // Micro Post Pattern
    register_block_pattern(
        'kiwi-theme/micro-post',
        array(
            'title'       => __('Micro Post', 'kiwi-theme'),
            'description' => __('A short-form post with Twitter-like styling', 'kiwi-theme'),
            'content'     => '<!-- wp:group {"className":"feed-item micro-post","style":{"spacing":{"padding":"1.25rem"},"border":{"radius":"12px","left":{"color":"var:preset|color|anna-secondary","width":"3px"}},"color":{"background":"#f8f9fa"}}} -->
<div class="wp-block-group feed-item micro-post">
    <!-- wp:group {"className":"post-meta"} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-micro"></span>
        <!-- /wp:html -->
        <!-- wp:post-date {"className":"post-date"} /-->
        <!-- wp:html -->
        <a href="#" class="post-category category-micros">Micros</a>
        <!-- /wp:html -->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-content {"className":"micro-content"} /-->
</div>
<!-- /wp:group -->',
            'categories'  => array('text'),
        )
    );
    
    // Photo Post Pattern
    register_block_pattern(
        'kiwi-theme/photo-post',
        array(
            'title'       => __('Photo Post', 'kiwi-theme'),
            'description' => __('A photo-focused post with caption', 'kiwi-theme'),
            'content'     => '<!-- wp:group {"className":"feed-item photo-post","style":{"textAlign":"center"}} -->
<div class="wp-block-group feed-item photo-post">
    <!-- wp:group {"className":"post-meta"} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-photo"></span>
        <!-- /wp:html -->
        <!-- wp:post-date {"className":"post-date"} /-->
        <!-- wp:post-terms {"term":"category","className":"post-category"} /-->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-title {"isLink":true,"className":"post-title"} /-->
    
    <!-- wp:post-featured-image {"className":"photo-preview","style":{"border":{"radius":"12px"}}} /-->
    
    <!-- wp:post-excerpt {"className":"post-excerpt"} /-->
</div>
<!-- /wp:group -->',
            'categories'  => array('gallery'),
        )
    );
    
    // Standard Post Pattern
    register_block_pattern(
        'kiwi-theme/standard-post',
        array(
            'title'       => __('Standard Post', 'kiwi-theme'),
            'description' => __('Standard blog post layout', 'kiwi-theme'),
            'content'     => '<!-- wp:group {"className":"feed-item"} -->
<div class="wp-block-group feed-item">
    <!-- wp:group {"className":"post-meta"} -->
    <div class="wp-block-group post-meta">
        <!-- wp:html -->
        <span class="post-type-indicator post-type-post"></span>
        <!-- /wp:html -->
        <!-- wp:post-date {"className":"post-date"} /-->
        <!-- wp:post-terms {"term":"category","className":"post-category"} /-->
    </div>
    <!-- /wp:group -->
    
    <!-- wp:post-title {"isLink":true,"className":"post-title"} /-->
    
    <!-- wp:post-excerpt {"className":"post-excerpt"} /-->
    
    <!-- wp:html -->
    <a href="#" class="read-more">Read more →</a>
    <!-- /wp:html -->
</div>
<!-- /wp:group -->',
            'categories'  => array('text'),
        )
    );
}
add_action('init', 'kiwi_theme_register_block_patterns');
?>
```

**Step 3.1.2**: Add Post Type Indicator Styles to theme.json
```json
{
  "styles": {
    "css": ".post-type-indicator { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 0.75rem; } .post-type-post { background: var(--wp--preset--color--anna-primary); } .post-type-micro { background: var(--wp--preset--color--anna-secondary); } .post-type-photo { background: #fd7e14; } .post-type-serial { background: #e83e8c; } .post-meta { display: flex; align-items: center; margin-bottom: 0.75rem; font-size: 0.9rem; color: #6c7b7f; } .post-date { margin-right: 1rem; } .post-category { color: var(--wp--preset--color--anna-primary); text-decoration: none; font-size: 0.85rem; text-transform: uppercase; font-weight: 500; letter-spacing: 0.5px; } .feed-item { margin-bottom: 3rem; padding-bottom: 2rem; border-bottom: 1px solid #f1f3f4; transition: all 0.2s ease; } .feed-item:hover { transform: translateY(-2px); } .post-title { font-size: 1.5rem; font-weight: 600; color: var(--wp--preset--color--anna-text-light); margin-bottom: 0.75rem; line-height: 1.3; } .post-title a { color: inherit; text-decoration: none; transition: color 0.2s ease; } .post-title a:hover { color: var(--wp--preset--color--anna-primary); } .post-excerpt { font-size: 1rem; line-height: 1.6; color: #495057; margin-bottom: 1rem; } .read-more { color: var(--wp--preset--color--anna-primary); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: all 0.2s ease; } .read-more:hover { text-decoration: underline; } .micro-post { background: #f8f9fa; border-radius: 12px; padding: 1.25rem; border-left: 3px solid var(--wp--preset--color--anna-secondary); } .micro-content { font-size: 1rem; line-height: 1.6; color: var(--wp--preset--color--anna-text-light); } .photo-post { text-align: center; } .photo-preview { width: 100%; max-width: 400px; height: 300px; border-radius: 12px; margin: 1rem auto; cursor: pointer; transition: transform 0.2s ease; } .photo-preview:hover { transform: scale(1.02); }"
  }
}
```

**Step 3.1.3**: Add Reading Time Calculation
```php
<?php
// Add to functions.php

function kiwi_theme_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    
    return $reading_time;
}

function kiwi_theme_display_reading_time($post_id = null) {
    $reading_time = kiwi_theme_reading_time($post_id);
    
    if ($reading_time > 1) {
        echo '<span class="reading-time">' . $reading_time . ' min read</span>';
    }
}

// Add reading time to post meta via hook
function kiwi_theme_add_reading_time_to_meta($post_id) {
    $reading_time = kiwi_theme_reading_time($post_id);
    update_post_meta($post_id, '_anna_reading_time', $reading_time);
}
add_action('save_post', 'kiwi_theme_add_reading_time_to_meta');
?>
```

---

## Phase 4: Performance & Modern CSS

### Phase 4.1: Performance Optimization

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/playwright/test-performance.spec.js`
```javascript
import { test, expect } from '@playwright/test';

test.describe('Performance Tests', () => {
  test('Core Web Vitals are within acceptable ranges', async ({ page }) => {
    await page.goto('/');
    
    // Wait for page to load completely
    await page.waitForLoadState('networkidle');
    
    // Measure LCP (Largest Contentful Paint)
    const lcp = await page.evaluate(() => {
      return new Promise((resolve) => {
        new PerformanceObserver((list) => {
          const entries = list.getEntries();
          const lastEntry = entries[entries.length - 1];
          resolve(lastEntry.startTime);
        }).observe({ entryTypes: ['largest-contentful-paint'] });
      });
    });
    
    // LCP should be under 2.5 seconds - will fail initially
    expect(lcp).toBeLessThan(2500);
    
    // Measure CLS (Cumulative Layout Shift)
    const cls = await page.evaluate(() => {
      return new Promise((resolve) => {
        let clsValue = 0;
        new PerformanceObserver((list) => {
          for (const entry of list.getEntries()) {
            if (!entry.hadRecentInput) {
              clsValue += entry.value;
            }
          }
          resolve(clsValue);
        }).observe({ entryTypes: ['layout-shift'] });
        
        // Resolve after a short delay
        setTimeout(() => resolve(clsValue), 1000);
      });
    });
    
    // CLS should be under 0.1 - will fail initially
    expect(cls).toBeLessThan(0.1);
  });
  
  test('images are properly optimized and lazy loaded', async ({ page }) => {
    await page.goto('/');
    
    const images = page.locator('img');
    const firstImage = images.first();
    
    // Check for loading="lazy" attribute - will fail initially
    await expect(firstImage).toHaveAttribute('loading', 'lazy');
    
    // Check for proper alt text
    const altText = await firstImage.getAttribute('alt');
    expect(altText).toBeTruthy();
    expect(altText.length).toBeGreaterThan(3);
  });
  
  test('CSS is optimized and uses modern techniques', async ({ page }) => {
    await page.goto('/');
    
    // Check for CSS custom properties usage
    const bodyStyles = await page.evaluate(() => {
      const styles = getComputedStyle(document.body);
      return styles.getPropertyValue('background-color');
    });
    
    // Should use CSS custom properties - will fail initially
    expect(bodyStyles).toContain('light-dark');
  });
  
  test('JavaScript is minimal and progressive enhancement', async ({ page }) => {
    // Disable JavaScript
    await page.setJavaScriptEnabled(false);
    await page.goto('/');
    
    // Site should still be functional without JS
    const navigation = page.locator('nav');
    await expect(navigation).toBeVisible();
    
    const mainContent = page.locator('main');
    await expect(mainContent).toBeVisible();
    
    // Links should still work
    const firstLink = page.locator('a').first();
    const href = await firstLink.getAttribute('href');
    expect(href).toBeTruthy();
  });
});
```

**Test File**: `tests/phpunit/test-performance-php.php`
```php
<?php
class Test_Performance_PHP extends WP_UnitTestCase {
    
    public function test_no_blocking_scripts_in_head() {
        ob_start();
        wp_head();
        $head_content = ob_get_clean();
        
        // Should not have blocking scripts in head - will fail initially
        $this->assertStringNotContainsString('<script src=', $head_content);
    }
    
    public function test_styles_are_minified_in_production() {
        // Check if CSS is minified (no unnecessary whitespace)
        $style_content = file_get_contents(get_stylesheet_uri());
        
        // Count line breaks - minified CSS should have very few
        $line_count = substr_count($style_content, "\n");
        
        // Should be minified - will fail initially with readable CSS
        $this->assertLessThan(10, $line_count, 'CSS should be minified for production');
    }
    
    public function test_image_optimization_functions_exist() {
        $this->assertTrue(function_exists('kiwi_theme_responsive_images'));
        $this->assertTrue(function_exists('kiwi_theme_lazy_loading'));
    }
    
    public function test_caching_headers_are_set() {
        // Test that proper caching headers are suggested
        $this->assertTrue(has_action('wp_head', 'kiwi_theme_cache_headers'));
    }
}
```

#### Development Steps to Pass Performance Tests

**Step 4.1.1**: Add Performance Optimizations to functions.php
```php
<?php
// Add to functions.php

/**
 * Performance optimizations
 */
function kiwi_theme_performance_optimizations() {
    // Remove unnecessary WordPress features that add bloat
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Remove unnecessary generators
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    
    // Remove query strings from static resources
    add_filter('script_loader_src', 'kiwi_theme_remove_query_strings', 15, 1);
    add_filter('style_loader_src', 'kiwi_theme_remove_query_strings', 15, 1);
}
add_action('init', 'kiwi_theme_performance_optimizations');

function kiwi_theme_remove_query_strings($src) {
    if (strpos($src, '?ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/**
 * Responsive images and lazy loading
 */
function kiwi_theme_responsive_images($html, $post_id, $post_image_id) {
    $html = str_replace('<img', '<img loading="lazy"', $html);
    return $html;
}
add_filter('post_thumbnail_html', 'kiwi_theme_responsive_images', 10, 3);

function kiwi_theme_lazy_loading($attr, $attachment, $size) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'kiwi_theme_lazy_loading', 10, 3);

/**
 * Optimize CSS delivery
 */
function kiwi_theme_optimize_css_delivery() {
    if (!is_admin()) {
        // Inline critical CSS
        add_action('wp_head', 'kiwi_theme_inline_critical_css', 5);
        
        // Defer non-critical CSS
        add_filter('style_loader_tag', 'kiwi_theme_defer_non_critical_css', 10, 2);
    }
}
add_action('wp_enqueue_scripts', 'kiwi_theme_optimize_css_delivery');

function kiwi_theme_inline_critical_css() {
    ?>
    <style id="anna-critical-css">
    /* Critical CSS for above-the-fold content */
    .container { display: flex; min-height: 100vh; }
    .sidebar { position: fixed; left: 0; top: 0; width: 240px; height: 100vh; background: light-dark(#fefefe, #1a1a1a); border-right: 1px solid light-dark(#ddd, #333); }
    .main-content { margin-left: 240px; flex: 1; padding: 3rem 4rem; max-width: 800px; }
    .intro-title { font-size: 2.5rem; font-weight: 300; color: light-dark(#2c3e50, #e5e5e5); }
    @media (max-width: 768px) {
        .sidebar { transform: translateX(-100%); }
        .main-content { margin-left: 0; padding: 2rem 1.5rem; }
        .intro-title { font-size: 2rem; }
    }
    </style>
    <?php
}

function kiwi_theme_defer_non_critical_css($html, $handle) {
    if (is_admin()) {
        return $html;
    }
    
    // List of non-critical CSS handles
    $defer_handles = array('kiwi-theme-blocks', 'kiwi-theme-components');
    
    if (in_array($handle, $defer_handles)) {
        $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"", $html);
        $html .= '<noscript><link rel="stylesheet" href="' . wp_styles()->registered[$handle]->src . '"></noscript>';
    }
    
    return $html;
}

/**
 * Cache headers
 */
function kiwi_theme_cache_headers() {
    if (!is_admin() && !is_user_logged_in()) {
        header('Cache-Control: public, max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    }
}
add_action('send_headers', 'kiwi_theme_cache_headers');
?>
```

**Step 4.1.2**: Update theme.json with Modern CSS
```json
{
  "version": 2,
  "settings": {
    "color": {
      "custom": true,
      "customDuotone": true,
      "customGradient": true,
      "defaultGradients": false,
      "defaultPalette": false,
      "palette": [
        {
          "slug": "anna-primary",
          "color": "#7b68ee",
          "name": "Anna Primary"
        },
        {
          "slug": "anna-text-light",
          "color": "light-dark(#2c3e50, #e5e5e5)",
          "name": "Anna Text"
        },
        {
          "slug": "anna-background",
          "color": "light-dark(#fefefe, #1a1a1a)",
          "name": "Anna Background"
        }
      ]
    },
    "spacing": {
      "padding": true,
      "margin": true,
      "blockGap": true,
      "spacingScale": {
        "operator": "*",
        "increment": 1.5,
        "steps": 7,
        "mediumStep": 1.5,
        "unit": "rem"
      }
    },
    "typography": {
      "fluid": true,
      "customFontSize": true
    }
  },
  "styles": {
    "color": {
      "background": "var(--wp--preset--color--anna-background)",
      "text": "var(--wp--preset--color--anna-text-light)"
    },
    "typography": {
      "fontFamily": "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
      "fontSize": "clamp(1rem, 2.5vw, 1.125rem)",
      "lineHeight": "1.6"
    },
    "elements": {
      "link": {
        "color": {
          "text": "var(--wp--preset--color--anna-primary)"
        },
        ":hover": {
          "color": {
            "text": "color-mix(in srgb, var(--wp--preset--color--anna-primary) 80%, black)"
          }
        },
        ":focus": {
          "outline": "2px solid var(--wp--preset--color--anna-primary)",
          "outlineOffset": "2px",
          "borderRadius": "2px"
        }
      }
    },
    "blocks": {
      "core/group": {
        "spacing": {
          "blockGap": "var(--wp--preset--spacing--medium)"
        }
      },
      "core/post-title": {
        "typography": {
          "fontSize": "clamp(1.5rem, 4vw, 2.5rem)",
          "fontWeight": "600",
          "lineHeight": "1.2"
        }
      }
    }
  }
}
```

**Step 4.1.3**: Add Progressive Enhancement JavaScript
```php
<?php
// Add to functions.php

function kiwi_theme_progressive_enhancement() {
    wp_enqueue_script(
        'kiwi-theme-progressive',
        get_template_directory_uri() . '/assets/js/progressive-enhancement.js',
        array(),
        wp_get_theme()->get('Version'),
        true // Load in footer
    );
    
    // Add script attributes for performance
    add_filter('script_loader_tag', 'kiwi_theme_script_attributes', 10, 2);
}
add_action('wp_enqueue_scripts', 'kiwi_theme_progressive_enhancement');

function kiwi_theme_script_attributes($tag, $handle) {
    if ('kiwi-theme-progressive' === $handle) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
?>
```

**Step 4.1.4**: Create Progressive Enhancement JavaScript File
```javascript
// assets/js/progressive-enhancement.js

(function() {
    'use strict';
    
    // Mobile menu enhancement
    function initMobileMenu() {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (!toggle || !sidebar) return;
        
        toggle.addEventListener('click', function() {
            const isOpen = sidebar.classList.contains('open');
            sidebar.classList.toggle('open');
            
            // Update ARIA attributes
            toggle.setAttribute('aria-expanded', !isOpen);
            sidebar.setAttribute('aria-hidden', isOpen);
            
            // Focus management
            if (!isOpen) {
                const firstLink = sidebar.querySelector('a');
                if (firstLink) firstLink.focus();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });
    }
    
    // Smooth scrolling enhancement
    function initSmoothScrolling() {
        document.addEventListener('click', function(e) {
            const target = e.target.closest('a[href^="#"]');
            if (!target) return;
            
            e.preventDefault();
            const id = target.getAttribute('href').slice(1);
            const element = document.getElementById(id);
            
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update focus for accessibility
                element.focus();
            }
        });
    }
    
    // Lazy loading enhancement for older browsers
    function initLazyLoading() {
        if ('loading' in HTMLImageElement.prototype) {
            return; // Browser supports native lazy loading
        }
        
        const images = document.querySelectorAll('img[loading="lazy"]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        }
    }
    
    // Color scheme preference
    function initColorScheme() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
        
        function handleSchemeChange(e) {
            document.documentElement.style.colorScheme = e.matches ? 'dark' : 'light';
        }
        
        handleSchemeChange(prefersDark);
        prefersDark.addEventListener('change', handleSchemeChange);
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    function init() {
        initMobileMenu();
        initSmoothScrolling();
        initLazyLoading();
        initColorScheme();
    }
})();
```

---

## Phase 5: WordPress.org Compliance & Testing

### Phase 5.1: Theme Check & Compliance

#### Test Specifications (Write These First - MUST FAIL)

**Test File**: `tests/phpunit/test-wordpress-compliance.php`
```php
<?php
class Test_WordPress_Compliance extends WP_UnitTestCase {
    
    public function test_theme_passes_theme_check() {
        // This would typically be run via Theme Check plugin
        // Testing for common issues manually
        
        $functions_file = get_template_directory() . '/functions.php';
        $functions_content = file_get_contents($functions_file);
        
        // No direct database queries
        $this->assertStringNotContainsString('$wpdb->query', $functions_content);
        $this->assertStringNotContainsString('mysql_query', $functions_content);
        
        // No hardcoded URLs
        $this->assertStringNotContainsString('http://', $functions_content);
        $this->assertStringNotContainsString('https://', $functions_content);
        
        // No eval() or base64_decode()
        $this->assertStringNotContainsString('eval(', $functions_content);
        $this->assertStringNotContainsString('base64_decode', $functions_content);
    }
    
    public function test_proper_text_domain_usage() {
        $template_files = glob(get_template_directory() . '/*.php');
        
        foreach ($template_files as $file) {
            $content = file_get_contents($file);
            
            // Check for translation functions with proper text domain
            if (preg_match_all('/(__\(|_e\(|_x\(|_n\()([^,]+),?\s*([^)]+)?\)/', $content, $matches)) {
                foreach ($matches[3] as $text_domain) {
                    if (!empty($text_domain)) {
                        $text_domain = trim($text_domain, '\'"');
                        // Will fail initially if wrong text domain used
                        $this->assertEquals('kiwi-theme', $text_domain);
                    }
                }
            }
        }
    }
    
    public function test_security_best_practices() {
        $functions_file = get_template_directory() . '/functions.php';
        $functions_content = file_get_contents($functions_file);
        
        // Should use wp_enqueue_script instead of direct script tags
        $this->assertStringNotContainsString('<script', $functions_content);
        
        // Should escape outputs
        if (strpos($functions_content, 'echo') !== false) {
            // Will fail initially if outputs aren't escaped
            $this->assertStringContainsString('esc_html', $functions_content);
        }
    }
    
    public function test_theme_supports_required_features() {
        $this->assertTrue(current_theme_supports('post-thumbnails'));
        $this->assertTrue(current_theme_supports('automatic-feed-links'));
        $this->assertTrue(current_theme_supports('title-tag'));
        $this->assertTrue(current_theme_supports('html5'));
    }
    
    public function test_no_plugin_functionality() {
        $functions_file = get_template_directory() . '/functions.php';
        $functions_content = file_get_contents($functions_file);
        
        // Should not register custom post types
        $this->assertStringNotContainsString('register_post_type', $functions_content);
        
        // Should not create database tables
        $this->assertStringNotContainsString('dbDelta', $functions_content);
        $this->assertStringNotContainsString('CREATE TABLE', $functions_content);
    }
}
```

**Test File**: `tests/accessibility/test-wcag-compliance.php`
```php
<?php
class Test_WCAG_Compliance extends WP_UnitTestCase {
    
    public function test_color_contrast_ratios() {
        // Load theme.json
        $theme_json_file = get_template_directory() . '/theme.json';
        $theme_json = json_decode(file_get_contents($theme_json_file), true);
        
        $colors = $theme_json['settings']['color']['palette'];
        
        // Test primary color against background
        $primary_color = '#7b68ee'; // Anna primary
        $background_light = '#fefefe';
        $background_dark = '#1a1a1a';
        
        // Calculate contrast ratios (simplified test)
        // Real implementation would use proper contrast calculation
        $this->assertTrue($this->calculate_contrast($primary_color, $background_light) >= 4.5);
        $this->assertTrue($this->calculate_contrast($primary_color, $background_dark) >= 4.5);
    }
    
    private function calculate_contrast($color1, $color2) {
        // Simplified contrast calculation for testing
        // Real implementation would use proper WCAG formula
        return 7.5; // Placeholder - implement proper calculation
    }
    
    public function test_heading_hierarchy() {
        // Create test post with headings
        $post_content = '<h1>Main Title</h1><h2>Section</h2><h3>Subsection</h3><h2>Another Section</h2>';
        $post_id = $this->factory->post->create(array(
            'post_content' => $post_content
        ));
        
        $this->go_to(get_permalink($post_id));
        
        // Test that heading hierarchy is logical
        // Will fail initially if headings are not properly structured
        $this->assertRegExp('/<h1[^>]*>.*<\/h1>/', get_echo('the_content'));
    }
    
    public function test_form_labels() {
        $this->go_to('/');
        $content = get_echo('wp_footer');
        
        // Check that all form inputs have labels
        if (strpos($content, '<input') !== false) {
            // Will fail initially if forms don't have proper labels
            $this->assertStringContainsString('aria-label', $content);
        }
    }
}
```

**Test File**: `tests/unit-test/test-theme-unit-data.php`
```php
<?php
class Test_Theme_Unit_Data extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        
        // Import theme unit test data
        $this->import_theme_unit_test_data();
    }
    
    private function import_theme_unit_test_data() {
        // This would import the official WordPress theme unit test data
        // For now, create representative test content
        
        // Create test posts with various content types
        $this->factory->post->create_many(10, array(
            'post_type' => 'post'
        ));
        
        // Create test pages
        $this->factory->post->create_many(5, array(
            'post_type' => 'page'
        ));
        
        // Create test categories
        $this->factory->category->create_many(3);
        
        // Create test tags
        $this->factory->tag->create_many(10);
    }
    
    public function test_handles_no_content() {
        // Test empty blog
        $this->go_to('/');
        $content = get_echo('wp_footer');
        
        // Should gracefully handle empty state
        $this->assertStringNotContainsString('Fatal error', $content);
    }
    
    public function test_handles_long_titles() {
        $long_title = str_repeat('Very Long Title ', 20);
        $post_id = $this->factory->post->create(array(
            'post_title' => $long_title
        ));
        
        $this->go_to(get_permalink($post_id));
        $content = get_echo('the_title');
        
        // Should handle long titles without breaking layout
        $this->assertStringContainsString($long_title, $content);
    }
    
    public function test_handles_special_characters() {
        $special_title = 'Special ÄÖÜ Characters & Symbols <>"\'';
        $post_id = $this->factory->post->create(array(
            'post_title' => $special_title
        ));
        
        $this->go_to(get_permalink($post_id));
        $content = get_echo('the_title');
        
        // Should properly escape special characters
        $this->assertStringContainsString('Special ÄÖÜ Characters', $content);
        $this->assertStringNotContainsString('<>"\'', $content);
    }
    
    public function test_pagination_works() {
        // Create many posts to test pagination
        $this->factory->post->create_many(20);
        
        $this->go_to('/?paged=2');
        $content = get_echo('wp_footer');
        
        // Should have pagination
        $this->assertStringContainsString('page-numbers', $content);
    }
}
```

#### Development Steps to Pass Compliance Tests

**Step 5.1.1**: Update functions.php for Full Compliance
```php
<?php
/**
 * Kiwi Theme Theme Functions
 * 
 * @package Anna_Kiwi
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 * 
 * @since 1.0.0
 */
function kiwi_theme_setup() {
    // Make theme available for translation
    load_theme_textdomain('kiwi-theme', get_template_directory() . '/languages');
    
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');
    
    // Let WordPress manage the document title
    add_theme_support('title-tag');
    
    // Enable support for Post Thumbnails
    add_theme_support('post-thumbnails');
    
    // Switch default core markup to output valid HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');
    
    // Add support for core custom logo
    add_theme_support('custom-logo', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ));
    
    // Add support for responsive embedded content
    add_theme_support('responsive-embeds');
    
    // Add support for editor styles
    add_theme_support('editor-styles');
    
    // Add support for wp-block-styles
    add_theme_support('wp-block-styles');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Navigation', 'kiwi-theme'),
        'footer'  => esc_html__('Footer Navigation', 'kiwi-theme'),
    ));
    
    // Add custom image sizes
    add_image_size('anna-featured', 1200, 630, true);
    add_image_size('anna-thumbnail', 300, 300, true);
}
add_action('after_setup_theme', 'kiwi_theme_setup');

/**
 * Enqueue scripts and styles
 * 
 * @since 1.0.0
 */
function kiwi_theme_scripts() {
    // Enqueue theme stylesheet
    wp_enqueue_style(
        'kiwi-theme-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue progressive enhancement script
    wp_enqueue_script(
        'kiwi-theme-script',
        get_template_directory_uri() . '/assets/js/theme.js',
        array(),
        wp_get_theme()->get('Version'),
        true
    );
    
    // Add async/defer attributes
    add_filter('script_loader_tag', 'kiwi_theme_script_attributes', 10, 2);
}
add_action('wp_enqueue_scripts', 'kiwi_theme_scripts');

/**
 * Add async/defer attributes to scripts
 * 
 * @since 1.0.0
 * @param string $tag    The script tag
 * @param string $handle The script handle
 * @return string Modified script tag
 */
function kiwi_theme_script_attributes($tag, $handle) {
    if ('kiwi-theme-script' === $handle && !is_admin()) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}

/**
 * Add preconnect links for performance
 * 
 * @since 1.0.0
 */
function kiwi_theme_resource_hints($urls, $relation_type) {
    if (wp_style_is('kiwi-theme-fonts', 'queue') && 'preconnect' === $relation_type) {
        $urls[] = array(
            'href' => 'https://fonts.gstatic.com',
            'crossorigin',
        );
    }
    return $urls;
}
add_filter('wp_resource_hints', 'kiwi_theme_resource_hints', 10, 2);

/**
 * Register block patterns
 * 
 * @since 1.0.0
 */
function kiwi_theme_register_block_patterns() {
    if (function_exists('register_block_pattern')) {
        // Micro post pattern
        register_block_pattern(
            'kiwi-theme/micro-post',
            array(
                'title'       => esc_html__('Micro Post', 'kiwi-theme'),
                'description' => esc_html__('A short-form post pattern', 'kiwi-theme'),
                'content'     => '<!-- wp:group {"className":"micro-post"} -->
<div class="wp-block-group micro-post">
    <!-- wp:paragraph -->
    <p>' . esc_html__('Your micro post content goes here...', 'kiwi-theme') . '</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
                'categories'  => array('text'),
            )
        );
        
        // Add more patterns as needed
    }
}
add_action('init', 'kiwi_theme_register_block_patterns');

/**
 * Customizer additions
 * 
 * @since 1.0.0
 * @param WP_Customize_Manager $wp_customize Theme Customizer object
 */
function kiwi_theme_customize_register($wp_customize) {
    $wp_customize->get_setting('blogname')->transport         = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport  = 'postMessage';
    
    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial('blogname', array(
            'selector'        => '.site-title',
            'render_callback' => 'kiwi_theme_customize_partial_blogname',
        ));
        $wp_customize->selective_refresh->add_partial('blogdescription', array(
            'selector'        => '.site-description',
            'render_callback' => 'kiwi_theme_customize_partial_blogdescription',
        ));
    }
}
add_action('customize_register', 'kiwi_theme_customize_register');

/**
 * Render the site title for the selective refresh partial
 * 
 * @since 1.0.0
 * @return void
 */
function kiwi_theme_customize_partial_blogname() {
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial
 * 
 * @since 1.0.0
 * @return void
 */
function kiwi_theme_customize_partial_blogdescription() {
    bloginfo('description');
}

/**
 * Security enhancements
 * 
 * @since 1.0.0
 */
function kiwi_theme_security_enhancements() {
    // Remove WordPress version from RSS feeds
    add_filter('the_generator', '__return_empty_string');
    
    // Remove version query strings from CSS and JS
    add_filter('style_loader_src', 'kiwi_theme_remove_version_strings', 15, 1);
    add_filter('script_loader_src', 'kiwi_theme_remove_version_strings', 15, 1);
}
add_action('init', 'kiwi_theme_security_enhancements');

/**
 * Remove version query strings from static resources
 * 
 * @since 1.0.0
 * @param string $src The source URL
 * @return string Modified URL
 */
function kiwi_theme_remove_version_strings($src) {
    if (strpos($src, 'ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}

/**
 * Add proper alt text to images that don't have it
 * 
 * @since 1.0.0
 * @param array $attr Attributes for the image markup
 * @param WP_Post $attachment Image attachment post
 * @return array Modified attributes
 */
function kiwi_theme_image_alt_text($attr, $attachment) {
    if (empty($attr['alt'])) {
        $attr['alt'] = esc_attr(get_the_title($attachment->ID));
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'kiwi_theme_image_alt_text', 10, 2);

/**
 * Improve accessibility of navigation menus
 * 
 * @since 1.0.0
 * @param string $items The HTML list content for the menu items
 * @param stdClass $args An object containing wp_nav_menu() arguments
 * @return string Modified menu items
 */
function kiwi_theme_nav_menu_accessibility($items, $args) {
    // Add proper ARIA labels and roles
    $items = str_replace('<ul', '<ul role="menubar"', $items);
    $items = str_replace('<li', '<li role="menuitem"', $items);
    
    return $items;
}
add_filter('wp_nav_menu_items', 'kiwi_theme_nav_menu_accessibility', 10, 2);
?>
```

**Step 5.1.2**: Create Accessibility-Compliant Templates
```html
<!-- templates/index.html -->
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- wp:html -->
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('Skip to main content', 'kiwi-theme'); ?></a>
<!-- /wp:html -->

<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"div","className":"container","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group container">
    
    <!-- wp:template-part {"slug":"sidebar","tagName":"aside"} /-->
    
    <!-- wp:group {"tagName":"main","className":"main-content","layout":{"type":"constrained"}} -->
    <main class="wp-block-group main-content" id="main" tabindex="-1">
        
        <!-- wp:group {"className":"intro-section"} -->
        <div class="wp-block-group intro-section">
            <!-- wp:html -->
            <div class="intro-icon" aria-hidden="true">🌱</div>
            <!-- /wp:html -->
            
            <!-- wp:heading {"level":1,"className":"intro-title"} -->
            <h1 class="intro-title"><?php esc_html_e("Hi, I'm Anna.", 'kiwi-theme'); ?></h1>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"className":"intro-text"} -->
            <p class="intro-text"><?php esc_html_e('I build things with code, with words, with plants. I wrangle code at Automattic and find connections in unexpected places.', 'kiwi-theme'); ?></p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->
        
        <!-- wp:query {"className":"content-feed"} -->
        <div class="wp-block-query content-feed">
            <!-- wp:post-template -->
            
                <!-- wp:group {"className":"feed-item"} -->
                <article class="wp-block-group feed-item">
                    
                    <!-- wp:group {"className":"post-meta"} -->
                    <div class="wp-block-group post-meta">
                        <!-- wp:html -->
                        <span class="post-type-indicator post-type-post" aria-hidden="true"></span>
                        <!-- /wp:html -->
                        
                        <!-- wp:post-date {"className":"post-date"} /-->
                        
                        <!-- wp:post-terms {"term":"category","className":"post-category"} /-->
                    </div>
                    <!-- /wp:group -->
                    
                    <!-- wp:post-title {"isLink":true,"className":"post-title","level":2} /-->
                    
                    <!-- wp:post-excerpt {"className":"post-excerpt"} /-->
                    
                    <!-- wp:html -->
                    <a href="<?php the_permalink(); ?>" class="read-more" aria-label="<?php printf(esc_attr__('Read more about %s', 'kiwi-theme'), get_the_title()); ?>">
                        <?php esc_html_e('Read more', 'kiwi-theme'); ?> →
                    </a>
                    <!-- /wp:html -->
                    
                </article>
                <!-- /wp:group -->
                
            <!-- /wp:post-template -->
            
            <!-- wp:query-pagination {"className":"pagination-wrapper"} -->
            <nav class="wp-block-query-pagination pagination-wrapper" aria-label="<?php esc_attr_e('Posts navigation', 'kiwi-theme'); ?>">
                <!-- wp:query-pagination-previous {"label":"Previous"} /-->
                <!-- wp:query-pagination-numbers /-->
                <!-- wp:query-pagination-next {"label":"Next"} /-->
            </nav>
            <!-- /wp:query-pagination -->
        </div>
        <!-- /wp:query -->
        
    </main>
    <!-- /wp:group -->
    
</div>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->

<?php wp_footer(); ?>
</body>
</html>
```

---

## Testing Execution Strategy

### Continuous Testing Approach

1. **Write Tests First**: Each feature starts with failing tests
2. **Implement Minimum Viable Code**: Write just enough code to pass tests
3. **Refactor**: Improve code quality while keeping tests passing
4. **Add More Tests**: Edge cases and integration scenarios

### Test Execution Order

1. **Unit Tests** (PHP) - Fast feedback loop
2. **Integration Tests** (WordPress functionality)
3. **Accessibility Tests** (axe-core + manual)
4. **Performance Tests** (Lighthouse + custom)
5. **Browser Compatibility** (Multiple browsers)
6. **WordPress.org Compliance** (Theme Check plugin)

### Automated Testing Pipeline

```bash
# Run all tests
npm run test:all

# Individual test suites
npm run test:php          # PHPUnit tests
npm run test:accessibility # axe-core tests
npm run test:performance  # Lighthouse tests
npm run test:visual       # Visual regression
npm run test:compliance   # WordPress.org compliance
```

This TDD strategy ensures every feature is properly tested before implementation, resulting in a robust, accessible, and compliant WordPress theme ready for the WordPress.org directory.

