<?php
/**
 * Tests for Frontend::encode_link() / decode_link().
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Mockery;

final class FrontendEncodingTest extends FrontendTestCase {

	public function test_encode_none_returns_url_unchanged(): void {
		$frontend = $this->make_frontend( [ 'link_encoding' => 'none' ] );

		$this->assertSame( 'https://example.com/', $frontend->encode_link( 'https://example.com/' ) );
	}

	public function test_base64_round_trip(): void {
		$frontend = $this->make_frontend( [ 'link_encoding' => 'base64' ] );
		$url      = 'https://example.com/path?a=1&b=2';

		$encoded = $frontend->encode_link( $url );

		$this->assertSame( base64_encode( $url ), $encoded );
		$this->assertSame( $url, $frontend->decode_link( $encoded ) );
	}

	public function test_decode_base64_of_payload(): void {
		$frontend = $this->make_frontend( [ 'link_encoding' => 'base64' ] );
		$b64      = 'PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==';

		$this->assertSame( '<script>alert(1)</script>', $frontend->decode_link( $b64 ) );
	}

	public function test_aes256_openssl_round_trip(): void {
		if ( ! function_exists( 'openssl_encrypt' ) ) {
			$this->markTestSkipped( 'openssl extension not available' );
		}

		$key      = base64_encode( random_bytes( 32 ) );
		$frontend = $this->make_frontend(
			[
				'link_encoding'  => 'aes256',
				'encryption'     => 'openssl',
				'encryption_key' => $key,
			]
		);

		$url     = 'https://example.com/secret?x=1';
		$encoded = $frontend->encode_link( $url );

		$this->assertStringContainsString( ':', $encoded );
		$this->assertSame( $url, $frontend->decode_link( $encoded ) );
	}

	public function test_numbers_encode_returns_existing_id(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->last_error = '';
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'SQL' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( '42' );

		$frontend = $this->make_frontend( [ 'link_encoding' => 'numbers' ] );

		$this->assertSame( '42', $frontend->encode_link( 'https://example.com/' ) );
	}

	public function test_numbers_decode_returns_url_from_db(): void {
		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'SQL' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( 'https://example.com/from-db' );

		$frontend = $this->make_frontend( [ 'link_encoding' => 'numbers' ] );

		$this->assertSame( 'https://example.com/from-db', $frontend->decode_link( '42' ) );
	}
}
