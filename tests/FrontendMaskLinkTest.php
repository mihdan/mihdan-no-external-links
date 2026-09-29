<?php
/**
 * Tests for Frontend::mask_link() branches.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey\Functions;
use Mockery;

final class FrontendMaskLinkTest extends FrontendTestCase {

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'esc_attr' )->alias(
			static fn( $text ) => htmlspecialchars( (string) $text, ENT_QUOTES )
		);
	}

	/**
	 * Set the global $wp_rewrite with a configurable using_permalinks().
	 */
	private function set_rewrite( bool $permalinks ): void {
		global $wp_rewrite;
		$wp_rewrite = Mockery::mock();
		$wp_rewrite->shouldReceive( 'using_permalinks' )->andReturn( $permalinks );
	}

	/**
	 * Build the regex-match array mask_link() expects.
	 */
	private function link_matches( string $url, string $text = 'link', string $before = '', string $after = '' ): array {
		$anchor = '<a ' . $before . 'href="' . $url . '"' . $after . '>' . $text . '</a>';

		return [ $anchor, $before, $url, $after, $text ];
	}

	public function test_bot_targeting_mismatch_returns_anchor_unchanged(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend(
			[ 'bot_targeting' => 'selected', 'bots_selector' => [ 'googlebot' ] ],
			[ 'user_agent_name' => 'browser' ]
		);

		$matches = $this->link_matches( 'https://ext.com' );

		$this->assertSame( $matches[0], $frontend->mask_link( $matches ) );
	}

	public function test_skip_follow_keeps_follow_links(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'skip_follow' => true ] );

		$matches = $this->link_matches( 'https://ext.com', 'link', '', ' rel="follow"' );

		$this->assertSame( $matches[0], $frontend->mask_link( $matches ) );
	}

	public function test_masking_with_permalinks_builds_goto_url(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'nofollow' => false, 'target_blank' => false ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com' ) );

		$this->assertStringContainsString( 'href="https://example.test/goto/https://ext.com"', $out );
	}

	public function test_masking_without_permalinks_uses_query_and_encodes(): void {
		$this->set_rewrite( false );
		$frontend = $this->make_frontend( [ 'nofollow' => false, 'target_blank' => false ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com/a b' ) );

		$this->assertStringContainsString( 'https://example.test/?goto=', $out );
		$this->assertStringContainsString( rawurlencode( 'https://ext.com/a b' ), $out );
	}

	public function test_nofollow_and_target_blank_attributes(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'nofollow' => true, 'target_blank' => true ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com' ) );

		$this->assertStringContainsString( 'rel="nofollow"', $out );
		$this->assertStringContainsString( 'target="_blank"', $out );
	}

	public function test_remove_all_links_returns_span(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'remove_all_links' => true ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com', 'anchor' ) );

		$this->assertSame( '<span class="waslinkname">anchor</span>', $out );
	}

	public function test_links_to_text_returns_name_and_url(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'links_to_text' => true ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com', 'anchor' ) );

		$this->assertStringContainsString( 'waslinkname', $out );
		$this->assertStringContainsString( 'waslinkurl', $out );
	}

	public function test_noindex_tag_and_comment_wrap_anchor(): void {
		$this->set_rewrite( true );
		$frontend = $this->make_frontend( [ 'noindex_tag' => true, 'noindex_comment' => true ] );

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com' ) );

		$this->assertStringContainsString( '<noindex>', $out );
		$this->assertStringContainsString( '<!--noindex-->', $out );
	}

	public function test_seo_hide_specific_returns_span_with_encoded_link(): void {
		$this->set_rewrite( true );
		Functions\when( 'wp_parse_url' )->alias(
			static fn( $url, $component = -1 ) => parse_url( (string) $url, $component )
		);

		$frontend = $this->make_frontend(
			[
				'seo_hide'              => true,
				'seo_hide_mode'         => 'specific',
				'seo_hide_include_list' => 'ext.com',
			]
		);

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com/page', 'anchor' ) );

		$this->assertStringContainsString( 'waslinkname', $out );
		$this->assertStringContainsString( 'data-link="' . base64_encode( 'https://ext.com/page' ) . '"', $out );
	}

	public function test_link_shortening_uses_cached_mask(): void {
		$this->set_rewrite( true );

		global $wpdb;
		$wpdb = Mockery::mock();
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )->andReturn( 'SQL' );
		$wpdb->shouldReceive( 'get_var' )->once()->andReturn( 'https://adf.ly/abc' );

		Functions\when( 'get_option' )->justReturn( 'UTF-8' );

		$frontend = $this->make_frontend(
			[ 'link_shortening' => 'adfly', 'nofollow' => false, 'target_blank' => false ]
		);

		$out = $frontend->mask_link( $this->link_matches( 'https://ext.com' ) );

		$this->assertStringContainsString( 'href="https://adf.ly/abc"', $out );
	}
}
