<?php
/**
 * Checks plugin is compatible with WordPress and PHP.
 *
 * @since         4.0.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Includes
 * @author        SteamerDevelopment
 */

class WP_NoExternalLinks_Compatibility {

    /**
     * The unique identifier of this plugin.
     *
     * @since    4.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The options prefix of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $options_prefix    The options prefix of this plugin.
     */
    private $options_prefix;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $options_prefix    The option prefix of this plugin.
     */
    public function __construct( $plugin_name, $options_prefix ) {

        $this->plugin_name = $plugin_name;
        $this->options_prefix = $options_prefix;

    }

	/**
	 * Checks plugin compatibility.
	 *
	 * Checks plugin is compatible with WordPress and PHP.
     * Disables plugin if checks fail.
	 *
     * @param    $wp
     * @param    $php
	 * @since    4.0.0
	 */
	public function check( $wp = '3.5', $php = '5.3' ) {

	    $compatibility_check = get_option( $this->options_prefix . 'compatibility_check' );

	    if ( ! $compatibility_check || 1 != $compatibility_check ) {
            global $wp_version;

            if ( version_compare( PHP_VERSION, $php, '<' ) ) {
                $flag = 'PHP';
            } elseif ( version_compare( $wp_version, $wp, '<' ) ) {
                $flag = 'WordPress';
            } else {
                add_option( $this->options_prefix . 'compatibility_check', 1 );
                return;
            }

            $version = 'PHP' == $flag ? $php : $wp;

            $path = basename( dirname( __DIR__ ) ) . '/' . $this->plugin_name . '.php';

            deactivate_plugins( $path );

            wp_die(
                '<p><strong>WP No External Links</strong> ' .
                __('requires', $this->plugin_name) . ' ' .
                $flag . ' '. $version . ' ' .
                __('or greater', $this->plugin_name),
                __( 'Plugin Activation Error', $this->plugin_name ),
                array( 'response' => 200, 'back_link' => true )
            );
        }

	}

}
