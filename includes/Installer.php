<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since         4.2.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Includes
 * @author        mihdan
 */

namespace Mihdan\No_External_Links;

/**
 * Class Installer.
 */
class Installer {

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.2.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private $options_prefix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.2.0
	 *
	 * @param string $plugin_name    The name of the plugin.
	 * @param string $version        The version of this plugin.
	 * @param string $options_prefix The options prefix of this plugin.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function __construct( $plugin_name, $version, $options_prefix ) {

		$this->options_prefix = $options_prefix;

	}

	/**
	 * Runs the installation scripts.
	 *
	 * @since    4.2.0
	 */
	public function install(): void {

		$installed_version = get_option( $this->options_prefix . 'version' );

		if ( false === $installed_version || version_compare( $installed_version, '4.2.1', '<' ) ) {

			Database::migrate();

			$installed_version = '4.5.1';
			update_option( $this->options_prefix . 'version', $installed_version );

		}
	}

}
