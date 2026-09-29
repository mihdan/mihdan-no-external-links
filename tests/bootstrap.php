<?php
/**
 * PHPUnit bootstrap for isolated (Brain Monkey) tests.
 *
 * These tests do not load WordPress. WP functions are mocked per test with
 * Brain Monkey, and the plugin classes under test are required directly.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// The plugin has no autoloader; classes are loaded via require_once at runtime.
require_once dirname( __DIR__ ) . '/public/Frontend.php';

// LogTable extends the global WP_List_Table. It is never instantiated by WP in
// these tests, so provide a minimal stand-in before the class is loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	class WP_List_Table {
		public function __construct( $args = [] ) {}

		public function row_actions( $actions, $always_visible = false ): string {
			return '';
		}
	}
}

// Minimal stand-in for the WP_Post class used in type checks.
if ( ! class_exists( 'WP_Post' ) ) {
	class WP_Post {
		public $ID = 0;

		public function __construct( int $id = 0 ) {
			$this->ID = $id;
		}
	}
}

require_once dirname( __DIR__ ) . '/admin/LogTable.php';

// Shared base class for Frontend tests (not a *Test.php file, so require it here).
require_once __DIR__ . '/FrontendTestCase.php';
