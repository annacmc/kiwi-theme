<?php
/**
 * Kiwi Theme Functions
 * 
 * @package Kiwi_Theme
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
    
    // Add support for wide and full alignment
    add_theme_support('align-wide');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => esc_html__('Primary Navigation', 'kiwi-theme'),
        'footer'  => esc_html__('Footer Navigation', 'kiwi-theme'),
    ));
    
    // Add custom image sizes
    add_image_size('kiwi-featured', 1200, 630, true);
    add_image_size('kiwi-thumbnail', 300, 300, true);
    add_image_size('kiwi-micro', 150, 150, true);
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
 * Mobile menu button
 * 
 * @since 1.0.0
 */
function kiwi_theme_mobile_menu_button() {
    ?>
    <button class="mobile-menu-toggle" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle mobile menu', 'kiwi-theme'); ?>">
        <span class="dashicons dashicons-menu" aria-hidden="true"></span>
    </button>
    <?php
}

/**
 * Progressive enhancement JavaScript for mobile menu
 * 
 * @since 1.0.0
 */
function kiwi_theme_mobile_menu_script() {
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (mobileToggle && sidebar) {
            mobileToggle.addEventListener('click', function() {
                const isOpen = sidebar.classList.contains('open');
                sidebar.classList.toggle('open');
                
                // Update ARIA attributes
                mobileToggle.setAttribute('aria-expanded', !isOpen);
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
                    mobileToggle.setAttribute('aria-expanded', 'false');
                    mobileToggle.focus();
                }
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target) && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                    mobileToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'kiwi_theme_mobile_menu_script');

/**
 * Calculate reading time for a post
 * 
 * @since 1.0.0
 * @param int $post_id Post ID
 * @return int Reading time in minutes
 */
function kiwi_theme_reading_time($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $content = get_post_field('post_content', $post_id);
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    
    return $reading_time;
}

/**
 * Display reading time
 * 
 * @since 1.0.0
 * @param int $post_id Post ID
 */
function kiwi_theme_display_reading_time($post_id = null) {
    $reading_time = kiwi_theme_reading_time($post_id);
    
    if ($reading_time > 1) {
        printf(
            '<span class="reading-time">%s</span>',
            sprintf(
                /* translators: %d: reading time in minutes */
                esc_html(_n('%d min read', '%d min read', $reading_time, 'kiwi-theme')),
                $reading_time
            )
        );
    }
}

/**
 * Add reading time to post meta
 * 
 * @since 1.0.0
 * @param int $post_id Post ID
 */
function kiwi_theme_add_reading_time_to_meta($post_id) {
    $reading_time = kiwi_theme_reading_time($post_id);
    update_post_meta($post_id, '_kiwi_reading_time', $reading_time);
}
add_action('save_post', 'kiwi_theme_add_reading_time_to_meta');

/**
 * Register block patterns
 * 
 * @since 1.0.0
 */
function kiwi_theme_register_block_patterns() {
    if (function_exists('register_block_pattern')) {
        
        // Register pattern categories
        register_block_pattern_category(
            'kiwi-theme',
            array('label' => esc_html__('Kiwi Theme', 'kiwi-theme'))
        );
        
        // Micro post pattern
        register_block_pattern(
            'kiwi-theme/micro-post',
            array(
                'title'       => esc_html__('Micro Post', 'kiwi-theme'),
                'description' => esc_html__('A short-form post pattern for Twitter-like content', 'kiwi-theme'),
                'categories'  => array('kiwi-theme', 'text'),
                'keywords'    => array('micro', 'short', 'twitter', 'social'),
                'content'     => file_get_contents(get_template_directory() . '/patterns/micro-post.php'),
            )
        );
        
        // Photo post pattern
        register_block_pattern(
            'kiwi-theme/photo-post',
            array(
                'title'       => esc_html__('Photo Post', 'kiwi-theme'),
                'description' => esc_html__('A photo-focused post pattern with caption support', 'kiwi-theme'),
                'categories'  => array('kiwi-theme', 'gallery', 'media'),
                'keywords'    => array('photo', 'image', 'gallery', 'visual'),
                'content'     => file_get_contents(get_template_directory() . '/patterns/photo-post.php'),
            )
        );
        
        // Standard post pattern
        register_block_pattern(
            'kiwi-theme/standard-post',
            array(
                'title'       => esc_html__('Standard Post', 'kiwi-theme'),
                'description' => esc_html__('Standard blog post layout with meta information', 'kiwi-theme'),
                'categories'  => array('kiwi-theme', 'text'),
                'keywords'    => array('post', 'blog', 'article', 'standard'),
                'content'     => file_get_contents(get_template_directory() . '/patterns/standard-post.php'),
            )
        );
        
        // No results pattern
        register_block_pattern(
            'kiwi-theme/hidden-no-results',
            array(
                'title'       => esc_html__('No Results Found', 'kiwi-theme'),
                'description' => esc_html__('Hidden pattern for no search results', 'kiwi-theme'),
                'categories'  => array('kiwi-theme'),
                'keywords'    => array('no results', 'search', 'empty'),
                'inserter'    => false,
                'content'     => file_get_contents(get_template_directory() . '/patterns/no-results.php'),
            )
        );
    }
}
add_action('init', 'kiwi_theme_register_block_patterns');

/**
 * Enhanced accessibility features
 * 
 * @since 1.0.0
 */
function kiwi_theme_accessibility_enhancements() {
    // Add skip link to wp_body_open
    add_action('wp_body_open', 'kiwi_theme_skip_link');
    
    // Enhance navigation with proper ARIA
    add_filter('wp_nav_menu_args', 'kiwi_theme_nav_menu_args');
    
    // Add ARIA landmarks to content
    add_filter('body_class', 'kiwi_theme_body_class');
}
add_action('init', 'kiwi_theme_accessibility_enhancements');

/**
 * Add skip link
 * 
 * @since 1.0.0
 */
function kiwi_theme_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#main">' . esc_html__('Skip to main content', 'kiwi-theme') . '</a>';
}

/**
 * Enhance navigation menu arguments
 * 
 * @since 1.0.0
 * @param array $args Nav menu arguments
 * @return array Modified arguments
 */
function kiwi_theme_nav_menu_args($args) {
    if (!isset($args['container_aria_label']) && isset($args['theme_location'])) {
        switch ($args['theme_location']) {
            case 'primary':
                $args['container_aria_label'] = esc_attr__('Main navigation', 'kiwi-theme');
                break;
            case 'footer':
                $args['container_aria_label'] = esc_attr__('Footer navigation', 'kiwi-theme');
                break;
        }
    }
    return $args;
}

/**
 * Add helpful body classes
 * 
 * @since 1.0.0
 * @param array $classes Body classes
 * @return array Modified classes
 */
function kiwi_theme_body_class($classes) {
    // Add class for JavaScript support
    $classes[] = 'no-js';
    
    // Add class for color scheme preference
    $classes[] = 'supports-light-dark';
    
    return $classes;
}