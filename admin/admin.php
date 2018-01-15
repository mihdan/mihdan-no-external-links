<?php
/**
 * Admin specific functionality.
 *
 * @since         4.0.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Admin
 * @author        SteamerDevelopment
 */

class WP_NoExternalLinks_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * The options of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      object    $options    The current options of this plugin.
     */
    private $options;

    /**
     * The options prefix of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $options_prefix    The options prefix of this plugin.
     */
    private $options_prefix;

    /**
     * The permalink query symbol.
     * Will be either a question mark, or a forward slash.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $permalink_query    The permalink query symbol.
     */
    private $permalink_query;

    /**
     * The permalink equals symbol.
     * Will be either an equals sign, or a forward slash.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $permalink_equals    The permalink equals sign.
     */
    private $permalink_equals;

    /**
     * The class that is responsible for the masks table.
     *
     * @since    4.2.0
     * @var      object    WP_List_Table    $masks_table    Contains mask table functionality.
     */
    public $masks_table;

    /**
     * The class that is responsible for the logs table.
     *
     * @since    4.0.0
     * @var      object    WP_List_Table    $logs_table    Contains log table functionality.
     */
    public $logs_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version           The version of this plugin.
     * @param    object    $options           The current options of this plugin.
     * @param    string    $options_prefix    The options prefix of this plugin.
	 */
	public function __construct( $plugin_name, $version, $options, $options_prefix ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->options = $options;

		$this->options_prefix = $options_prefix;

        $this->permalink_query = get_option( 'permalink_structure' ) ? '' : '?';
        $this->permalink_equals = get_option( 'permalink_structure' ) ? '/' : '=';

        $this->admin_notices();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/wp-noexternallinks-admin.min.css',
            array(),
            $this->version,
            'all'
        );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/wp-noexternallinks-admin.min.js',
            array( 'jquery' ),
            $this->version,
            false
        );

	}

    /**
     * Add No External Links menu and pages.
     *
     * @since  4.0.0
     */
    public function add_admin_pages() {

        add_menu_page(
            __( 'No External Links', $this->plugin_name ),
            __( 'External Links', $this->plugin_name ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_masks_page' ),
            'dashicons-admin-links'
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'WP No External Links Masks', $this->plugin_name ),
            __( 'Masks', $this->plugin_name ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_masks_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'WP No External Links Logs', $this->plugin_name ),
            __( 'Logs', $this->plugin_name ),
            'manage_options',
            $this->plugin_name . '-logs',
            array( $this, 'display_log_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'WP No External Links Settings', $this->plugin_name ),
            __( 'Settings', $this->plugin_name ),
            'manage_options',
            $this->plugin_name . '-settings',
            array( $this, 'display_settings_page' )
        );

    }

    /**
     * Set screen options for the masks page.
     *
     * @since     4.2.0
     * @param     bool|int    $status
     * @param     string      $option The option name.
     * @param     bool|int    $value The number of rows to use.
     * @return    int
     */
    public static function mask_page_set_screen_options( $status, $option, $value ) {

        return $value;

    }

    /**
     * Define screen options for the masks page.
     *
     * @since    4.2.0
     */
    public function mask_page_screen_options() {

        $option = 'per_page';
        $args   = array(
            'label'   => 'Number of items per page:',
            'default' => 20,
            'option'  => 'masks_per_page'
        );

        add_screen_option( $option, $args );

        $this->masks_table = new WP_NoExternalLinks_Admin_Mask_Table( $this->plugin_name, $this->options_prefix );

        $this->masks_table->process_bulk_action();

    }

    /**
     * Set screen options for the logs page.
     *
     * @since     4.0.0
     * @param     bool|int    $status
     * @param     string      $option The option name.
     * @param     bool|int    $value The number of rows to use.
     * @return    int
     */
    public static function log_page_set_screen_options( $status, $option, $value ) {

        return $value;

    }

    /**
     * Define screen options for the logs page.
     *
     * @since    4.0.0
     */
    public function log_page_screen_options() {

        $option = 'per_page';
        $args   = array(
            'label'   => 'Number of items per page:',
            'default' => 20,
            'option'  => 'logs_per_page'
        );

        add_screen_option( $option, $args );

        $this->logs_table = new WP_NoExternalLinks_Admin_Log_Table( $this->plugin_name, $this->options_prefix );

        $this->logs_table->process_bulk_action();

    }

    /**
     * Render the masks page.
     *
     * @since  4.2.0
     */
    public function display_masks_page() {

        include_once 'partials/masks.php';

    }

    /**
     * Render the logs page.
     *
     * @since  4.0.0
     */
    public function display_log_page() {

        include_once 'partials/logs.php';

    }

    /**
     * Render the settings page.
     *
     * @since  4.0.0
     */
    public function display_settings_page() {

        include_once 'partials/settings.php';

    }

    /**
     * Register plugin settings.
     *
     * @since 4.0.0
     */
    public function register_setting() {

        add_settings_section(
            $this->options_prefix . 'settings_section',
            '',
            '',
            $this->plugin_name . '-settings'
        );

        add_settings_section(
            $this->options_prefix . 'settings_links_structure_section',
            __( 'Masking Link Structure', $this->plugin_name ),
            '',
            $this->plugin_name . '-settings-links'
        );

        add_settings_section(
            $this->options_prefix . 'settings_links_encoding_section',
            __( 'Masking Link Encoding', $this->plugin_name ),
            '',
            $this->plugin_name . '-settings-links'
        );

        add_settings_section(
            $this->options_prefix . 'settings_link_shortening_section',
            __( 'Link Shortening', $this->plugin_name ),
            '',
            $this->plugin_name . '-settings-links'
        );

        add_settings_section(
            $this->options_prefix . 'settings_advanced_section',
            '',
            '',
            $this->plugin_name . '-settings-advanced'
        );

        add_settings_section(
            $this->options_prefix . 'settings_include_exclude_section',
            '',
            array( $this, 'include_exclude_cb' ),
            $this->plugin_name . '-settings-include-exclude'
        );

        add_settings_field(
            $this->options_prefix . 'masking_type',
            __( 'Masking Type', $this->plugin_name ),
            array( $this, 'masking_type_cb' ),
            $this->plugin_name . '-settings',
            $this->options_prefix . 'settings_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'masking_type'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'redirect_time'
        );

        add_settings_field(
            $this->options_prefix . 'mask',
            __( 'Masking', $this->plugin_name ),
            array( $this, 'mask_cb' ),
            $this->plugin_name . '-settings',
            $this->options_prefix . 'settings_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_links'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_posts_pages'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_comments'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_comment_author'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_rss'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'mask_rss_comments'
        );

        add_settings_field(
            $this->options_prefix . 'general',
            __( 'General', $this->plugin_name ),
            array( $this, 'general_cb' ),
            $this->plugin_name . '-settings',
            $this->options_prefix . 'settings_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'nofollow'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'target_blank'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'noindex_tag'
        );

        register_setting(
            $this->plugin_name . '-settings',
            $this->options_prefix . 'noindex_comment'
        );

        add_settings_field(
            $this->options_prefix . 'logging',
            __( 'Logging', $this->plugin_name ),
            array( $this, 'logging_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'logging'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'log_duration'
        );

        add_settings_field(
            $this->options_prefix . 'anonymize',
            __( 'Anonymize', $this->plugin_name ),
            array( $this, 'anonymize_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'anonymize_links'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'anonymous_link_provider'
        );

        add_settings_field(
            $this->options_prefix . 'bot_targeting',
            __( 'Bot Targeting', $this->plugin_name ),
            array( $this, 'bot_targeting_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'bot_targeting'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'bots_selector'
        );

        add_settings_field(
            $this->options_prefix . 'advanced',
            __( 'Advanced', $this->plugin_name ),
            array( $this, 'advanced_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'check_referrer'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'remove_all_links'
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'links_to_text'
        );

        add_settings_field(
            $this->options_prefix . 'debugging',
            __( 'Debugging', $this->plugin_name ),
            array( $this, 'debugging_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'debug_mode'
        );

        add_settings_field(
            $this->options_prefix . 'link_structure_default',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_structure"
                         id="' . $this->options_prefix . 'link_structure_default"
                         value="default"
                         ' . checked($this->options->link_structure, 'default', false) . ' /> ' . __( 'Default', $this->plugin_name ),
            array( $this, 'link_structure_default_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_structure_section',
            array( 'label_for' => $this->options_prefix . 'link_structure_default' )
        );

        add_settings_field(
            $this->options_prefix . 'link_structure_custom',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_structure"
                         id="' . $this->options_prefix . 'link_structure_custom"
                         value="custom"
                         ' . checked($this->options->link_structure, 'custom', false) . ' /> ' . __( 'Custom', $this->plugin_name ),
            array( $this, 'link_structure_custom_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_structure_section',
            array( 'label_for' => $this->options_prefix . 'link_structure_custom' )
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'link_structure'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'separator'
        );

        add_settings_field(
            $this->options_prefix . 'link_encoding_none',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_none"
                         value="none"
                         ' . checked($this->options->link_encoding, 'none', false) . ' /> ' .
                         __( 'None', $this->plugin_name ),
            array( $this, 'link_encoding_none_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_encoding_section',
            array( 'label_for' => $this->options_prefix . 'link_encoding_none' )
        );

        add_settings_field(
            $this->options_prefix . 'link_encoding_aes256',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_aes256"
                         value="aes256"
                         ' . checked($this->options->link_encoding, 'aes256', false) . '
                         ' . disabled( $this->options->encryption, false, false ) . '/> ' .
                         __( 'AES-256', $this->plugin_name ),
            array( $this, 'link_encoding_aes256_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_encoding_section',
            array( 'label_for' => $this->options_prefix . 'link_encoding_aes256' )
        );

        add_settings_field(
            $this->options_prefix . 'link_encoding_base64',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_base64"
                         value="base64"
                         ' . checked($this->options->link_encoding, 'base64', false) . ' /> ' .
                         __( 'Base64', $this->plugin_name ),
            array( $this, 'link_encoding_base64_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_encoding_section',
            array( 'label_for' => $this->options_prefix . 'link_encoding_base64' )
        );

        add_settings_field(
            $this->options_prefix . 'link_encoding_numbers',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_numbers"
                         value="numbers"
                         ' . checked($this->options->link_encoding, 'numbers', false) . ' /> ' .
            __( 'Numeric', $this->plugin_name ),
            array( $this, 'link_encoding_numbers_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_links_encoding_section',
            array( 'label_for' => $this->options_prefix . 'link_encoding_numbers' )
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'link_encoding'
        );

        add_settings_field(
            $this->options_prefix . 'link_shortening_none',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_none"
                         value="none"
                         ' . checked($this->options->link_shortening, 'none', false) . ' /> ' .
            __( 'None', $this->plugin_name ),
            array( $this, 'link_shortening_none_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_link_shortening_section',
            array( 'label_for' => $this->options_prefix . 'link_shortening_none' )
        );

        add_settings_field(
            $this->options_prefix . 'link_shortening_adfly',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_adfly"
                         value="adfly"
                         ' . checked($this->options->link_shortening, 'adfly', false) . ' /> ' .
            __( 'Adf.ly', $this->plugin_name ),
            array( $this, 'link_shortening_adfly_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_link_shortening_section',
            array( 'label_for' => $this->options_prefix . 'link_shortening_adfly' )
        );

        add_settings_field(
            $this->options_prefix . 'link_shortening_bitly',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_bitly"
                         value="bitly"
                         ' . checked($this->options->link_shortening, 'bitly', false) . ' /> ' .
            __( 'Bitly', $this->plugin_name ),
            array( $this, 'link_shortening_bitly_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_link_shortening_section',
            array( 'label_for' => $this->options_prefix . 'link_shortening_bitly' )
        );

        add_settings_field(
            $this->options_prefix . 'link_shortening_linkshrink',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_linkshrink"
                         value="linkshrink"
                         ' . checked($this->options->link_shortening, 'linkshrink', false) . ' /> ' .
            __( 'Link Shrink', $this->plugin_name ),
            array( $this, 'link_shortening_linkshrink_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_link_shortening_section',
            array( 'label_for' => $this->options_prefix . 'link_shortening_linkshrink' )
        );

        add_settings_field(
            $this->options_prefix . 'link_shortening_shortest',
            '<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_shortest"
                         value="shortest"
                         ' . checked($this->options->link_shortening, 'shortest', false) . ' /> ' .
            __( 'Shorte.st', $this->plugin_name ),
            array( $this, 'link_shortening_shortest_cb' ),
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'settings_link_shortening_section',
            array( 'label_for' => $this->options_prefix . 'link_shortening_shortest' )
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'link_shortening'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'adfly_api_key'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'adfly_user_id'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'bitly_login'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'bitly_api_key'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'linkshrink_api_key'
        );

        register_setting(
            $this->plugin_name . '-settings-links',
            $this->options_prefix . 'shortest_api_key'
        );

        add_settings_field(
            $this->options_prefix . 'javascript',
            __( 'Javascript Redirect Text', $this->plugin_name ),
            array( $this, 'javascript_cb' ),
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'settings_advanced_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-advanced',
            $this->options_prefix . 'redirect_message'
        );

        add_settings_field(
            $this->options_prefix . 'include',
            __( 'Include', $this->plugin_name ),
            array( $this, 'include_cb' ),
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'settings_include_exclude_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'inclusion_list'
        );

        add_settings_field(
            $this->options_prefix . 'exclude',
            __( 'Exclude', $this->plugin_name ),
            array( $this, 'exclude_cb' ),
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'settings_include_exclude_section',
            ''
        );

        register_setting(
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'exclusion_list'
        );

        register_setting(
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'skip_auth'
        );

        register_setting(
            $this->plugin_name . '-settings-include-exclude',
            $this->options_prefix . 'skip_follow'
        );

    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function masking_type_cb() {
        ?>
        <fieldset>
            <label>
                <input type="radio"
                       name="<?php echo $this->options_prefix . 'masking_type' ?>"
                       value="no"
                       <?php checked( $this->options->masking_type, 'no' ); ?> />
                <?php _e( 'No Redirect', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <input type="radio"
                       name="<?php echo $this->options_prefix . 'masking_type' ?>"
                       value="302"
                       <?php checked( $this->options->masking_type, '302' ); ?> />
                <?php _e( '302 Redirect', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <input type="radio"
                       name="<?php echo $this->options_prefix . 'masking_type' ?>"
                       value="javascript"
                       <?php checked( $this->options->masking_type, 'javascript' ); ?> />
                <?php _e( 'Javascript Redirect', $this->plugin_name ); ?>
            </label>
            <ul>
                <li>
                    <?php _e( 'Redirect after', $this->plugin_name ); ?>
                    <input type="text"
                           name="<?php echo $this->options_prefix . 'redirect_time' ?>"
                           id="<?php echo $this->options_prefix . 'redirect_time' ?>"
                           size="3"
                           maxlength="4"
                           value="<?php echo $this->options->redirect_time ? $this->options->redirect_time : 3 ?>"
                           <?php echo 'javascript' == $this->options->masking_type ? '' : 'readonly' ?> />
                    <?php _e( 'seconds', $this->plugin_name ); ?>
                </li>
            </ul>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function mask_cb() {
        ?>
        <fieldset>
            <label>
                <input type="radio"
                       name="<?php echo $this->options_prefix . 'mask_links' ?>"
                       value="all"
                       <?php checked( $this->options->mask_links, 'all' ); ?>
                       <?php disabled( ob_get_level(), 0 ); ?> />
                <?php _e( 'Mask All Links', $this->plugin_name ); ?>
                &nbsp;
                <strong><small><em>(<?php _e( 'Recommended', $this->plugin_name ); ?>)</em></small></strong>
            </label>
            <br>
            <label>
                <input type="radio"
                       name="<?php echo $this->options_prefix . 'mask_links' ?>"
                       value="specific"
                       <?php checked( $this->options->mask_links, 'specific' ); ?> />
                <?php _e( 'Mask Specific Links (select below)', $this->plugin_name ); ?>
            </label>
            <div class="list-tree">
                <ul>
                    <li>
                        <label>
                            <input type="checkbox"
                                   name="<?php echo $this->options_prefix . 'mask_posts_pages' ?>"
                                   id="<?php echo $this->options_prefix . 'mask_posts_pages' ?>"
                                   value="1"
                                   <?php checked( $this->options->mask_posts_pages ); ?>
                                   <?php checked( $this->options->mask_links, 'all' ); ?>
                                   <?php disabled( $this->options->mask_links, 'all' ); ?> />
                            <?php _e( 'Mask links in posts and pages', $this->plugin_name ); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox"
                                   name="<?php echo $this->options_prefix . 'mask_comments' ?>"
                                   id="<?php echo $this->options_prefix . 'mask_comments' ?>"
                                   value="1"
                                   <?php checked( $this->options->mask_comments ); ?>
                                   <?php checked( $this->options->mask_links, 'all' ); ?>
                                   <?php disabled( $this->options->mask_links, 'all' ); ?> />
                            <?php _e( 'Mask links in comments', $this->plugin_name ); ?>
                        </label>
                    </li>
                    <li>
                        <label>
                            <input type="checkbox"
                                   name="<?php echo $this->options_prefix . 'mask_comment_author' ?>"
                                   id="<?php echo $this->options_prefix . 'mask_comment_author' ?>"
                                   value="1"
                                   <?php checked( $this->options->mask_comment_author ); ?>
                                   <?php checked( $this->options->mask_links, 'all' ); ?>
                                   <?php disabled( $this->options->mask_links, 'all' ); ?> />
                            <?php _e( 'Mask comment authors\'s homepage link', $this->plugin_name ); ?>
                        </label>
                    </li>
                </ul>
            </div>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'mask_rss' ?>"
                       id="<?php echo $this->options_prefix . 'mask_rss' ?>"
                       value="1"
                       <?php checked( $this->options->mask_rss ); ?> />
                <?php _e( 'Mask links in your RSS post content', $this->plugin_name ); ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_tag' ?>_description">
                <?php _e( 'May result in invalid RSS if used with some masking options.', $this->plugin_name ); ?>
            </p>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'mask_rss_comments' ?>"
                       id="<?php echo $this->options_prefix . 'mask_rss_comments' ?>"
                       value="1"
                       <?php checked( $this->options->mask_rss_comments ); ?> />
                <?php _e( 'Mask links in RSS comments', $this->plugin_name ); ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_tag' ?>_description">
                <?php _e( 'May result in invalid RSS if used with some masking options.', $this->plugin_name ); ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function general_cb() {
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'nofollow' ?>"
                       id="<?php echo $this->options_prefix . 'nofollow' ?>"
                       value="1"
                       <?php checked( $this->options->nofollow ); ?> />
                <?php _e( 'No Follow Masked Links', $this->plugin_name ); ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_tag' ?>_description">
                <?php _e( 'Add rel="nofollow" to masked links.', $this->plugin_name ); ?>
            </p>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'target_blank' ?>"
                       id="<?php echo $this->options_prefix . 'target_blank' ?>"
                       value="1"
                       <?php checked( $this->options->target_blank ); ?> />
                <?php _e( 'Open Masked Links in a New Window', $this->plugin_name ); ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_tag' ?>_description">
                <?php _e( 'Add target="_blank" to masked links.', $this->plugin_name ); ?>
            </p>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'noindex_tag' ?>"
                       id="<?php echo $this->options_prefix . 'noindex_tag' ?>"
                       value="1"
                       <?php checked( $this->options->noindex_tag ); ?> />
                <?php
                    _e(
                        'Surround Masked Links with <code>&#x3C;noindex/&#x3E;</code> tags',
                        $this->plugin_name
                    );
                ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_tag' ?>_description">
                <?php _e( 'For yandex search engine.', $this->plugin_name ); ?>
            </p>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'noindex_comment' ?>"
                       id="<?php echo $this->options_prefix . 'noindex_comment' ?>"
                       value="1"
                       <?php checked( $this->options->noindex_comment ); ?> />
                <?php
                    _e(
                        'Surround Masked Links with <code>&#x3C;!--noindex--&#x3E;</code> comments',
                        $this->plugin_name
                    );
                ?>
            </label>
            <p class="description" id="<?php echo $this->options_prefix . 'noindex_comment' ?>_description">
                <?php
                    _e(
                        'For yandex search engine, better then noindex tag because valid.',
                        $this->plugin_name
                    );
                ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function logging_cb() {
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'logging' ?>"
                       id="<?php echo $this->options_prefix . 'logging' ?>"
                       value="1"
                       <?php checked( $this->options->logging ); ?> />
                <?php _e( 'Enable Logging', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <?php _e( 'Keep logs for ', $this->plugin_name ); ?>
                <input type="text"
                       name="<?php echo $this->options_prefix . 'log_duration' ?>"
                       id="<?php echo $this->options_prefix . 'log_duration' ?>"
                       size="3"
                       maxlength="4"
                       value="<?php echo $this->options->log_duration >= 0 ? $this->options->log_duration : 30 ?>"
                       <?php echo true == $this->options->logging ? '' : 'readonly' ?> />
                <?php _e( 'days', $this->plugin_name ); ?>
            </label>
            <p class="description">
                <?php _e( 'Set to 0 to keep logs permanently.', $this->plugin_name ) ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Render the anonymize settings section.
     *
     * @since  4.2.0
     */
    public function anonymize_cb() {
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'anonymize_links' ?>"
                       id="<?php echo $this->options_prefix . 'anonymize_links' ?>"
                       value="1"
                    <?php checked( $this->options->anonymize_links ); ?> />
                <?php _e( 'Enable Anonymous Links', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <?php _e( 'Anonymizer prefix ', $this->plugin_name ); ?>
                <input type="text"
                       name="<?php echo $this->options_prefix . 'anonymous_link_provider' ?>"
                       id="<?php echo $this->options_prefix . 'anonymous_link_provider' ?>"
                       value="<?php echo $this->options->anonymous_link_provider ?>"
                    <?php echo true == $this->options->anonymize_links ? '' : 'readonly' ?> />
                <code>http://www.example.com</code>
            </label>
        </fieldset>
        <?php
    }

    /**
     * Render the bot targeting settings section.
     *
     * @since  4.2.0
     */
    public function bot_targeting_cb() {
        ?>
        <fieldset>
            <p>
                <label>
                    <input type="radio"
                           name="<?php echo $this->options_prefix . 'bot_targeting' ?>"
                           id="<?php echo $this->options_prefix . 'bot_targeting' ?>"
                           value="all"
                           <?php checked( $this->options->bot_targeting, 'all' ); ?> />
                    <?php _e( 'Target All Bots', $this->plugin_name ); ?>
                </label>
            </p>
            <p>
                <label>
                    <input type="radio"
                           name="<?php echo $this->options_prefix . 'bot_targeting' ?>"
                           id="<?php echo $this->options_prefix . 'bot_targeting' ?>"
                           value="specific"
                           <?php checked( $this->options->bot_targeting, 'specific' ); ?> />
                    <?php _e( 'Target Specific Bot(s)', $this->plugin_name ); ?>
                </label>
            </p>
            <ul>
                <li>
                    <label for="">
                        <?php _e( 'Bot(s)', $this->plugin_name ) ?>
                        <select name="<?php echo $this->options_prefix . 'bots_selector[]' ?>"
                                id="<?php echo $this->options_prefix . 'bots_selector' ?>"
                                multiple
                                <?php disabled( $this->options->bot_targeting, 'all' ); ?>>
                            <option value="aolspider"
                                <?php
                                selected( in_array( 'aolspider', $this->options->bots_selector ) )
                                ?>>AOL</option>
                            <option value="askbot"
                                <?php
                                selected( in_array( 'askbot', $this->options->bots_selector ) )
                                ?>>Ask</option>
                            <option value="baiduspider"
                                <?php
                                selected( in_array( 'baiduspider', $this->options->bots_selector ) )
                                ?>>Baidu</option>
                            <option value="bingbot"
                                <?php
                                selected( in_array( 'bingbot', $this->options->bots_selector ) )
                                ?>>Bing</option>
                            <option value="duckduckbot"
                                <?php
                                selected( in_array( 'duckduckbot', $this->options->bots_selector ) )
                                ?>>DuckDuckGo</option>
                            <option value="googlebot"
                                <?php
                                selected( in_array( 'googlebot', $this->options->bots_selector ) )
                                ?>>Google</option>
                            <option value="yahoobot"
                                <?php
                                selected( in_array( 'yahoobot', $this->options->bots_selector ) )
                                ?>>Yahoo</option>
                            <option value="yandexbot"
                                <?php
                                selected( in_array( 'yandexbot', $this->options->bots_selector ) )
                                ?>>Yandex</option>
                        </select>
                    </label>
                </li>
            </ul>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function advanced_cb() {
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'check_referrer' ?>"
                       id="<?php echo $this->options_prefix . 'check_referrer' ?>"
                       value="1"
                       <?php checked( $this->options->check_referrer ); ?> />
                <?php _e( 'Check Referrer', $this->plugin_name ); ?>
            </label>
            <p class="description">
                <?php
                    _e(
                        'Check for document referer and restrict redirect if it is not your own website.
                              Useful against spoofing attacks.',
                        $this->plugin_name
                    )
                ?>
            </p>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'remove_all_links' ?>"
                       id="<?php echo $this->options_prefix . 'remove_all_links' ?>"
                       value="1"
                       <?php checked( $this->options->remove_all_links ); ?> />
                <?php _e( 'Remove All Links', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'links_to_text' ?>"
                       id="<?php echo $this->options_prefix . 'links_to_text' ?>"
                       value="1"
                       <?php checked( $this->options->links_to_text ); ?> />
                <?php _e( 'Convert All Links to Text', $this->plugin_name ); ?>
            </label>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function debugging_cb() {
        ?>
        <fieldset>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'debug_mode' ?>"
                       id="<?php echo $this->options_prefix . 'debug_mode' ?>"
                       value="1"
                       <?php checked( $this->options->debug_mode ); ?> />
                <?php _e( 'Enable Debug Mode', $this->plugin_name ); ?>
            </label>
            <p class="description">
                <?php
                    _e(
                        'Adds comment lines like
                              <code>&#x3C;!--wp-noexternallinks debug: some info--&#x3E;</code> to output.',
                        $this->plugin_name
                    )
                ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function link_structure_default_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url') ?>/<?php
            echo $this->permalink_query ?>goto<?php echo $this->permalink_equals ?>https://example.com
        </code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function link_structure_custom_cb() {
        ?>
        <code><?php echo get_bloginfo('url') ?>/<?php echo $this->permalink_query ?></code>
        <input type="text"
               name="<?php echo $this->options_prefix . 'separator' ?>"
               id="<?php echo $this->options_prefix . 'separator' ?>"
               value="<?php echo 'goto' === $this->options->separator ? '' : $this->options->separator ?>" />
        <code><?php echo $this->permalink_equals ?>https://example.com</code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function link_encoding_none_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url')
            ?>/<?php echo $this->permalink_query ?><span class="link-separator"><?php
                echo $this->options->separator ?
                    $this->options->separator :
                    'goto' ?></span><?php echo $this->permalink_equals ?>https://example.com
        </code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function link_encoding_numbers_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url')
            ?>/<?php echo $this->permalink_query ?><span class="link-separator"><?php
                echo $this->options->separator ?
                    $this->options->separator :
                    'goto' ?></span><?php echo $this->permalink_equals ?>123
        </code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_shortening_none_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url')
            ?>/<?php echo $this->permalink_query ?><span class="link-separator"><?php
                echo $this->options->separator ?
                    $this->options->separator :
                    'goto' ?></span><?php echo $this->permalink_equals ?>https://example.com
        </code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_shortening_adfly_cb() {
        ?>
        <code>http://adf.ly/1npeZF</code> &nbsp;
        <?php _e( 'API Key', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'adfly_api_key' ?>"
               id="<?php echo $this->options_prefix . 'adfly_api_key' ?>"
               value="<?php echo $this->options->adfly_api_key ?>" />
        &nbsp;
        <?php _e( 'User ID', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'adfly_user_id' ?>"
               id="<?php echo $this->options_prefix . 'adfly_user_id' ?>"
               value="<?php echo $this->options->adfly_user_id ?>" />
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_shortening_bitly_cb() {
        ?>
        <code>http://bit.ly/2w2V71G</code> &nbsp;
        <?php _e( 'Login', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'bitly_login' ?>"
               id="<?php echo $this->options_prefix . 'bitly_login' ?>"
               value="<?php echo $this->options->bitly_login ?>" />
        &nbsp;
        <?php _e( 'API Key', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'bitly_api_key' ?>"
               id="<?php echo $this->options_prefix . 'bitly_api_key' ?>"
               value="<?php echo $this->options->bitly_api_key ?>" />
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_shortening_linkshrink_cb() {
        ?>
        <code>http://linkshrink.net/721lH9</code> &nbsp;
        <?php _e( 'API Key', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'linkshrink_api_key' ?>"
               id="<?php echo $this->options_prefix . 'linkshrink_api_key' ?>"
               value="<?php echo $this->options->linkshrink_api_key ?>" />
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_shortening_shortest_cb() {
        ?>
        <code>http://destyy.com/q15Xzx</code> &nbsp;
        <?php _e( 'API Key', $this->plugin_name ) ?>
        <input type="text"
               name="<?php echo $this->options_prefix . 'shortest_api_key' ?>"
               id="<?php echo $this->options_prefix . 'shortest_api_key' ?>"
               value="<?php echo $this->options->shortest_api_key ?>" />
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.2.0
     */
    public function link_encoding_aes256_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url')
            ?>/<?php echo $this->permalink_query ?><span class="link-separator"><?php
                echo $this->options->separator ?
                    $this->options->separator :
                    'goto' ?></span><?php echo $this->permalink_equals ?>bpAc0lhj6liv34KXZfvNxpi5VSAPxPbz2g6jbUAAgHM=:N9QaHkKpnpawbSlWgCp1iQ==
        </code>
        <?php if ( ! $this->options->encryption ): ?>
            <p class="description">
                <?php _e( 'Requires OpenSSL (<strong>Recommended</strong>) or Mcrypt (<strong>Deprecated in PHP 7</strong>).', $this->plugin_name ) ?>
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function link_encoding_base64_cb() {
        ?>
        <code>
            <?php echo get_bloginfo('url')
            ?>/<?php echo $this->permalink_query ?><span class="link-separator"><?php
                echo $this->options->separator ?
                    $this->options->separator :
                    'goto' ?></span><?php echo $this->permalink_equals ?>aHR0cHM6Ly9leGFtcGxlLmNvbQ%3D%3D
        </code>
        <?php
    }

    /**
     * Render the masking type settings section.
     *
     * @since  4.0.0
     */
    public function javascript_cb() {
        ?>
        <fieldset>
            <textarea class="large-text code" rows="10" cols="50"
                    name="<?php echo $this->options_prefix . 'redirect_message' ?>"
                    id="<?php echo $this->options_prefix . 'redirect_message' ?>"
                    <?php echo 'javascript' == $this->options->masking_type ?
                        '' : 'readonly' ?>><?php echo $this->options->redirect_message ?></textarea>
            <?php if ('javascript' !== $this->options->masking_type) : ?>
                <p class="description">
                    <?php _e( 'Javascript redirect not selected.', $this->plugin_name ) ?>
                </p>
            <?php endif ?>
        </fieldset>
        <?php
    }

    /**
     * Render the include / exclude description.
     *
     * @since  4.2.0
     */
    public function include_exclude_cb() {
        ?>
        <p>
            <?php _e(
                'You can choose to target specific domains (<em>include</em>), or exclude specific domains
                (<em>exclude</em>), by entering their URLs below. When entering a URL you should include the
                protocol prefix - for example, <strong>https://</strong>google.com or
                <strong>ftp://</strong>microsoft.com. Please note that domains with and without
                "<strong>www</strong>" are considered different, so are "<strong>http://</strong>" and
                "<strong>https://</strong>". If you wish to include or exclude "<strong>pinterest.com</strong>"
                then you may want to specify "<strong>http://pinterest.com</strong>",
                "<strong>http://www.pinterest.com</strong>", "<strong>https://pinterest.com</strong>" and
                "<strong>https://www.pinterest.com</strong>".',
                $this->plugin_name
            ) ?>
        </p>
        <?php
    }

    /**
     * Render the inclusion settings section.
     *
     * @since  4.2.0
     */
    public function include_cb() {
        ?>
        <fieldset>
            <label for="<?php echo $this->options_prefix . 'inclusion_list' ?>">
                <?php _e(
                    'Enter URLs you wish to be masked. One URL per line. All other URLs will be ignored.',
                    $this->plugin_name
                ) ?>
            </label>
            <br>
            <textarea class="large-text code" rows="10" cols="50"
                      name="<?php echo $this->options_prefix . 'inclusion_list' ?>"
                      id="<?php echo $this->options_prefix . 'inclusion_list' ?>"><?php
                echo $this->options->inclusion_list ?></textarea>
        </fieldset>
        <?php
    }

    /**
     * Render the exclusion settings section.
     *
     * @since  4.0.0
     */
    public function exclude_cb() {
        ?>
        <fieldset>
            <label for="<?php echo $this->options_prefix . 'exclusion_list' ?>">
                <?php _e(
                    'Enter URLs you wish to exclude from being masked. One URL per line.
                    <em>Javascript, Magnet, Mailto, Skype and Tel</em> links are all excluded by default.
                    To exclude a full protocol, just add a line for that prefix - for example,
                    "<strong>ftp://</strong>".',
                    $this->plugin_name
                ) ?>
            </label>
            <br>
            <textarea class="large-text code" rows="10" cols="50"
                      name="<?php echo $this->options_prefix . 'exclusion_list' ?>"
                      id="<?php echo $this->options_prefix . 'exclusion_list' ?>"><?php
                echo $this->options->exclusion_list ?></textarea>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'skip_follow' ?>"
                       id="<?php echo $this->options_prefix . 'skip_follow' ?>"
                       value="1"
                       <?php checked( $this->options->skip_follow ); ?> />
                <?php _e( 'Do Not Mask Follow Links', $this->plugin_name ); ?>
            </label>
            <br>
            <label>
                <input type="checkbox"
                       name="<?php echo $this->options_prefix . 'skip_auth' ?>"
                       id="<?php echo $this->options_prefix . 'skip_auth' ?>"
                       value="1"
                       <?php checked( $this->options->skip_auth ); ?> />
                <?php _e( 'Do Not Mask Links When User Logged In', $this->plugin_name ); ?>
            </label>
            <p class="description">
                <?php _e( 'May conflict with caching plugins.', $this->plugin_name ); ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Custom meta box HTML markup.
     *
     * @since  4.0.0
     */
    public function custom_meta_box_markup() {

        global $post;

        wp_nonce_field(basename(__FILE__), "meta-box-nonce");

        $mask = get_post_meta( $post->ID, 'mask_links', true )
            ? get_post_meta( $post->ID, 'mask_links', true ) : 'default';
        ?>

        <div id="mask-links-select">
            <fieldset>
                <legend class="screen-reader-text">Mask Links</legend>
                <input type="radio"
                       name="mask_links"
                       id="mask_links_default"
                       value="default"
                       <?php checked( $mask, 'default' ) ?>>
                <label for="mask_links_default">
                    <?php _e( 'Use default settings', $this->plugin_name ) ?>
                </label>
                <br>
                <input type="radio"
                       name="mask_links"
                       id="mask_links_disabled"
                       value="disabled"
                       <?php checked( $mask, 'disabled' ) ?>>
                <label for="mask_links_disabled">
                    <?php _e( 'Do not mask links', $this->plugin_name ) ?>
                </label>
            </fieldset>
        </div>

        <?php
    }

    /**
     * Adds a custom meta box to the post/page admin panel.
     *
     * @since  4.0.0
     */
    public function add_custom_meta_box() {

        add_meta_box(
            $this->plugin_name . '-meta-box',
            __( 'Link Masking', $this->plugin_name ),
            array( $this, 'custom_meta_box_markup' ),
            array( 'post', 'page' ),
            'side',
            'low',
            null
        );

    }

    /**
     * Saves the custom meta data against a post/page.
     *
     * @since    4.0.0
     * @param    string    $post_id
     * @return   string    $post_id
     */
    public function save_custom_meta_box( $post_id ) {

        if (
            ! isset( $_POST[ 'meta-box-nonce' ] ) ||
            ! wp_verify_nonce( $_POST[ 'meta-box-nonce' ], basename( __FILE__ ) )
        ) {
            return $post_id;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }

        if ( defined( 'DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
            return $post_id;
        }

        $mask_links = 'default';

        if ( isset( $_POST[ 'mask_links' ] ) ) {

            $values = array( 'default', 'disabled' );

            if ( in_array( $_POST[ 'mask_links' ], $values ) ) {
                $mask_links = $_POST[ 'mask_links' ];
            } else {
                // TODO: Return error message
            }

        }

        update_post_meta( $post_id, 'mask_links', $mask_links );

    }

    /**
     * Add admin notices.
     *
     * @since    4.2.0
     */
    public function admin_notices() {

        $page = isset( $_GET['page'] ) ? $_GET['page'] : null;

        if ( 'wp-noexternallinks-settings' === $page || 'wp-noexternallinks' === $page ) {
            if ( $this->options->custom_parser ) {
                add_action( 'admin_notices', array( $this, 'parser_notice' ) );
            }

            if ( 'aes256' === $this->options->link_encoding && 'mcrypt' === $this->options->encryption ) {
                add_action( 'admin_notices', array( $this, 'mcrypt_deprecation_notice' ) );
            }

            if ( false === $this->options->output_buffer ) {
                add_action( 'admin_notices', array( $this, 'output_buffer_notice' ) );
            }
        }

    }

    /**
     * Display custom parser notice.
     *
     * @since    4.1.0
     */
    function parser_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e(
                    '<strong>Custom Parser</strong> is active, some options/settings may not work. We do <strong>not</strong> recommend using this feature!',
                    $this->plugin_name
                ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Display mcrypt deprecation notice.
     *
     * @since    4.2.0
     */
    function mcrypt_deprecation_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e(
                    '<strong>AES-256 Encoding</strong> - mcrypt has been deprecated in favour of OpenSSL.',
                    $this->plugin_name
                ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Display output buffer notice.
     *
     * @since    4.2.1
     */
    function output_buffer_notice() {
        ?>
        <div class="notice notice-warning">
            <p>
                <?php _e(
                    '<strong>Output Buffering</strong> is disabled, <em>Mask All Links</em> will not work. Contact your server administrator to get this feature enabled.',
                    $this->plugin_name
                ); ?>
            </p>
        </div>
        <?php
    }

}
