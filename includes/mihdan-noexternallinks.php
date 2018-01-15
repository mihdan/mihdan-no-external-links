<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since         4.0.0
 * @package       Mihdan_NoExternalLinks
 * @subpackage    Mihdan_NoExternalLinks/Includes
 * @author        mihdan
 */

class Mihdan_NoExternalLinks {

    /**
     * The class that's responsible for all administrative functions.
     *
     * @since    4.0.0
     * @access   protected
     * @var      Mihdan_NoExternalLinks_Admin    $admin    Contains all administrative functions.
     */
    protected $admin;

    /**
     * The class that's responsible for all public facing functions.
     *
     * @since    4.0.0
     * @access   protected
     * @var      Mihdan_NoExternalLinks_Public    $public    Contains all public facing functions.
     */
    protected $public;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    4.0.0
     * @access   protected
     * @var      Mihdan_NoExternalLinks_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    4.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
     * The list of options for the plugin.
     *
     * @since    4.0.0
     * @access   protected
     * @var      object    $options    The list of options for the plugin.
     */
    protected $options;

    /**
     * The options prefix of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $options_prefix    The options prefix of this plugin.
     */
    private $options_prefix;

    /**
     * The class that's responsible for custom functionality.
     *
     * @since    4.0.0
     * @access   protected
     * @var      WP_CustomParser    $custom_parser    Contains custom functions.
     */
    private $custom_parser;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    4.0.0
	 */
	public function __construct() {

        $this->plugin_name = 'mihdan-noexternallinks';
		$this->version = '4.2.2';
        $this->options_prefix = 'mihdan_noexternallinks_';

        $upload_dir = wp_upload_dir();
        $custom_parser = $upload_dir['basedir'] . '/custom-parser.php';

        $this->custom_parser = false;
        if ( file_exists( $custom_parser ) ) {
            $this->custom_parser = $custom_parser;
        }

		$this->load_dependencies();
        $this->compatibility_check();
        $this->install();
        $this->upgrade();
		$this->set_locale();
        $this->set_options();
        $this->initiate();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/i18n.php';

        /**
         * The class responsible for checking compatibility.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/compatibility.php';

        /**
         * The class responsible for database tables.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/database.php';

        /**
         * The class responsible for installing the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/installer.php';

        /**
         * The class responsible for upgrading the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/upgrader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';

        /**
         * The class responsible for the masks table.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/mask-table.php';

        /**
         * The class responsible for the logs table.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/log-table.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/public.php';

        /**
         * The class responsible for custom functionality.
         */
        if ( $this->custom_parser ) {
            require_once $this->custom_parser;
        }

		$this->loader = new Mihdan_NoExternalLinks_Loader();

	}

    /**
     * Runs the compatibility check.
     *
     * Checks if the plugin is compatible with WordPress and PHP.
     * Disables the plugin if checks fail.
     *
     * @since    4.0.0
     * @access   private
     */
    private function compatibility_check() {

        $plugin_compatibility = new Mihdan_NoExternalLinks_Compatibility( $this->get_plugin_name(), $this->get_options_prefix() );

        $this->loader->add_action( 'admin_init', $plugin_compatibility, 'check' );

    }

    /**
     * Runs the installation scripts.
     *
     * @since    4.2.0
     * @access   private
     */
    private function install() {

        $current_options = get_option( 'mihdan_noexternallinks' );

        if ( false === $current_options ) {
            $plugin_installer = new Mihdan_NoExternalLinks_Installer(
                $this->get_plugin_name(), $this->get_version(), $this->get_options_prefix()
            );

            $plugin_installer->install();
        }

    }

    /**
     * Runs the upgrade scripts.
     *
     * Updates database tables, fields, and data.
     *
     * @since    4.0.0
     * @access   private
     */
    private function upgrade() {

        $plugin_upgrader = new Mihdan_NoExternalLinks_Upgrader(
            $this->get_plugin_name(), $this->get_version(), $this->get_options_prefix()
        );

        $plugin_upgrader->upgrade();

    }

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Mihdan_NoExternalLinks_i18n( $this->get_plugin_name() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

    /**
     * Define the options for this plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    private function set_options() {

        $output_buffer = ob_get_level() > 0 ? true : false;
        $masking_default = $output_buffer ? false : true;

        $encryption = false;
        $encryption_key = false;

        if ( extension_loaded( 'openssl' ) ) {
            $encryption = 'openssl';
            $encryption_key = openssl_random_pseudo_bytes( 32 );
        } elseif ( extension_loaded( 'mcrypt' ) ) {
            $encryption = 'mcrypt';
            $encryption_key = md5( rand() );
        }

        // Default Options
        $options = array(
            'masking_type' => '302',
            'redirect_time' => 3,
            'mask_links' => $output_buffer ? 'all' : 'specific',
            'mask_posts_pages' => $masking_default,
            'mask_comments' => $masking_default,
            'mask_comment_author' => $masking_default,
            'mask_rss' => $masking_default,
            'mask_rss_comments' => $masking_default,
            'nofollow' => true,
            'target_blank' => true,
            'noindex_tag' => false,
            'noindex_comment' => false,
            'link_structure' => 'default',
            'separator' => 'goto',
            'link_encoding' => 'none',
            'encryption' => $encryption,
            'encryption_key' => $encryption_key,
            'link_shortening' => 'none',
            'adfly_api_key' => 'a722c6594441a443bafa644a820a8d3f',
            'adfly_user_id' => '17681319',
            'bitly_login' => 'steamerdev',
            'bitly_api_key' => 'R_31d62b0aa55e4c0abe306693624ff73a',
            'linkshrink_api_key' => 'Ssk',
            'shortest_api_key' => '57bfc99a0c2ce713061730b696750659',
            'logging' => true,
            'log_duration' => 0,
            'remove_all_links' => false,
            'links_to_text' => false,
            'debug_mode' => false,
            'anonymize_links' => false,
            'anonymous_link_provider' => 'https://href.li/?',
            'bot_targeting' => 'all',
            'bots_selector' => '',
            'check_referrer' => true,
            'inclusion_list' => '',
            'exclusion_list' => '',
            'skip_auth' => false,
            'skip_follow' => false,
            'redirect_message' => __(
                'You will be redirected in 3 seconds. If your browser does not automatically redirect you, please <a href="%linkurl%">click here</a>.',
                $this->plugin_name
            ),
            'custom_parser' => false,
            'output_buffer' => $output_buffer
        );

        $this->options = $this->validate_options( $options );

    }

    /**
     * Validates the options for this plugin.
     *
     * @since      4.2.0
     * @access     private
     * @param      array     $options
     * @return     object    $options
     */
    private function validate_options( $options ) {

        $output_buffer = $options['output_buffer'];

        $encryption = $options['encryption'];
        $encryption_key = $options['encryption_key'];

        foreach ( $options as $key => $value ) {
            $option = get_option( $this->options_prefix . $key );

            switch ( $key ) {
                case 'masking_type':
                case 'link_structure':
                case 'link_shortening':
                case 'anonymous_link_provider':
                case 'inclusion_list':
                case 'exclusion_list':
                case 'bot_targeting':
                case 'redirect_message':
                    if ( false !== $option ) {
                        $options[ $key ] = ( string ) $option;
                    }

                    continue 2;
                case 'adfly_api_key':
                case 'adfly_user_id':
                case 'bitly_login':
                case 'bitly_api_key':
                case 'linkshrink_api_key':
                case 'shortest_api_key':
                    if ( false !== $option && '' !== $option ) {
                        $options[ $key ] = ( string ) $option;
                    }

                    continue 2;
                case 'mask_links':
                    if ( false !== $option ) {
                        $options[ $key ] = ( string ) $option;
                    }

                    if ( ! $output_buffer ) {
                        $options[ $key ] = ( string ) 'specific';
                    }

                    continue 2;
                case 'link_encoding':
                    if ( false !== $option ) {
                        $options[ $key ] = ( string ) $option;
                    }

                    if ( 'aes256' === $option && ! $encryption ) {
                        $options[ $key ] = ( string ) 'none';
                    }

                    continue 2;
                case 'separator':
                    if ( '' !== $option && false !== $option ) {
                        $options[ $key ] = ( string ) $option;
                    } else {
                        $options[ $key ] = 'goto';
                    }

                    continue 2;
                case 'encryption_key':
                    if ( '' === $option || false === $option ) {
                        if ( $encryption_key ) {
                            $encryption_key = base64_encode( $encryption_key );
                            update_option( $this->options_prefix . $key, $encryption_key );
                            $options[ $key ] = ( string ) $encryption_key;
                        } else {
                            $options[ $key ] = false;
                        }
                    } else {
                        $options[ $key ] = ( string ) $option;
                    }

                    continue 2;
                case 'redirect_time':
                case 'log_duration':
                    if ( false !== $option ) {
                        $options[ $key ] = ( int ) $option;
                    }

                    continue 2;
                case 'custom_parser':
                    if ( $this->custom_parser ) {
                        $options[ $key ] = true;
                    }

                    continue 2;
                case 'bots_selector':
                    if ( false !== $option || '' !== $option ) {
                        $options[ $key ] = ( array ) $option;
                    }

                    continue 2;
                default:
                    if ( false !== $option ) {
                        $options[ $key ] = ( int ) $option === 1 ? true : false;
                    }
            }
        }

        return (object) $options;

    }

    /**
     * Initiates the plugin.
     *
     * @since    4.0.0
     * @access   private
     */
    private function initiate() {

        $this->admin = new Mihdan_NoExternalLinks_Admin(
            $this->get_plugin_name(),
            $this->get_version(),
            $this->get_options(),
            $this->get_options_prefix()
        );

        if ( $this->custom_parser ) {
            $this->public = new WP_CustomParser(
                $this->get_plugin_name(),
                $this->get_version(),
                $this->get_options()
            );
        } else {
            $this->public = new Mihdan_NoExternalLinks_Public(
                $this->get_plugin_name(),
                $this->get_version(),
                $this->get_options()
            );
        }

        if ( $this->options->skip_auth ) {
            $this->public->debug_info( 'Masking is enabled only for non logged in users' );

            // TODO: Look to improve this; without including pluggable.php
            if ( ! function_exists( 'is_user_logged_in' ) ) {
                $this->public->debug_info( '\'is_user_logged_in\' function not found! Trying to include its file' );

                require_once( ABSPATH . 'wp-includes/pluggable.php' );
            }
        }

        if ( $this->options->logging && $this->options->log_duration !== 0 ) {

            global $wpdb;

            $table_name = $wpdb->prefix . 'external_links_logs';

            $current_time = current_time( 'mysql' );

            $last_cleared = get_option( $this->options_prefix . 'last_cleared_logs' );

            if ( ! $last_cleared || $last_cleared < current_time( 'timestamp' ) - 3600 * 24 ) {
                $sql = "DELETE FROM $table_name WHERE date < DATE_SUB('$current_time', INTERVAL %d DAY)";

                $wpdb->query( $wpdb->prepare( $sql, $this->options->log_duration ) );

                update_option( $this->options_prefix . 'last_cleared_logs', current_time( 'timestamp' ) );
            }

        }

    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_scripts' );

        $this->loader->add_action( 'admin_menu', $this->admin, 'add_admin_pages' );
        $this->loader->add_action( 'admin_init', $this->admin, 'register_setting' );

        $this->loader->add_filter( 'set-screen-option', $this->admin, 'mask_page_set_screen_options', NULL, 3 );
        $this->loader->add_action( 'load-toplevel_page_' . $this->plugin_name, $this->admin, 'mask_page_screen_options' );

        $this->loader->add_filter( 'set-screen-option', $this->admin, 'log_page_set_screen_options', NULL, 3 );
        $this->loader->add_action( 'load-external-links_page_' . $this->get_plugin_name() . '-logs', $this->admin, 'log_page_screen_options' );

        $this->loader->add_action( 'add_meta_boxes', $this->admin, 'add_custom_meta_box' );
        $this->loader->add_action( 'save_post', $this->admin, 'save_custom_meta_box' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

        $this->loader->add_filter( 'template_redirect', $this->public, 'check_redirect', 1 );

        if ( $this->options->skip_auth && is_user_logged_in() ) {
            $this->public->debug_info("User is authorised, we're not doing anything");
        } else {
            if ( 'all' === $this->options->mask_links ) {
                $this->public->debug_info("Setting fullmask filters");

                $this->loader->add_filter( 'the_content', $this->public, 'check_post', 99 );
                $this->loader->add_filter( 'the_excerpt', $this->public, 'check_post', 99 );
                $this->loader->add_filter( 'wp', $this->public, 'fullpage_filter', 99 );
            } else {
                $this->public->debug_info("Setting per element filters");

                if ( $this->options->mask_posts_pages ) {
                    $this->loader->add_filter( 'the_content', $this->public, 'check_post', 99 );
                    $this->loader->add_filter( 'the_excerpt', $this->public, 'check_post', 99 );
                }

                if ( $this->options->mask_comments ) {
                    $this->loader->add_filter( 'comment_text', $this->public, 'filter', 99 );
                    $this->loader->add_filter( 'comment_url', $this->public, 'filter', 99 );
                }

                if ( $this->options->mask_comment_author ) {
                    $this->loader->add_filter ('get_comment_author_url_link', $this->public, 'filter', 99 );
                    $this->loader->add_filter( 'get_comment_author_link', $this->public, 'filter', 99 );
                    $this->loader->add_filter( 'get_comment_author_url', $this->public, 'filter', 99 );
                }
            }

            if ( $this->options->mask_rss ) {
                $this->loader->add_filter( 'the_content_feed', $this->public, 'filter', 99 );
                $this->loader->add_filter( 'the_content_rss', $this->public, 'filter', 99 );
                $this->loader->add_filter( 'the_excerpt_rss', $this->public, 'filter', 99 );
            }

            if ( $this->options->mask_rss_comments ) {
                $this->loader->add_filter( 'comment_text_rss', $this->public, 'filter', 99 );
            }
        }

        if ( $this->options->debug_mode ) {
            $this->loader->add_action( 'wp_footer', $this->public, 'output_debug', 99 );
        }

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    4.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     4.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     4.0.0
	 * @return    Mihdan_NoExternalLinks_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     4.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

    /**
     * Retrieve the option prefix for the plugin.
     *
     * @since     4.0.0
     * @return    string    The option prefix for the plugin.
     */
    public function get_options_prefix() {
        return $this->options_prefix;
    }

    /**
     * Retrieve the list of options for the plugin.
     *
     * @since     4.0.0
     * @return    object    The list of options for the plugin.
     */
    public function get_options() {
        return $this->options;
    }

}
