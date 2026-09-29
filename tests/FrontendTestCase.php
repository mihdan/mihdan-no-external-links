<?php
/**
 * Shared base class for isolated Frontend tests.
 *
 * Frontend's constructor registers hooks and reads superglobals, so tests build
 * an instance without invoking the constructor and inject the private state they
 * need via reflection. WordPress functions are mocked per test with Brain Monkey.
 *
 * @package mihdan-no-external-links
 */

declare( strict_types=1 );

namespace Mihdan\No_External_Links\Tests;

use Brain\Monkey;
use Mihdan\No_External_Links\Frontend;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;

abstract class FrontendTestCase extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown(): void {
		Monkey\tearDown();
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * Default plugin options as an object, mirroring Main::default_options().
	 */
	protected function default_options( array $overrides = [] ): stdClass {
		$defaults = [
			'masking_type'          => '302',
			'redirect_time'         => 3,
			'redirect_page'         => 0,
			'nofollow'              => true,
			'target_blank'          => true,
			'noindex_tag'           => false,
			'noindex_comment'       => false,
			'seo_hide'              => false,
			'seo_hide_mode'         => 'specific',
			'seo_hide_include_list' => '',
			'seo_hide_exclude_list' => '',
			'separator'             => 'goto',
			'link_encoding'         => 'none',
			'encryption'            => 'openssl',
			'encryption_key'        => base64_encode( str_repeat( 'k', 32 ) ),
			'link_shortening'       => 'none',
			'logging'               => true,
			'remove_all_links'      => false,
			'links_to_text'         => false,
			'debug_mode'            => false,
			'anonymize_links'       => false,
			'anonymous_link_provider' => 'https://href.li/?',
			'bot_targeting'         => 'all',
			'bots_selector'         => [],
			'check_referrer'        => true,
			'inclusion_list'        => '',
			'exclusion_list'        => '',
			'skip_follow'           => false,
			'mask_rss'              => true,
			'mask_rss_comments'     => true,
		];

		return (object) array_merge( $defaults, $overrides );
	}

	/**
	 * Default data layer, mirroring Frontend::initiate().
	 */
	protected function default_data( array $overrides = [] ): stdClass {
		$defaults = [
			'site'            => 'https://example.test',
			'before'          => '',
			'after'           => '',
			'buffer'          => '',
			'client_ip'       => '203.0.113.5',
			'referring_url'   => 'https://example.test/post',
			'user_agent'      => 'Mozilla/5.0',
			'user_agent_name' => 'browser',
		];

		return (object) array_merge( $defaults, $overrides );
	}

	/**
	 * Build a Frontend instance without running the constructor.
	 */
	protected function make_frontend( array $options = [], array $data = [], array $exclusion_list = [] ): Frontend {
		$frontend = ( new ReflectionClass( Frontend::class ) )->newInstanceWithoutConstructor();

		$this->set_private( $frontend, 'plugin_name', 'mihdan-no-external-links' );
		$this->set_private( $frontend, 'options', $this->default_options( $options ) );
		$this->set_private( $frontend, 'data', $this->default_data( $data ) );
		$this->set_private( $frontend, 'exclusion_list', $exclusion_list );
		$this->set_private( $frontend, 'debug_log', [] );

		return $frontend;
	}

	protected function set_private( object $object, string $property, $value ): void {
		$ref = $this->property_ref( $object, $property );
		$ref->setValue( $object, $value );
	}

	protected function get_private( object $object, string $property ) {
		return $this->property_ref( $object, $property )->getValue( $object );
	}

	/**
	 * Resolve a (possibly inherited private) property to an accessible reflection.
	 */
	private function property_ref( object $object, string $property ): ReflectionProperty {
		$class = new ReflectionClass( $object );

		while ( $class && ! $class->hasProperty( $property ) ) {
			$class = $class->getParentClass();
		}

		$ref = $class->getProperty( $property );
		$ref->setAccessible( true );

		return $ref;
	}

	/**
	 * Invoke a private/protected method.
	 */
	protected function call_private( object $object, string $method, array $args = [] ) {
		$ref = new ReflectionMethod( $object, $method );
		$ref->setAccessible( true );

		return $ref->invokeArgs( $object, $args );
	}
}
