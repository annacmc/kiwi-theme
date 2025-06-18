<?php
/**
 * PHPUnit bootstrap for Kiwi Theme testing
 * 
 * Sets up WordPress testing environment for theme-specific tests
 */

// Define test environment constants
define( 'KIWI_THEME_TESTS', true );
define( 'WP_USE_THEMES', true );

// WordPress test suite path (adjust as needed for your environment)
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit( 1 );
}

// Give access to tests_add_filter() function
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the theme being tested
 */
function _manually_load_theme() {
    switch_theme( 'kiwi-theme' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_theme' );

// Start up the WP testing environment
require $_tests_dir . '/includes/bootstrap.php';

// Include theme-specific test utilities
require_once __DIR__ . '/theme-test-utils.php';