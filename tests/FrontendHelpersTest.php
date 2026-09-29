<?php
/**
 * Tests for Frontend private helpers: textarea_to_array, get_domain_from_url.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;

final class FrontendHelpersTest extends FrontendTestCase {

	public function test_textarea_to_array_splits_and_trims(): void {
		$frontend = $this->make_frontend();

		$result = $this->call_private(
			$frontend,
			'textarea_to_array',
			[ "  a.com \n b.com \n\tc.com " ]
		);

		$this->assertSame( [ 'a.com', 'b.com', 'c.com' ], $result );
	}

	public function test_textarea_to_array_empty_string(): void {
		$frontend = $this->make_frontend();

		$this->assertSame( [ '' ], $this->call_private( $frontend, 'textarea_to_array', [ '' ] ) );
	}

	public function test_get_domain_from_url_returns_host(): void {
		Functions\when( 'wp_parse_url' )->alias(
			static fn( $url, $component = -1 ) => parse_url( (string) $url, $component )
		);

		$frontend = $this->make_frontend();

		$this->assertSame(
			'sub.example.com',
			$this->call_private( $frontend, 'get_domain_from_url', [ 'https://sub.example.com/path?q=1' ] )
		);
	}

	public function test_get_domain_from_url_without_host(): void {
		Functions\when( 'wp_parse_url' )->alias(
			static fn( $url, $component = -1 ) => parse_url( (string) $url, $component )
		);

		$frontend = $this->make_frontend();

		$this->assertSame( '', $this->call_private( $frontend, 'get_domain_from_url', [ 'not-a-url' ] ) );
	}
}
