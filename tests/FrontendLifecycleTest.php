<?php
/**
 * Tests for Frontend lifecycle methods: setup_hooks, fullpage_filter,
 * enqueue_scripts, initiate.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;
use Mihdan\No_External_Links\Frontend;
use Mockery;

final class FrontendLifecycleTest extends FrontendTestCase {

	public function test_constructor_wires_hooks_and_initiates(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\when( 'get_option' )->justReturn( 'https://example.test' );

		$frontend = new Frontend( 'mihdan-no-external-links', '5.2.1', $this->default_options() );

		$this->assertInstanceOf( Frontend::class, $frontend );
		$this->assertSame( 'https://example.test', $this->get_private( $frontend, 'data' )->site );
	}

	public function test_setup_hooks_registers_enqueue_action(): void {
		Functions\expect( 'add_action' )
			->once()
			->with( 'wp_enqueue_scripts', Mockery::type( 'array' ) );

		$this->make_frontend()->setup_hooks();
		$this->addToAssertionCount( 1 );
	}

	public function test_fullpage_filter_starts_output_buffering(): void {
		$before = ob_get_level();

		$this->make_frontend()->fullpage_filter();

		$this->assertSame( $before + 1, ob_get_level() );

		ob_end_clean(); // Restore the buffer level.
	}

	public function test_enqueue_scripts_does_nothing_when_seo_hide_off(): void {
		$frontend = $this->make_frontend( [ 'seo_hide' => false ] );
		$frontend->enqueue_scripts();

		$this->assertTrue( true ); // No enqueue functions are called.
	}

	public function test_enqueue_scripts_enqueues_assets_when_seo_hide_on(): void {
		$this->define_plugin_constants();

		Functions\expect( 'wp_enqueue_style' )->once();
		Functions\expect( 'wp_enqueue_script' )->once();

		$frontend = $this->make_frontend( [ 'seo_hide' => true ] );
		$frontend->enqueue_scripts();
		$this->addToAssertionCount( 1 );
	}

	public function test_initiate_populates_data_layer(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\when( 'get_option' )->justReturn( 'https://example.test' );

		$_SERVER['REMOTE_ADDR']     = '203.0.113.9';
		$_SERVER['HTTP_REFERER']    = 'https://example.test/from';
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
		$_SERVER['REQUEST_URI']     = '/goto/abc';

		$frontend = $this->make_frontend();
		$frontend->initiate();

		$data = $this->get_private( $frontend, 'data' );

		$this->assertSame( '203.0.113.9', $data->client_ip );
		$this->assertSame( 'https://example.test', $data->site );
		$this->assertSame( 'googlebot', $data->user_agent_name );

		foreach ( [ 'REMOTE_ADDR', 'HTTP_REFERER', 'HTTP_USER_AGENT', 'REQUEST_URI' ] as $key ) {
			unset( $_SERVER[ $key ] );
		}
	}

	public function test_initiate_builds_exclusion_list(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'get_col' )->andReturn( [] );

		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'esc_url_raw' )->returnArg( 1 );
		Functions\when( 'get_option' )->justReturn( 'https://example.test' );

		$frontend = $this->make_frontend();
		$frontend->initiate();

		$exclusions = $this->get_private( $frontend, 'exclusion_list' );

		$this->assertContains( 'javascript', $exclusions );
		$this->assertContains( 'mailto', $exclusions );
		$this->assertContains( 'https://example.test', $exclusions );
	}

	private function define_plugin_constants(): void {
		$root = dirname( __DIR__ );

		if ( ! defined( 'MIHDAN_NO_EXTERNAL_LINKS_SLUG' ) ) {
			define( 'MIHDAN_NO_EXTERNAL_LINKS_SLUG', 'mihdan-no-external-links' );
		}
		if ( ! defined( 'MIHDAN_NO_EXTERNAL_LINKS_URL' ) ) {
			define( 'MIHDAN_NO_EXTERNAL_LINKS_URL', 'https://example.test/plugin' );
		}
		if ( ! defined( 'MIHDAN_NO_EXTERNAL_LINKS_VERSION' ) ) {
			define( 'MIHDAN_NO_EXTERNAL_LINKS_VERSION', 'test' );
		}
		if ( ! defined( 'MIHDAN_NO_EXTERNAL_LINKS_DIR' ) ) {
			define( 'MIHDAN_NO_EXTERNAL_LINKS_DIR', $root );
		}
	}
}
