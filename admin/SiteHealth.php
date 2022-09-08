<?php
/**
 * Class that add Site Health tests.
 *
 * @since   4.5.1
 * @package mihdan-no-external-links
 * @author  mihdan
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

namespace Mihdan\No_External_Links\Admin;

use const Mihdan\No_External_Links\MIHDAN_NO_EXTERNAL_LINKS_SLUG;

/**
 * Class SiteHealth.
 */
class SiteHealth {

	/**
	 * Plugin name.
	 *
	 * @var string
	 */
	private string $plugin_name;

	/**
	 * Site_Health constructor.
	 *
	 * @param string $plugin_name Plugin name.
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;

		$this->init_hooks();
	}

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks(): void {
		add_filter( 'site_status_tests', [ $this, 'add_test' ] );
	}

	/**
	 * Add test.
	 *
	 * @param array $tests Tests.
	 *
	 * @return array
	 */
	public function add_test( $tests ): array {

		$tests['direct'][ MIHDAN_NO_EXTERNAL_LINKS_SLUG ] = [
			'label' => __( 'Output Buffering', $this->plugin_name ),
			'test'  => [ $this, 'check_buffering' ],
		];

		return $tests;
	}

	/**
	 * Custom tests.
	 *
	 * @return array
	 */
	public function check_buffering(): array {
		$output_buffer = (bool) ini_get( 'output_buffering' );

		// phpcs:disable WordPress.WP.I18n.NoHtmlWrappedStrings

		$result = [
			'label'       => __( 'Output Buffering is enabled', $this->plugin_name ),
			'status'      => 'good',
			'badge'       => [
				'label' => __( 'Performance' ),
				'color' => 'blue',
			],
			'description' => __( '<p>Output Buffering is enabled. <em>Mask All Links</em> will work.</p>', $this->plugin_name ),
		];

		if ( false === $output_buffer ) {
			$result = [
				'label'       => __( 'Output Buffering is disabled', $this->plugin_name ),
				'status'      => 'critical',
				'badge'       => [
					'label' => __( 'Performance' ),
					'color' => 'red',
				],
				'description' => __( '<p>Output Buffering is disabled, <em>Mask All Links</em> will not work. Contact your server administrator to get this feature enabled.</p>', $this->plugin_name ),
			];
		}

		return $result;
	}
}
