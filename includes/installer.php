<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since         4.2.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Includes
 * @author        SteamerDevelopment
 */

class WP_NoExternalLinks_Installer {

    /**
     * The ID of this plugin.
     *
     * @since    4.2.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    4.2.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The options prefix of this plugin.
     *
     * @since    4.2.0
     * @access   private
     * @var      string    $options_prefix    The options prefix of this plugin.
     */
    private $options_prefix;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.2.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version           The version of this plugin.
     * @param      string    $options_prefix    The options prefix of this plugin.
     */
    public function __construct( $plugin_name, $version, $options_prefix ) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options_prefix = $options_prefix;

    }

	/**
     * Runs the installation scripts.
	 *
	 * @since    4.2.0
	 */
	public function install() {

        $installed_version = get_option( $this->options_prefix . 'version' );

        if ( false === $installed_version || version_compare( $installed_version, '4.2.0', '<' ) ) {

            WP_NoExternalLinks_Database::migrate();

            $installed_version = '4.2.0';
            update_option( $this->options_prefix . 'version', $installed_version );

        }

	}

}
