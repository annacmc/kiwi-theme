<?php
/**
 * Tests for theme.json design system validation
 * 
 * These tests validate the theme.json configuration and design tokens
 */

class Test_Theme_Json extends Kiwi_Theme_Test_Case {
    
    /**
     * Test that theme.json has valid JSON syntax
     * 
     * @test
     */
    public function test_theme_json_valid_syntax() {
        $theme_json_data = $this->getThemeJsonData();
        
        $this->assertNotNull( $theme_json_data, 'theme.json must contain valid JSON' );
        $this->assertIsArray( $theme_json_data, 'theme.json must be a valid JSON object' );
    }
    
    /**
     * Test that theme.json uses correct schema version
     * 
     * @test
     */
    public function test_theme_json_schema_version() {
        $theme_json_data = $this->getThemeJsonData();
        
        $this->assertArrayHasKey( 'version', $theme_json_data, 'theme.json must specify schema version' );
        $this->assertEquals( 2, $theme_json_data['version'], 'theme.json must use schema version 2' );
    }
    
    /**
     * Test that theme.json has required color palette
     * 
     * @test
     */
    public function test_theme_json_color_palette() {
        $required_color_properties = [
            'settings.color.palette'
        ];
        
        $this->assertThemeJsonHasProperties( $required_color_properties );
        
        $theme_json_data = $this->getThemeJsonData();
        $color_palette = $theme_json_data['settings']['color']['palette'];
        
        $this->assertIsArray( $color_palette, 'Color palette must be an array' );
        $this->assertNotEmpty( $color_palette, 'Color palette must not be empty' );
        
        // Test required color entries exist
        $required_colors = ['primary', 'secondary', 'accent', 'background', 'foreground'];
        $palette_slugs = array_column( $color_palette, 'slug' );
        
        foreach ( $required_colors as $required_color ) {
            $this->assertContains( $required_color, $palette_slugs, 
                "Color palette must contain '{$required_color}' color" );
        }
        
        // Test color structure
        foreach ( $color_palette as $color ) {
            $this->assertArrayHasKey( 'name', $color, 'Each color must have a name' );
            $this->assertArrayHasKey( 'slug', $color, 'Each color must have a slug' );
            $this->assertArrayHasKey( 'color', $color, 'Each color must have a color value' );
            
            // Test color value format (hex, rgb, hsl, or CSS custom property)
            $color_value = $color['color'];
            $this->assertTrue(
                preg_match( '/^(#[0-9a-fA-F]{3,8}|rgb\(|rgba\(|hsl\(|hsla\(|var\(|light-dark\()/', $color_value ),
                "Color value '{$color_value}' must be a valid CSS color format"
            );
        }
    }
    
    /**
     * Test that theme.json has required typography settings
     * 
     * @test
     */
    public function test_theme_json_typography() {
        $required_typography_properties = [
            'settings.typography.fontFamilies',
            'settings.typography.fontSizes'
        ];
        
        $this->assertThemeJsonHasProperties( $required_typography_properties );
        
        $theme_json_data = $this->getThemeJsonData();
        
        // Test font families
        $font_families = $theme_json_data['settings']['typography']['fontFamilies'];
        $this->assertIsArray( $font_families, 'Font families must be an array' );
        $this->assertNotEmpty( $font_families, 'Font families must not be empty' );
        
        foreach ( $font_families as $font_family ) {
            $this->assertArrayHasKey( 'name', $font_family, 'Each font family must have a name' );
            $this->assertArrayHasKey( 'slug', $font_family, 'Each font family must have a slug' );
            $this->assertArrayHasKey( 'fontFamily', $font_family, 'Each font family must have a fontFamily value' );
        }
        
        // Test font sizes
        $font_sizes = $theme_json_data['settings']['typography']['fontSizes'];
        $this->assertIsArray( $font_sizes, 'Font sizes must be an array' );
        $this->assertNotEmpty( $font_sizes, 'Font sizes must not be empty' );
        
        $required_sizes = ['small', 'medium', 'large', 'x-large'];
        $size_slugs = array_column( $font_sizes, 'slug' );
        
        foreach ( $required_sizes as $required_size ) {
            $this->assertContains( $required_size, $size_slugs, 
                "Font sizes must contain '{$required_size}' size" );
        }
    }
    
    /**
     * Test that theme.json has required spacing scale
     * 
     * @test
     */
    public function test_theme_json_spacing() {
        $required_spacing_properties = [
            'settings.spacing.spacingSizes'
        ];
        
        $this->assertThemeJsonHasProperties( $required_spacing_properties );
        
        $theme_json_data = $this->getThemeJsonData();
        $spacing_sizes = $theme_json_data['settings']['spacing']['spacingSizes'];
        
        $this->assertIsArray( $spacing_sizes, 'Spacing sizes must be an array' );
        $this->assertNotEmpty( $spacing_sizes, 'Spacing sizes must not be empty' );
        
        foreach ( $spacing_sizes as $spacing ) {
            $this->assertArrayHasKey( 'name', $spacing, 'Each spacing must have a name' );
            $this->assertArrayHasKey( 'slug', $spacing, 'Each spacing must have a slug' );
            $this->assertArrayHasKey( 'size', $spacing, 'Each spacing must have a size value' );
        }
    }
    
    /**
     * Test that theme.json has proper layout settings
     * 
     * @test
     */
    public function test_theme_json_layout() {
        $required_layout_properties = [
            'settings.layout.contentSize',
            'settings.layout.wideSize'
        ];
        
        $this->assertThemeJsonHasProperties( $required_layout_properties );
        
        $theme_json_data = $this->getThemeJsonData();
        
        $content_size = $theme_json_data['settings']['layout']['contentSize'];
        $wide_size = $theme_json_data['settings']['layout']['wideSize'];
        
        $this->assertMatchesRegularExpression( '/^\d+(\.\d+)?(px|rem|em|%)$/', $content_size, 
            'contentSize must be a valid CSS unit' );
        $this->assertMatchesRegularExpression( '/^\d+(\.\d+)?(px|rem|em|%)$/', $wide_size, 
            'wideSize must be a valid CSS unit' );
    }
    
    /**
     * Test that theme.json has proper style definitions
     * 
     * @test
     */
    public function test_theme_json_styles() {
        $theme_json_data = $this->getThemeJsonData();
        
        $this->assertArrayHasKey( 'styles', $theme_json_data, 'theme.json must have styles section' );
        
        $styles = $theme_json_data['styles'];
        $this->assertIsArray( $styles, 'Styles must be an array' );
        
        // Test global styles exist
        $this->assertArrayHasKey( 'typography', $styles, 'Global typography styles must be defined' );
        $this->assertArrayHasKey( 'color', $styles, 'Global color styles must be defined' );
    }
    
    /**
     * Test that theme.json enables required theme support features
     * 
     * @test
     */
    public function test_theme_json_theme_support() {
        $theme_json_data = $this->getThemeJsonData();
        
        // Check appearance tools are enabled
        if ( isset( $theme_json_data['settings']['appearanceTools'] ) ) {
            $this->assertTrue( $theme_json_data['settings']['appearanceTools'], 
                'appearanceTools should be enabled for better editor experience' );
        }
        
        // Check specific feature enablement
        $expected_features = [
            'settings.color.custom' => true,
            'settings.color.customGradient' => true,
            'settings.typography.customFontSize' => true,
            'settings.spacing.padding' => true,
            'settings.spacing.margin' => true
        ];
        
        foreach ( $expected_features as $feature_path => $expected_value ) {
            $this->assertArrayHasNestedKey( $theme_json_data, $feature_path, 
                "Feature {$feature_path} should be configured" );
        }
    }
}