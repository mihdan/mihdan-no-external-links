<?php
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Includes
 * @author        mihdan
 */

namespace Mihdan\No_External_Links;

/**
 * Class I18n.
 */
class I18n {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 */
	public function __construct( $plugin_name ) {

		$this->plugin_name = $plugin_name;

	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    4.0.0
	 */
	public function load_plugin_textdomain(): void {

		load_plugin_textdomain(
			$this->plugin_name,
			false,
			dirname( plugin_basename( __FILE__ ), 2 ) . '/languages/'
		);

	}

}
