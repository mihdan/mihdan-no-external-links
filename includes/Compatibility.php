<?php
/**
 * Checks plugin is compatible with WordPress and PHP.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/includes
 * @author        mihdan
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

namespace Mihdan\No_External_Links;

/**
 * Class Compatibility.
 */
class Compatibility {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private string $options_prefix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name    The name of this plugin.
	 * @param string $options_prefix The option prefix of this plugin.
	 */
	public function __construct( string $plugin_name, string $options_prefix ) {
		$this->plugin_name    = $plugin_name;
		$this->options_prefix = $options_prefix;
	}

	/**
	 * Checks plugin compatibility.
	 *
	 * Checks plugin is compatible with WordPress and PHP.
	 * Disables plugin if checks fail.
	 *
	 * @since    4.0.0
	 *
	 * @param string $wp  WordPress version.
	 * @param string $php PHP version.
	 *
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function check( string $wp = '3.5', string $php = '5.3' ): void {

		$compatibility_check = get_option( $this->options_prefix . 'compatibility_check' );

		if ( 1 !== $compatibility_check ) {
			global $wp_version;

			if ( version_compare( PHP_VERSION, $php, '<' ) ) {
				$flag = 'PHP';
			} elseif ( version_compare( $wp_version, $wp, '<' ) ) {
				$flag = 'WordPress';
			} else {
				add_option( $this->options_prefix . 'compatibility_check', 1 );

				return;
			}

			$version = ( 'PHP' === $flag ) ? $php : $wp;

			deactivate_plugins( MIHDAN_NO_EXTERNAL_LINKS_BASENAME );

			wp_die(
				'<p><strong>Mihdan: No External Links</strong> ' .
				esc_html__( 'requires', $this->plugin_name ) . ' ' .
				esc_html( $flag ) . ' ' . esc_html( $version ) . ' ' .
				esc_html__( 'or greater', $this->plugin_name ),
				esc_html__( 'Plugin Activation Error', $this->plugin_name ),
				[
					'response'  => 200,
					'back_link' => true,
				]
			);
		}

	}
}
