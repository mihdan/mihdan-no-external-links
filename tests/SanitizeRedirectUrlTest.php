<?php
/**
 * Tests for Frontend::sanitize_redirect_url().
 *
 * This is the gate that keeps non-http(s) values (raw HTML, javascript:,
 * data:, schemeless input) from ever reaching the click log and the admin
 * Logs screen. The scheme check runs on real parse_url(); esc_url_raw() is
 * WordPress core and is aliased to identity here only so the class can run in
 * isolation — the security boundary under test is the scheme rejection.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mihdan\No_External_Links\Frontend;
use PHPUnit\Framework\TestCase;

final class SanitizeRedirectUrlTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		// Delegate to the real parser so the scheme check is genuinely exercised.
		Functions\when( 'wp_parse_url' )->alias(
			static function ( $url, $component = -1 ) {
				return -1 === $component ? parse_url( (string) $url ) : parse_url( (string) $url, $component );
			}
		);

		// esc_url_raw is WP core; only the scheme gate is under test here.
		Functions\when( 'esc_url_raw' )->alias(
			static function ( $url, $protocols = null ) {
				return (string) $url;
			}
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @dataProvider malicious_provider
	 */
	public function test_rejects_non_http_values( string $input ): void {
		$this->assertSame( '', Frontend::sanitize_redirect_url( $input ) );
	}

	public function malicious_provider(): array {
		return [
			'raw script tag'      => [ '<script>alert("poc-stored-xss")</script>' ],
			'img onerror'         => [ '<img src=x onerror=alert("WFPROOF")>' ],
			'javascript scheme'   => [ 'javascript:alert(1)' ],
			'data uri'            => [ 'data:text/html,<script>alert(1)</script>' ],
			'protocol relative'   => [ '//evil.example/path' ],
			'schemeless path'     => [ '/goto/whatever' ],
			'ftp scheme'          => [ 'ftp://host/file' ],
			'empty string'        => [ '' ],
			'decoded poc payload' => [ base64_decode( 'PHNjcmlwdD5hbGVydCgicG9jLXN0b3JlZC14c3MiKTwvc2NyaXB0Pg==' ) ],
		];
	}

	/**
	 * @dataProvider valid_provider
	 */
	public function test_accepts_http_urls( string $input ): void {
		$this->assertSame( $input, Frontend::sanitize_redirect_url( $input ) );
	}

	public function valid_provider(): array {
		return [
			'https'            => [ 'https://wordpress.org/plugins/?a=1&b=2' ],
			'http'             => [ 'http://example.com/' ],
			'uppercase scheme' => [ 'HTTP://example.com/' ],
			'encoded cyrillic' => [ 'https://ru.wikipedia.org/wiki/%D0%A2%D0%B5%D1%81%D1%82' ],
		];
	}
}
