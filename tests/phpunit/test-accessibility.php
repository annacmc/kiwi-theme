<?php
/**
 * Tests for accessibility compliance (WCAG 2.1 AA)
 * 
 * These tests validate the accessibility features and compliance
 */

class Test_Accessibility extends Kiwi_Theme_Test_Case {
    
    /**
     * Test that template files contain required accessibility landmarks
     * 
     * @test
     */
    public function test_template_accessibility_landmarks() {
        $templates_to_test = [
            'templates/index.html',
            'templates/single.html',
            'templates/archive.html',
            'templates/page.html'
        ];
        
        foreach ( $templates_to_test as $template_path ) {
            $this->assertThemeFileExists( $template_path );
            
            $template_content = file_get_contents( $this->theme_dir . '/' . $template_path );
            
            // Test for main landmark
            $this->assertStringContainsString( 'role="main"', $template_content, 
                "{$template_path} must contain main landmark" );
        }
    }
    
    /**
     * Test that header template part has proper accessibility structure
     * 
     * @test
     */
    public function test_header_accessibility() {
        $header_path = 'parts/header.html';
        $this->assertThemeFileExists( $header_path );
        
        $header_content = file_get_contents( $this->theme_dir . '/' . $header_path );
        
        // Test for header landmark
        $this->assertStringContainsString( 'role="banner"', $header_content, 
            'Header must contain banner landmark' );
        
        // Test for skip link
        $this->assertStringContainsString( 'skip-link', $header_content, 
            'Header must contain skip link for keyboard navigation' );
        
        // Test for navigation landmark
        $this->assertStringContainsString( 'role="navigation"', $header_content, 
            'Header must contain navigation landmark' );
    }
    
    /**
     * Test that footer template part has proper accessibility structure
     * 
     * @test
     */
    public function test_footer_accessibility() {
        $footer_path = 'parts/footer.html';
        $this->assertThemeFileExists( $footer_path );
        
        $footer_content = file_get_contents( $this->theme_dir . '/' . $footer_path );
        
        // Test for contentinfo landmark
        $this->assertStringContainsString( 'role="contentinfo"', $footer_content, 
            'Footer must contain contentinfo landmark' );
    }
    
    /**
     * Test that theme JavaScript includes proper accessibility features
     * 
     * @test
     */
    public function test_javascript_accessibility() {
        $js_path = 'assets/js/theme.js';
        $this->assertThemeFileExists( $js_path );
        
        $js_content = file_get_contents( $this->theme_dir . '/' . $js_path );
        
        // Test for keyboard navigation support
        $this->assertStringContainsString( 'keydown', $js_content, 
            'Theme JavaScript must include keyboard event handling' );
        
        // Test for focus management
        $this->assertStringContainsString( 'focus', $js_content, 
            'Theme JavaScript must include focus management' );
        
        // Test for ARIA attributes handling
        $this->assertStringContainsString( 'aria-', $js_content, 
            'Theme JavaScript must handle ARIA attributes' );
    }
    
    /**
     * Test that CSS includes proper focus indicators
     * 
     * @test
     */
    public function test_css_focus_indicators() {
        $style_path = 'style.css';
        $this->assertThemeFileExists( $style_path );
        
        $css_content = file_get_contents( $this->theme_dir . '/' . $style_path );
        
        // Test for focus styles
        $focus_patterns = [
            ':focus',
            ':focus-visible',
            'outline'
        ];
        
        foreach ( $focus_patterns as $pattern ) {
            $this->assertStringContainsString( $pattern, $css_content, 
                "CSS must contain focus indicator: {$pattern}" );
        }
    }
    
    /**
     * Test that theme.json includes accessibility-friendly color contrast
     * 
     * @test
     */
    public function test_theme_json_color_contrast() {
        $theme_json_data = $this->getThemeJsonData();
        
        $this->assertArrayHasKey( 'settings', $theme_json_data );
        $this->assertArrayHasKey( 'color', $theme_json_data['settings'] );
        $this->assertArrayHasKey( 'palette', $theme_json_data['settings']['color'] );
        
        $palette = $theme_json_data['settings']['color']['palette'];
        
        // Test that primary colors exist (required for contrast testing)
        $palette_slugs = array_column( $palette, 'slug' );
        $this->assertContains( 'background', $palette_slugs, 'Background color must be defined for contrast testing' );
        $this->assertContains( 'foreground', $palette_slugs, 'Foreground color must be defined for contrast testing' );
        
        // Test that colors are not identical (basic contrast check)
        $background_color = null;
        $foreground_color = null;
        
        foreach ( $palette as $color ) {
            if ( $color['slug'] === 'background' ) {
                $background_color = $color['color'];
            }
            if ( $color['slug'] === 'foreground' ) {
                $foreground_color = $color['color'];
            }
        }
        
        $this->assertNotEquals( $background_color, $foreground_color, 
            'Background and foreground colors must be different for proper contrast' );
    }
    
    /**
     * Test that form elements have proper accessibility attributes
     * 
     * @test
     */
    public function test_form_accessibility() {
        $templates_with_forms = [
            'templates/search.html',
            'parts/comments.html'
        ];
        
        foreach ( $templates_with_forms as $template_path ) {
            if ( file_exists( $this->theme_dir . '/' . $template_path ) ) {
                $template_content = file_get_contents( $this->theme_dir . '/' . $template_path );
                
                // If template contains forms, test for proper labeling
                if ( strpos( $template_content, '<form' ) !== false || 
                     strpos( $template_content, 'wp:search' ) !== false ) {
                    
                    // Test for form labels or aria-label
                    $has_accessibility = strpos( $template_content, 'aria-label' ) !== false ||
                                       strpos( $template_content, '<label' ) !== false ||
                                       strpos( $template_content, 'aria-labelledby' ) !== false;
                    
                    $this->assertTrue( $has_accessibility, 
                        "{$template_path} forms must have proper accessibility labels" );
                }
            }
        }
    }
    
    /**
     * Test that images have proper alt text handling
     * 
     * @test
     */
    public function test_image_accessibility() {
        $templates_to_test = [
            'templates/index.html',
            'templates/single.html',
            'templates/archive.html'
        ];
        
        foreach ( $templates_to_test as $template_path ) {
            $template_content = file_get_contents( $this->theme_dir . '/' . $template_path );
            
            // If template contains images, they should have alt attribute handling
            if ( strpos( $template_content, 'wp:image' ) !== false ||
                 strpos( $template_content, 'wp:post-featured-image' ) !== false ) {
                
                // WordPress blocks handle alt text automatically, but we test for proper implementation
                $this->assertStringNotContainsString( 'alt=""', $template_content, 
                    "{$template_path} should not contain empty alt attributes" );
            }
        }
    }
    
    /**
     * Test that theme supports screen reader text
     * 
     * @test
     */
    public function test_screen_reader_support() {
        $css_content = file_get_contents( $this->theme_dir . '/style.css' );
        
        // Test for screen reader text class
        $screen_reader_patterns = [
            'screen-reader-text',
            'sr-only', 
            'visually-hidden'
        ];
        
        $has_screen_reader_class = false;
        foreach ( $screen_reader_patterns as $pattern ) {
            if ( strpos( $css_content, $pattern ) !== false ) {
                $has_screen_reader_class = true;
                break;
            }
        }
        
        $this->assertTrue( $has_screen_reader_class, 
            'Theme must include screen reader text utilities' );
    }
    
    /**
     * Test that functions.php includes accessibility-related theme support
     * 
     * @test
     */
    public function test_functions_accessibility_support() {
        $functions_content = file_get_contents( $this->theme_dir . '/functions.php' );
        
        // Test for accessibility-related theme support
        $accessibility_features = [
            'html5', // HTML5 support for better semantics
            'title-tag' // Dynamic title tag support
        ];
        
        foreach ( $accessibility_features as $feature ) {
            $this->assertStringContainsString( $feature, $functions_content, 
                "functions.php must add theme support for {$feature}" );
        }
    }
}