<?php
/**
 * Tests for Frontend content pipeline: parser, filter, check_post, ob_filter.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;
use Mockery;
use WP_Post;

final class FrontendParserTest extends FrontendTestCase {

	private function set_rewrite( bool $permalinks = true ): void {
		global $wp_rewrite;
		$wp_rewrite = Mockery::mock();
		$wp_rewrite->shouldReceive( 'using_permalinks' )->andReturn( $permalinks );
	}

	private function anchor_matches( string $url, string $text = 'link', string $before = '', string $after = '' ): array {
		$anchor = '<a ' . $before . 'href="' . $url . '"' . $after . '>' . $text . '</a>';

		return [ $anchor, $before, $url, $after, $text ];
	}

	public function test_parser_strips_rel_exclude_and_records_it(): void {
		$frontend = $this->make_frontend();
		$matches  = $this->anchor_matches( 'https://ext.com', 'link', '', ' rel="exclude"' );

		$out = $frontend->parser( $matches );

		$this->assertStringNotContainsString( 'rel="exclude"', $out );
		$this->assertContains( 'https://ext.com', $this->get_private( $frontend, 'exclusion_list' ) );
	}

	public function test_parser_skips_excluded_url(): void {
		$frontend = $this->make_frontend( [], [], [ 'https://ext.com' ] );
		$matches  = $this->anchor_matches( 'https://ext.com/page' );

		$this->assertSame( $matches[0], $frontend->parser( $matches ) );
	}

	public function test_parser_masks_non_excluded_url(): void {
		$this->set_rewrite();
		$frontend = $this->make_frontend( [ 'nofollow' => false, 'target_blank' => false ] );
		$matches  = $this->anchor_matches( 'https://ext.com' );

		$out = $frontend->parser( $matches );

		$this->assertStringContainsString( '/goto/https://ext.com', $out );
	}

	public function test_parser_inclusion_list_no_match_returns_unchanged(): void {
		$frontend = $this->make_frontend( [ 'inclusion_list' => "https://allowed.com" ] );
		$matches  = $this->anchor_matches( 'https://other.com' );

		$this->assertSame( $matches[0], $frontend->parser( $matches ) );
	}

	public function test_filter_returns_content_unchanged_in_admin(): void {
		Functions\when( 'is_admin' )->justReturn( true );

		$frontend = $this->make_frontend();
		$content  = '<a href="https://ext.com">x</a>';

		$this->assertSame( $content, $frontend->filter( $content ) );
	}

	public function test_filter_masks_links_in_content(): void {
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'is_feed' )->justReturn( false );
		$this->set_rewrite();

		$frontend = $this->make_frontend( [ 'nofollow' => false, 'target_blank' => false ] );
		$content  = 'before <a href="https://ext.com">x</a> after';

		$out = $frontend->filter( $content );

		$this->assertStringContainsString( '/goto/https://ext.com', $out );
		$this->assertStringContainsString( 'before ', $out );
	}

	public function test_check_post_returns_content_when_not_wp_post(): void {
		global $post;
		$post = null;

		$frontend = $this->make_frontend();

		$this->assertSame( 'body', $frontend->check_post( 'body' ) );
	}

	public function test_check_post_skips_when_meta_disabled(): void {
		global $post;
		$post = new WP_Post( 7 );

		Functions\when( 'get_post_meta' )->justReturn( 'disabled' );

		$frontend = $this->make_frontend();

		$this->assertSame( 'body', $frontend->check_post( 'body' ) );
	}

	public function test_check_post_applies_filter(): void {
		global $post;
		$post = new WP_Post( 7 );

		Functions\when( 'get_post_meta' )->justReturn( '' );
		Functions\when( 'is_admin' )->justReturn( false );
		Functions\when( 'is_feed' )->justReturn( false );
		$this->set_rewrite();

		$frontend = $this->make_frontend( [ 'nofollow' => false, 'target_blank' => false ] );
		$content  = '<a href="https://ext.com">x</a>';

		$out = $frontend->check_post( $content );

		$this->assertStringContainsString( '/goto/https://ext.com', $out );
	}

	public function test_ob_filter_returns_empty_content_untouched(): void {
		$frontend = $this->make_frontend();

		$this->assertSame( '', $frontend->ob_filter( '' ) );
	}

	public function test_ob_filter_injects_buffer_into_body(): void {
		global $post;
		$post = null;

		$frontend = $this->make_frontend( [], [ 'buffer' => '<!--buf-->' ] );
		$html     = '<html><body class="x">content</body></html>';

		$out = $frontend->ob_filter( $html );

		$this->assertStringContainsString( '<body class="x"><!--buf-->', $out );
	}
}
