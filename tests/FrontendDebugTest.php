<?php
/**
 * Tests for Frontend debug helpers.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;

final class FrontendDebugTest extends FrontendTestCase {

	public function test_debug_info_returns_empty_when_debug_off(): void {
		$frontend = $this->make_frontend( [ 'debug_mode' => false ] );

		$this->assertSame( '', $frontend->debug_info( 'hello', 1 ) );
		$this->assertSame( [], $this->get_private( $frontend, 'debug_log' ) );
	}

	public function test_debug_info_logs_when_debug_on(): void {
		$frontend = $this->make_frontend( [ 'debug_mode' => true ] );

		$this->assertSame( '', $frontend->debug_info( 'first' ) );
		$this->assertSame( [ 'first' ], $this->get_private( $frontend, 'debug_log' ) );
	}

	public function test_debug_info_returns_wrapped_when_requested(): void {
		$frontend = $this->make_frontend( [ 'debug_mode' => true ] );

		$out = $frontend->debug_info( 'payload', 1 );

		$this->assertStringContainsString( 'payload', $out );
		$this->assertStringContainsString( 'wp-noexternallinks debug', $out );
	}

	public function test_output_debug_escapes_and_joins_log(): void {
		Functions\when( 'esc_html' )->alias(
			static fn( $text ) => htmlspecialchars( (string) $text, ENT_QUOTES )
		);

		$frontend = $this->make_frontend();
		$this->set_private( $frontend, 'debug_log', [ 'a', '<b>' ] );

		$this->expectOutputRegex( '/wp-noexternallinks debug/' );
		$frontend->output_debug();
	}

	public function test_output_debug_escapes_html(): void {
		Functions\when( 'esc_html' )->alias(
			static fn( $text ) => htmlspecialchars( (string) $text, ENT_QUOTES )
		);

		$frontend = $this->make_frontend();
		$this->set_private( $frontend, 'debug_log', [ '<script>x</script>' ] );

		ob_start();
		$frontend->output_debug();
		$out = (string) ob_get_clean();

		$this->assertStringNotContainsString( '<script>', $out );
		$this->assertStringContainsString( '&lt;script&gt;', $out );
	}
}
