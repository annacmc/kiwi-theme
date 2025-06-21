<?php
/**
 * Simple Bootstrap file for PHPUnit tests
 * 
 * This bootstrap provides a minimal environment for testing
 * theme structure and functionality without requiring WordPress installation.
 */

// Define WordPress constants if not already defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( dirname( dirname( __DIR__ ) ) ) . '/' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// Define theme directory
define( 'KIWI_THEME_DIR', dirname( dirname( __DIR__ ) ) );

// Mock essential WordPress functions for testing
if ( ! function_exists( 'get_template_directory' ) ) {
	function get_template_directory() {
		return KIWI_THEME_DIR;
	}
}

if ( ! function_exists( 'get_template_directory_uri' ) ) {
	function get_template_directory_uri() {
		return 'http://localhost/wp-content/themes/kiwi-theme';
	}
}

if ( ! function_exists( 'switch_theme' ) ) {
	function switch_theme( $theme ) {
		return true;
	}
}

if ( ! function_exists( 'current_theme_supports' ) ) {
	function current_theme_supports( $feature ) {
		$supported_features = array(
			'post-thumbnails',
			'automatic-feed-links', 
			'title-tag',
			'html5',
			'responsive-embeds',
			'editor-styles',
			'wp-block-styles'
		);
		return in_array( $feature, $supported_features, true );
	}
}

if ( ! function_exists( 'wp_get_theme' ) ) {
	function wp_get_theme() {
		$theme = new stdClass();
		$theme->version = '1.0.0';
		return $theme;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $hook, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'add_theme_support' ) ) {
	function add_theme_support( $feature, $args = null ) {
		return true;
	}
}

if ( ! function_exists( 'load_theme_textdomain' ) ) {
	function load_theme_textdomain( $domain, $path = false ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		return true;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		return true;
	}
}

if ( ! function_exists( 'get_stylesheet_uri' ) ) {
	function get_stylesheet_uri() {
		return get_template_directory_uri() . '/style.css';
	}
}

// Mock WP_UnitTestCase for theme testing
if ( ! class_exists( 'WP_UnitTestCase' ) ) {
	class WP_UnitTestCase extends PHPUnit\Framework\TestCase {
		
		public function setUp(): void {
			parent::setUp();
		}
		
		public function tearDown(): void {
			parent::tearDown();
		}
	}
}

// Load theme test utilities
require_once __DIR__ . '/theme-test-utils.php';