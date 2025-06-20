<?php
/**
 * Theme testing utilities for Kiwi Theme
 * 
 * Helper functions and assertions for theme-specific testing
 */

/**
 * Base test case class for Kiwi Theme tests
 */
abstract class Kiwi_Theme_Test_Case extends WP_UnitTestCase {
    
    /**
     * Theme directory path
     * 
     * @var string
     */
    protected $theme_dir;
    
    /**
     * Theme URI
     * 
     * @var string
     */
    protected $theme_uri;
    
    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        $this->theme_dir = get_template_directory();
        $this->theme_uri = get_template_directory_uri();
        
        // Ensure theme directory exists
        if ( ! is_dir( $this->theme_dir ) ) {
            $this->markTestSkipped( 'Theme directory not found: ' . $this->theme_dir );
        }
    }
    
    /**
     * Assert that a file exists in the theme directory
     * 
     * @param string $file_path Relative path from theme root
     * @param string $message   Optional custom message
     */
    protected function assertThemeFileExists( $file_path, $message = '' ) {
        $full_path = $this->theme_dir . '/' . ltrim( $file_path, '/' );
        $this->assertFileExists( $full_path, $message );
    }
    
    /**
     * Assert that theme.json contains required properties
     * 
     * @param array $required_properties Array of required property paths
     */
    protected function assertThemeJsonHasProperties( $required_properties ) {
        $theme_json_path = $this->theme_dir . '/theme.json';
        $this->assertFileExists( $theme_json_path, 'theme.json must exist' );
        
        $theme_json_content = file_get_contents( $theme_json_path );
        $theme_json = json_decode( $theme_json_content, true );
        
        $this->assertNotNull( $theme_json, 'theme.json must be valid JSON' );
        
        foreach ( $required_properties as $property_path ) {
            $this->assertArrayHasNestedKey( $theme_json, $property_path, "theme.json must contain {$property_path}" );
        }
    }
    
    /**
     * Assert that an array has a nested key using dot notation
     * 
     * @param array  $array        The array to check
     * @param string $key_path     Dot-separated key path (e.g., 'settings.color.palette')
     * @param string $message      Optional custom message
     */
    protected function assertArrayHasNestedKey( $array, $key_path, $message = '' ) {
        $keys = explode( '.', $key_path );
        $current = $array;
        
        foreach ( $keys as $key ) {
            $this->assertArrayHasKey( $key, $current, $message ?: "Missing key: {$key_path}" );
            $current = $current[ $key ];
        }
    }
    
    /**
     * Assert that a CSS file contains specific properties
     * 
     * @param string $css_content  The CSS content to check
     * @param array  $properties   Array of CSS properties to find
     */
    protected function assertCssContainsProperties( $css_content, $properties ) {
        foreach ( $properties as $property ) {
            $this->assertStringContainsString( $property, $css_content, "CSS must contain property: {$property}" );
        }
    }
    
    /**
     * Get theme.json data as associative array
     * 
     * @return array|null
     */
    protected function getThemeJsonData() {
        $theme_json_path = $this->theme_dir . '/theme.json';
        if ( ! file_exists( $theme_json_path ) ) {
            return null;
        }
        
        $content = file_get_contents( $theme_json_path );
        return json_decode( $content, true );
    }
}