<?php
/**
 * Tests for Kiwi Theme structure and basic requirements
 * 
 * These tests validate the basic theme structure and WordPress compliance
 */

class Test_Theme_Structure extends Kiwi_Theme_Test_Case {
    
    /**
     * Test that theme.json file exists
     * 
     * @test
     */
    public function test_theme_json_exists() {
        $this->assertThemeFileExists( 'theme.json', 'theme.json is required for FSE themes' );
    }
    
    /**
     * Test that style.css exists with proper WordPress theme headers
     * 
     * @test
     */
    public function test_style_css_exists_with_proper_headers() {
        $this->assertThemeFileExists( 'style.css', 'style.css is required for all WordPress themes' );
        
        $style_content = file_get_contents( $this->theme_dir . '/style.css' );
        
        // Test required theme headers
        $required_headers = [
            'Theme Name: Kiwi Theme',
            'Version: 1.0.0',
            'License: GPL v2 or later',
            'Text Domain: kiwi-theme',
            'Author: Anna McPhee'
        ];
        
        foreach ( $required_headers as $header ) {
            $this->assertStringContainsString( $header, $style_content, "style.css must contain header: {$header}" );
        }
    }
    
    /**
     * Test that all required template files exist
     * 
     * @test
     */
    public function test_required_templates_exist() {
        $required_templates = [
            'templates/index.html',
            'templates/single.html', 
            'templates/archive.html',
            'templates/page.html',
            'templates/404.html'
        ];
        
        foreach ( $required_templates as $template ) {
            $this->assertThemeFileExists( $template, "Required template {$template} must exist" );
        }
    }
    
    /**
     * Test that required template parts exist
     * 
     * @test
     */
    public function test_required_template_parts_exist() {
        $required_parts = [
            'parts/header.html',
            'parts/footer.html',
            'parts/sidebar.html',
            'parts/comments.html'
        ];
        
        foreach ( $required_parts as $part ) {
            $this->assertThemeFileExists( $part, "Required template part {$part} must exist" );
        }
    }
    
    /**
     * Test that functions.php exists and loads without errors
     * 
     * @test
     */
    public function test_functions_php_exists_and_loads() {
        $this->assertThemeFileExists( 'functions.php', 'functions.php is required for theme functionality' );
        
        // Test that it doesn't cause fatal errors when included
        ob_start();
        $error_level = error_reporting( 0 );
        
        try {
            include_once $this->theme_dir . '/functions.php';
            $output = ob_get_clean();
            error_reporting( $error_level );
            
            // functions.php should not produce direct output
            $this->assertEmpty( $output, 'functions.php should not produce direct output' );
        } catch ( Exception $e ) {
            ob_end_clean();
            error_reporting( $error_level );
            $this->fail( 'functions.php caused an error: ' . $e->getMessage() );
        }
    }
    
    /**
     * Test that block patterns directory exists with required patterns
     * 
     * @test
     */
    public function test_block_patterns_exist() {
        $this->assertThemeFileExists( 'patterns', 'patterns directory must exist for block patterns' );
        
        $required_patterns = [
            'patterns/micro-post.php',
            'patterns/photo-post.php', 
            'patterns/standard-post.php',
            'patterns/no-results.php'
        ];
        
        foreach ( $required_patterns as $pattern ) {
            $this->assertThemeFileExists( $pattern, "Required pattern {$pattern} must exist" );
        }
    }
    
    /**
     * Test that theme assets directory structure exists
     * 
     * @test
     */
    public function test_assets_directory_structure() {
        $required_assets = [
            'assets/js/theme.js'
        ];
        
        foreach ( $required_assets as $asset ) {
            $this->assertThemeFileExists( $asset, "Required asset {$asset} must exist" );
        }
    }
    
    /**
     * Test that theme text domain is consistent
     * 
     * @test
     */
    public function test_text_domain_consistency() {
        $expected_text_domain = 'kiwi-theme';
        
        // Check style.css
        $style_content = file_get_contents( $this->theme_dir . '/style.css' );
        $this->assertStringContainsString( "Text Domain: {$expected_text_domain}", $style_content );
        
        // Check functions.php for load_theme_textdomain call
        if ( file_exists( $this->theme_dir . '/functions.php' ) ) {
            $functions_content = file_get_contents( $this->theme_dir . '/functions.php' );
            $this->assertStringContainsString( "'{$expected_text_domain}'", $functions_content, 
                'functions.php must use consistent text domain' );
        }
    }
}