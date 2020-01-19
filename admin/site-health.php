<?php
/**
 * Class that add Site Health tests.
 *
 * @since   4.5.1
 * @package Mihdan_NoExternalLinks
 * @author  mihdan
 */

namespace Mihdan\No_External_Links;

class Site_Health {

	/**
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Site_Health constructor.
	 */
	public function __construct( $plugin_name ) {
		$this->plugin_name = $plugin_name;

		$this->init_hooks();
	}

	/**
	 * Init plugin hooks.
	 */
	public function init_hooks() {
		add_filter( 'site_status_tests', [ $this, 'add_test' ] );
	}

	/**
	 * @param array $tests
	 *
	 * @return mixed
	 */
	public function add_test( $tests ) {

		$tests['direct'][ MIHDAN_NO_EXTERNAL_LINKS_SLUG ] = array(
			'label' => __( 'Output Buffering', $this->plugin_name ),
			'test'  => [ $this, 'check_buffering' ],
		);

		return $tests;
	}

	/**
	 * Custom tests.
	 *
	 * @return array
	 */
	public function check_buffering() {
		$output_buffer = ob_get_level() ? true : false;

		$result = [
			'label'       => __( 'Output Buffering is enabled', $this->plugin_name ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance' ),
				'color' => 'blue',
			),
			'description' => __( '<p>Output Buffering is enabled. <em>Mask All Links</em> will work.</p>', $this->plugin_name ),
		];

		if ( false === $output_buffer ) {
			$result = [
				'label'       => __( 'Output Buffering is disabled', $this->plugin_name ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Performance' ),
					'color' => 'red',
				),
				'description' => __( '<p>Output Buffering is disabled, <em>Mask All Links</em> will not work. Contact your server administrator to get this feature enabled.</p>', $this->plugin_name ),
			];
		}

		return $result;
	}
}

// eol.
