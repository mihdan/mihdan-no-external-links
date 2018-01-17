<?php
/**
 * Mihdan: No External Links bootstrap file.
 *
 * @link              https://wordpress.org/plugins/mihdan-noexternallinks/
 * @since             4.0.0
 * @package           Mihdan_NoExternalLinks
 *
 * @wordpress-plugin
 * Plugin Name:       Mihdan: No External Links
 * Plugin URI:        https://wordpress.org/plugins/mihdan-noexternallinks/
 * Description:       Convert external links into internal links, site wide or post/page specific. Add NoFollow, Click logging, and more...
 * Version:           4.3.2
 * Author:            Mikhail Kobzarev
 * Author URI:        https://www.kobzarev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mihdan-noexternallinks
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/mihdan-noexternallinks.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.0.0
 */
function run_mihdan_noexternallinks() {
	$plugin = new Mihdan_NoExternalLinks();
	$plugin->run();
}

run_mihdan_noexternallinks();

// eof;
