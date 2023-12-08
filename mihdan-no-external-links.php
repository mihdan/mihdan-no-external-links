<?php
/**
 * No External Links bootstrap file.
 *
 * @link              https://wordpress.org/plugins/mihdan-no-external-links/
 * @since             4.0.0
 * @package           mihdan-no-external-links
 *
 * @wordpress-plugin
 * Plugin Name:       No External Links
 * Plugin URI:        https://wordpress.org/plugins/mihdan-no-external-links/
 * Description:       Convert external links into internal links, site wide or post/page specific. Add NoFollow, Click logging, and more...
 * Version:           5.1.2.1
 * Author:            Mikhail Kobzarev
 * Author URI:        https://www.kobzarev.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mihdan-no-external-links
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/mihdan/mihdan-no-external-links/
 */

namespace Mihdan\No_External_Links;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

const MIHDAN_NO_EXTERNAL_LINKS_DIR     = __DIR__;
const MIHDAN_NO_EXTERNAL_LINKS_VERSION = '5.1.2.1';
const MIHDAN_NO_EXTERNAL_LINKS_SLUG    = 'mihdan-no-external-links';

define( 'MIHDAN_NO_EXTERNAL_LINKS_BASENAME', plugin_basename( __FILE__ ) );
define( 'MIHDAN_NO_EXTERNAL_LINKS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

$autoload_path = MIHDAN_NO_EXTERNAL_LINKS_DIR . '/vendor/autoload.php';

if ( file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once MIHDAN_NO_EXTERNAL_LINKS_DIR . '/includes/Main.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    4.0.0
 */
( new Main() )->run();
