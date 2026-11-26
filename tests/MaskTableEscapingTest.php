<?php
/**
 * Tests that MaskTable escapes stored values before rendering them.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Mihdan\No_External_Links\Admin\MaskTable;
use PHPUnit\Framework\TestCase;

final class MaskTableEscapingTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();

		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'wp_create_nonce' )->justReturn( 'nonce' );
		Functions\when( 'absint' )->alias( static fn( $v ) => (int) $v );
		Functions\when( 'esc_attr' )->returnArg( 1 );
		Functions\when( 'esc_html' )->alias(
			static fn( $text ) => htmlspecialchars( (string) $text, ENT_QUOTES )
		);
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * @dataProvider column_provider
	 */
	public function test_columns_escape_payload( string $column, string $key ): void {
		$item   = [ 'id' => 5, $key => 'http://evil.example/?<img src=x onerror=alert(1)>' ];
		$output = ( new MaskTable( 'mihdan-no-external-links', 'mihdan_noexternallinks_' ) )->column_default( $item, $column );

		$this->assertStringNotContainsString( '<img', $output );
		$this->assertStringContainsString( '&lt;img', $output );
	}

	public function column_provider(): array {
		return [
			'title' => [ 'title', 'url' ],
			'mask'  => [ 'mask', 'mask' ],
		];
	}
}
