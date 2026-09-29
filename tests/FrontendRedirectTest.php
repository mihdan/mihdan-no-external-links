<?php
/**
 * Tests for the redirect pipeline: check_redirect, redirect, add_log, shorten_link.
 *
 * redirect()/check_redirect() normally terminate the request (die/exit) and emit
 * headers, so they are exercised through a lightweight subclass double that
 * overrides the terminal collaborators and records what they were called with.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;
use Mihdan\No_External_Links\Frontend;
use Mockery;

final class FrontendRedirectTest extends FrontendTestCase {

	/**
	 * A Frontend double that skips the constructor and captures terminal calls.
	 */
	private function double(): Frontend {
		return new class() extends Frontend {
			public $logged;
			public $shown;
			public $decoded = 'https://ext.com/page';
			public $warned = false;

			// phpcs:ignore Squiz.Commenting.FunctionComment.Missing
			public function __construct() {}

			public function decode_link( $url ): string {
				return $this->decoded;
			}

			public function add_log( $url ): void {
				$this->logged = $url;
			}

			public function show_redirect_page( $url ): void {
				$this->shown = $url;
			}

			public function show_referrer_warning(): void {
				$this->warned = true;
			}
		};
	}

	private function init_double( Frontend $frontend, array $options = [], array $data = [] ): void {
		$this->set_private( $frontend, 'plugin_name', 'mihdan-no-external-links' );
		$this->set_private( $frontend, 'options', $this->default_options( $options ) );
		$this->set_private( $frontend, 'data', $this->default_data( $data ) );
		$this->set_private( $frontend, 'exclusion_list', [] );
		$this->set_private( $frontend, 'debug_log', [] );
	}

	private function set_rewrite( bool $permalinks = true ): void {
		global $wp_rewrite, $wp_query;
		$wp_rewrite = Mockery::mock();
		$wp_rewrite->shouldReceive( 'using_permalinks' )->andReturn( $permalinks );
		$wp_query          = new \stdClass();
		$wp_query->is_404  = false;
	}

	private function stub_url_functions(): void {
		Functions\when( 'wp_parse_url' )->alias(
			static fn( $url, $component = -1 ) => parse_url( (string) $url, $component )
		);
		Functions\when( 'esc_url_raw' )->alias( static fn( $url, $protocols = null ) => (string) $url );
		Functions\when( 'get_option' )->justReturn( 'UTF-8' );
	}

	public function test_add_log_skips_when_logging_disabled(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->shouldNotReceive( 'insert' );

		$frontend = $this->make_frontend( [ 'logging' => false ] );
		$frontend->add_log( 'https://ext.com' );

		$this->assertTrue( true ); // No insert expected.
	}

	public function test_add_log_inserts_when_logging_enabled(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'insert' )->once()->andReturn( 1 );

		Functions\when( 'current_time' )->justReturn( '2026-09-29 00:00:00' );

		$frontend = $this->make_frontend( [ 'logging' => true ] );
		$frontend->add_log( 'https://ext.com' );

		$this->assertTrue( true ); // Insert expectation verified on tearDown.
	}

	public function test_redirect_logs_and_shows_sanitized_url(): void {
		$this->set_rewrite();
		$this->stub_url_functions();

		$frontend          = $this->double();
		$this->init_double( $frontend, [ 'check_referrer' => false, 'anonymize_links' => false ] );
		$frontend->decoded = 'https://ext.com/page';

		$frontend->redirect( 'ignored-input' );

		$this->assertSame( 'https://ext.com/page', $frontend->logged );
		$this->assertSame( 'https://ext.com/page', $frontend->shown );
	}

	public function test_redirect_prepends_anonymizer(): void {
		$this->set_rewrite();
		$this->stub_url_functions();

		$frontend = $this->double();
		$this->init_double(
			$frontend,
			[
				'check_referrer'          => false,
				'anonymize_links'         => true,
				'anonymous_link_provider' => 'https://href.li/?',
			]
		);

		$frontend->redirect( 'x' );

		$this->assertSame( 'https://href.li/?https://ext.com/page', $frontend->shown );
	}

	public function test_redirect_warns_on_foreign_referrer(): void {
		$this->set_rewrite();
		$this->stub_url_functions();
		Functions\when( 'wp_get_referer' )->justReturn( 'https://evil.example/x' );

		$frontend = $this->double();
		$this->init_double( $frontend, [ 'check_referrer' => true ], [ 'site' => 'https://example.test' ] );

		$frontend->redirect( 'x' );

		$this->assertTrue( $frontend->warned );
	}

	public function test_redirect_does_not_warn_on_own_referrer(): void {
		$this->set_rewrite();
		$this->stub_url_functions();
		Functions\when( 'wp_get_referer' )->justReturn( 'https://example.test/post' );

		$frontend = $this->double();
		$this->init_double( $frontend, [ 'check_referrer' => true ], [ 'site' => 'https://example.test' ] );

		$frontend->redirect( 'x' );

		$this->assertFalse( $frontend->warned );
	}

	public function test_check_redirect_extracts_goto_from_path(): void {
		Functions\when( 'esc_url_raw' )->alias( static fn( $url, $protocols = null ) => (string) $url );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg( 1 );

		$_SERVER['REQUEST_URI'] = '/goto/aHR0cHM6Ly9leHQuY29t';
		unset( $_REQUEST['goto'] );

		$captured = null;
		$frontend = new class() extends Frontend {
			public $captured_goto;

			// phpcs:ignore Squiz.Commenting.FunctionComment.Missing
			public function __construct() {}

			public function redirect( $url ): void {
				$this->captured_goto = $url;
			}
		};
		$this->set_private( $frontend, 'options', $this->default_options( [ 'separator' => 'goto' ] ) );

		$frontend->check_redirect();

		$this->assertSame( 'aHR0cHM6Ly9leHQuY29t', $frontend->captured_goto );

		unset( $_SERVER['REQUEST_URI'] );
	}

	public function test_check_redirect_uses_request_param(): void {
		Functions\when( 'esc_url_raw' )->alias( static fn( $url, $protocols = null ) => (string) $url );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'wp_strip_all_tags' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias( static fn( $key ) => strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $key ) ) );

		unset( $_SERVER['REQUEST_URI'] );
		$_REQUEST['goto'] = 'abc123';

		$frontend = new class() extends Frontend {
			public $captured_goto;

			// phpcs:ignore Squiz.Commenting.FunctionComment.Missing
			public function __construct() {}

			public function redirect( $url ): void {
				$this->captured_goto = $url;
			}
		};
		$this->set_private( $frontend, 'options', $this->default_options( [ 'separator' => 'goto' ] ) );

		$frontend->check_redirect();

		$this->assertSame( 'abc123', $frontend->captured_goto );

		unset( $_REQUEST['goto'] );
	}

	public function test_shorten_link_returns_cached_mask(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'SQL' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( 'https://adf.ly/xyz' );

		Functions\when( 'get_option' )->justReturn( 'UTF-8' );

		$frontend = $this->make_frontend( [ 'link_shortening' => 'adfly' ] );

		$this->assertSame( 'https://adf.ly/xyz', $frontend->shorten_link( 'https://ext.com' ) );
	}

	public function test_shorten_link_unknown_shortener_returns_url(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';

		Functions\when( 'get_option' )->justReturn( 'UTF-8' );

		$frontend = $this->make_frontend( [ 'link_shortening' => 'unknown' ] );

		$this->assertSame( 'https://ext.com', $frontend->shorten_link( 'https://ext.com' ) );
	}
}
