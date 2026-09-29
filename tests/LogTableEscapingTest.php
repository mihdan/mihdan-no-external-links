<?php
/**
 * Tests that LogTable escapes stored values before rendering them.
 *
 * Even if a payload somehow reached the log table (e.g. a row stored by an
 * older version), the admin Logs screen must never render it unescaped.
 * esc_html() is aliased to a faithful htmlspecialchars() stand-in so the
 * escaping is really performed, not merely asserted against a mock.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mihdan\No_External_Links\Admin\LogTable;
use PHPUnit\Framework\TestCase;

final class LogTableEscapingTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'wp_create_nonce' )->justReturn( 'nonce' );
		Functions\when( 'absint' )->alias( static fn( $v ) => (int) $v );
		Functions\when( 'esc_html' )->alias(
			static fn( $text ) => htmlspecialchars( (string) $text, ENT_QUOTES )
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	private function make_table(): LogTable {
		return new LogTable( 'mihdan-no-external-links', 'mihdan_noexternallinks_' );
	}

	public function test_title_column_escapes_payload(): void {
		$item   = [ 'id' => 5, 'url' => '<script>alert("xss")</script>' ];
		$output = $this->make_table()->column_default( $item, 'title' );

		$this->assertStringNotContainsString( '<script>', $output );
		$this->assertStringContainsString( '&lt;script&gt;', $output );
	}

	/**
	 * @dataProvider column_provider
	 */
	public function test_other_columns_escape_payload( string $column, string $key ): void {
		$payload = '<img src=x onerror=alert(1)>';
		$item    = [ 'id' => 5, $key => $payload ];
		$output  = (string) $this->make_table()->column_default( $item, $column );

		$this->assertStringNotContainsString( '<img', $output );
		$this->assertStringContainsString( '&lt;img', $output );
	}

	public function column_provider(): array {
		return [
			'referring_url' => [ 'referring_url', 'referring_url' ],
			'user_agent'    => [ 'user_agent', 'user_agent' ],
			'ip_address'    => [ 'ip_address', 'ip_address' ],
			'datetime'      => [ 'datetime', 'date' ],
		];
	}
}
