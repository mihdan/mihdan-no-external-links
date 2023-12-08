<?php
/**
 * Admin specific functionality.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Admin
 * @author        mihdan
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection HttpUrlsUsage */
// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

namespace Mihdan\No_External_Links\Admin;

use const Mihdan\No_External_Links\MIHDAN_NO_EXTERNAL_LINKS_SLUG;
use WP_Plugin_Install_List_Table;

/**
 * Class Admin.
 */
class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private string $version;

	/**
	 * The options of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      object $options The current options of this plugin.
	 */
	private $options;

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private $options_prefix;

	/**
	 * The permalink query symbol.
	 * Will be either a question mark, or a forward slash.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $permalink_query The permalink query symbol.
	 */
	private $permalink_query;

	/**
	 * The permalink equals symbol.
	 * Will be either an equals sign, or a forward slash.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $permalink_equals The permalink equals sign.
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
	 * The class that add Site Health tests.
	 *
	 * @since 4.5.1
	 * @var SiteHealth
	 */
	public $site_health;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name    The name of this plugin.
	 * @param string $version        The version of this plugin.
	 * @param object $options        The current options of this plugin.
	 * @param string $options_prefix The options prefix of this plugin.
	 */
	public function __construct( $plugin_name, $version, $options, $options_prefix ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->options     = $options;

		$this->options_prefix = $options_prefix;

		$this->permalink_query  = get_option( 'permalink_structure' ) ? '' : '?';
		$this->permalink_equals = get_option( 'permalink_structure' ) ? '/' : '=';

		$this->admin_notices();
	}

	/**
	 * Get site health.
	 *
	 * @since 4.5.1
	 */
	public function site_health(): void {
		$this->site_health = new SiteHealth( $this->plugin_name );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/mihdan-noexternallinks-admin.min.css',
			[],
			filemtime( plugin_dir_path( __FILE__ ) . 'css/mihdan-noexternallinks-admin.min.css' ),
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    4.0.0
	 */
	public function enqueue_scripts( $hook ): void {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/mihdan-noexternallinks-admin.min.js',
			[ 'jquery' ],
			filemtime( plugin_dir_path( __FILE__ ) . 'js/mihdan-noexternallinks-admin.min.js', ),
			false
		);

		wp_enqueue_script( 'plugin_install' );
		wp_enqueue_script( 'updates' );
		add_thickbox();

		if ( 'toplevel_page_' . $this->plugin_name === $hook ) {
			$settings = wp_enqueue_code_editor(
				[
					'type' => 'text/html',
				]
			);

			if ( $settings ) {
				wp_add_inline_script(
					'code-editor',
					sprintf( 'jQuery( function() { wp.codeEditor.initialize( "' . $this->options_prefix . 'redirect_message", %s ); } );', wp_json_encode( $settings ) )
				);
			}
		}
	}

	/**
	 * Install non-menu tabs.
	 *
	 * @param array $tabs Tabs.
	 *
	 * @return array
	 */
	public function install_plugins_nonmenu_tabs( $tabs ): array {

		$tabs[] = MIHDAN_NO_EXTERNAL_LINKS_SLUG;

		return $tabs;
	}

	/**
	 * Install table API args.
	 *
	 * @param mixed $args Arguments.
	 *
	 * @return array
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function install_plugins_table_api_args( $args ): array {
		global $paged;

		return [
			'page'     => $paged,
			'per_page' => 100,
			'locale'   => get_user_locale(),
			'author'   => 'mihdan',
		];
	}

	/**
	 * Add No External Links menu and pages.
	 *
	 * @since  4.0.0
	 */
	public function add_admin_pages(): void {

		add_menu_page(
			__( 'No External Links', $this->plugin_name ),
			__( 'No External Links', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_settings_page' ],
			'dashicons-admin-links'
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Mihdan: No External Links Settings', $this->plugin_name ),
			__( 'Settings', $this->plugin_name ),
			'manage_options',
			$this->plugin_name,
			[ $this, 'display_settings_page' ]
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Mihdan: No External Links Masks', $this->plugin_name ),
			__( 'Masks', $this->plugin_name ),
			'manage_options',
			$this->plugin_name . '-masks',
			[ $this, 'display_masks_page' ]
		);

		add_submenu_page(
			$this->plugin_name,
			__( 'Mihdan: No External Links Logs', $this->plugin_name ),
			__( 'Logs', $this->plugin_name ),
			'manage_options',
			$this->plugin_name . '-logs',
			[ $this, 'display_log_page' ]
		);
	}

	/**
	 * Set screen options for the masks page.
	 *
	 * @since     4.2.0
	 *
	 * @param bool|int $status Status.
	 * @param string   $option The option name.
	 * @param bool|int $value  The number of rows to use.
	 *
	 * @return int
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function mask_page_set_screen_options( $status, $option, $value ) {

		return $value;

	}

	/**
	 * Define screen options for the masks page.
	 *
	 * @since    4.2.0
	 */
	public function mask_page_screen_options(): void {

		$option = 'per_page';
		$args   = [
			'label'   => 'Number of items per page:',
			'default' => 20,
			'option'  => 'masks_per_page',
		];

		add_screen_option( $option, $args );

		$this->masks_table = new MaskTable( $this->plugin_name, $this->options_prefix );

		$this->masks_table->process_bulk_action();

	}

	/**
	 * Set screen options for the logs page.
	 *
	 * @since     4.0.0
	 *
	 * @param bool|int $status Status.
	 * @param string   $option The option name.
	 * @param bool|int $value  The number of rows to use.
	 *
	 * @return int
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function log_page_set_screen_options( $status, $option, $value ) {

		return $value;

	}

	/**
	 * Define screen options for the logs page.
	 *
	 * @since    4.0.0
	 */
	public function log_page_screen_options(): void {

		$option = 'per_page';
		$args   = [
			'label'   => 'Number of items per page:',
			'default' => 20,
			'option'  => 'logs_per_page',
		];

		add_screen_option( $option, $args );

		$this->logs_table = new LogTable( $this->plugin_name, $this->options_prefix );

		$this->logs_table->process_bulk_action();

	}

	/**
	 * Render the masks page.
	 *
	 * @since  4.2.0
	 */
	public function display_masks_page(): void {
		include_once 'partials/masks.php';
	}

	/**
	 * Render the logs page.
	 *
	 * @since  4.0.0
	 */
	public function display_log_page(): void {
		include_once 'partials/logs.php';
	}

	/**
	 * Render the settings page.
	 *
	 * @since  4.0.0
	 */
	public function display_settings_page(): void {
		include_once 'partials/settings.php';
	}

	/**
	 * Register plugin settings.
	 *
	 * @since 4.0.0
	 */
	public function register_setting(): void {

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
			[ $this, 'include_exclude_cb' ],
			$this->plugin_name . '-settings-include-exclude'
		);

		add_settings_section(
			$this->options_prefix . 'settings_seo_hide_section',
			'',
			[ $this, 'seo_hide_cb' ],
			$this->plugin_name . '-settings-seo-hide'
		);

		add_settings_field(
			$this->options_prefix . 'masking_type',
			__( 'Masking Type', $this->plugin_name ),
			[ $this, 'masking_type_cb' ],
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
			[ $this, 'mask_cb' ],
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
			[ $this, 'general_cb' ],
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
			$this->options_prefix . 'seo_hide',
			__( 'SEO hide', $this->plugin_name ),
			[ $this, 'checkbox_cb' ],
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'settings_seo_hide_section',
			[
				'name'  => $this->options_prefix . 'seo_hide',
				'id'    => $this->options_prefix . 'seo_hide',
				'value' => $this->options->seo_hide,
				'title' => __( 'Enable', $this->plugin_name ),
			]
		);

		register_setting(
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'seo_hide'
		);

		add_settings_field(
			$this->options_prefix . 'seo_hide_mode_specific',
			__( 'Mode', $this->plugin_name ),
			[ $this, 'radio_cb' ],
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'settings_seo_hide_section',
			[
				'name'    => $this->options_prefix . 'seo_hide_mode',
				'id'      => $this->options_prefix . 'seo_hide_mode_specific',
				'value'   => 'specific',
				'checked' => 'specific' === $this->options->seo_hide_mode,
				'title'   => __( 'Specific links', $this->plugin_name ),
			]
		);

		add_settings_field(
			$this->options_prefix . 'seo_hide_include_list',
			__( 'Include', $this->plugin_name ),
			[ $this, 'textarea_cb' ],
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'settings_seo_hide_section',
			[
				'name'        => $this->options_prefix . 'seo_hide_include_list',
				'id'          => $this->options_prefix . 'seo_hide_include_list',
				'value'       => $this->options->seo_hide_include_list,
				'description' => __( 'Enter domains you wish <b>TO BE</b> masked. One domain per line. All other domain will be ignored.', $this->plugin_name ),
				'class'       => ( 'all' === $this->options->seo_hide_mode )
					? $this->options_prefix . 'seo_hide_mode ' . $this->options_prefix . 'hidden'
					: $this->options_prefix . 'seo_hide_mode',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'seo_hide_include_list'
		);

		add_settings_field(
			$this->options_prefix . 'seo_hide_mode_all',
			__( 'Mode', $this->plugin_name ),
			[ $this, 'radio_cb' ],
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'settings_seo_hide_section',
			[
				'name'    => $this->options_prefix . 'seo_hide_mode',
				'id'      => $this->options_prefix . 'seo_hide_mode_all',
				'value'   => 'all',
				'checked' => 'all' === $this->options->seo_hide_mode,
				'title'   => __( 'All links', $this->plugin_name ),
			]
		);

		register_setting(
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'seo_hide_mode'
		);

		add_settings_field(
			$this->options_prefix . 'seo_hide_exclude_list',
			__( 'Exclude', $this->plugin_name ),
			[ $this, 'textarea_cb' ],
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'settings_seo_hide_section',
			[
				'name'        => $this->options_prefix . 'seo_hide_exclude_list',
				'id'          => $this->options_prefix . 'seo_hide_exclude_list',
				'value'       => $this->options->seo_hide_exclude_list,
				'description' => __( 'Enter domains you wish <b>NOT TO BE</b> masked. One domain per line. All other domain will be ignored.', $this->plugin_name ),
				'class'       => ( 'specific' === $this->options->seo_hide_mode )
					? $this->options_prefix . 'seo_hide_mode ' . $this->options_prefix . 'hidden'
					: $this->options_prefix . 'seo_hide_mode',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-seo-hide',
			$this->options_prefix . 'seo_hide_exclude_list'
		);

		add_settings_field(
			$this->options_prefix . 'logging',
			__( 'Logging', $this->plugin_name ),
			[ $this, 'logging_cb' ],
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
			[ $this, 'anonymize_cb' ],
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
			[ $this, 'bot_targeting_cb' ],
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
			[ $this, 'advanced_cb' ],
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
			[ $this, 'debugging_cb' ],
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
                         ' . checked( $this->options->link_structure, 'default', false ) . ' /> ' . __( 'Default', $this->plugin_name ),
			[ $this, 'link_structure_default_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_structure_section',
			[ 'label_for' => $this->options_prefix . 'link_structure_default' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_structure_custom',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_structure"
                         id="' . $this->options_prefix . 'link_structure_custom"
                         value="custom"
                         ' . checked( $this->options->link_structure, 'custom', false ) . ' /> ' . __( 'Custom', $this->plugin_name ),
			[ $this, 'link_structure_custom_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_structure_section',
			[ 'label_for' => $this->options_prefix . 'link_structure_custom' ]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'link_structure'
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'separator',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		add_settings_field(
			$this->options_prefix . 'link_encoding_none',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_none"
                         value="none"
                         ' . checked( $this->options->link_encoding, 'none', false ) . ' /> ' .
			__( 'None', $this->plugin_name ),
			[ $this, 'link_encoding_none_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_encoding_section',
			[ 'label_for' => $this->options_prefix . 'link_encoding_none' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_encoding_aes256',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_aes256"
                         value="aes256"
                         ' . checked( $this->options->link_encoding, 'aes256', false ) . '
                         ' . disabled( $this->options->encryption, false, false ) . '/> ' .
			__( 'AES-256', $this->plugin_name ),
			[ $this, 'link_encoding_aes256_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_encoding_section',
			[ 'label_for' => $this->options_prefix . 'link_encoding_aes256' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_encoding_base64',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_base64"
                         value="base64"
                         ' . checked( $this->options->link_encoding, 'base64', false ) . ' /> ' .
			__( 'Base64', $this->plugin_name ),
			[ $this, 'link_encoding_base64_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_encoding_section',
			[ 'label_for' => $this->options_prefix . 'link_encoding_base64' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_encoding_numbers',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_encoding"
                         id="' . $this->options_prefix . 'link_encoding_numbers"
                         value="numbers"
                         ' . checked( $this->options->link_encoding, 'numbers', false ) . ' /> ' .
			__( 'Numeric', $this->plugin_name ),
			[ $this, 'link_encoding_numbers_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_links_encoding_section',
			[ 'label_for' => $this->options_prefix . 'link_encoding_numbers' ]
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
                         ' . checked( $this->options->link_shortening, 'none', false ) . ' /> ' .
			__( 'None', $this->plugin_name ),
			[ $this, 'link_shortening_none_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_link_shortening_section',
			[ 'label_for' => $this->options_prefix . 'link_shortening_none' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_shortening_adfly',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_adfly"
                         value="adfly"
                         ' . checked( $this->options->link_shortening, 'adfly', false ) . ' /> ' .
			__( 'Adf.ly', $this->plugin_name ),
			[ $this, 'link_shortening_adfly_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_link_shortening_section',
			[ 'label_for' => $this->options_prefix . 'link_shortening_adfly' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_shortening_bitly',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_bitly"
                         value="bitly"
                         ' . checked( $this->options->link_shortening, 'bitly', false ) . ' /> ' .
			__( 'Bitly', $this->plugin_name ),
			[ $this, 'link_shortening_bitly_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_link_shortening_section',
			[ 'label_for' => $this->options_prefix . 'link_shortening_bitly' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_shortening_shortest',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_shortest"
                         value="shortest"
                         ' . checked( $this->options->link_shortening, 'shortest', false ) . ' /> ' .
			__( 'Shorte.st', $this->plugin_name ),
			[ $this, 'link_shortening_shortest_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_link_shortening_section',
			[ 'label_for' => $this->options_prefix . 'link_shortening_shortest' ]
		);

		add_settings_field(
			$this->options_prefix . 'link_shortening_yourls',
			'<input type="radio"
                         name="' . $this->options_prefix . 'link_shortening"
                         id="' . $this->options_prefix . 'link_shortening_yourls"
                         value="yourls"
                         ' . checked( $this->options->link_shortening, 'yourls', false ) . ' /> ' .
			__( 'Yourls', $this->plugin_name ),
			[ $this, 'link_shortening_yourls_cb' ],
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'settings_link_shortening_section',
			[ 'label_for' => $this->options_prefix . 'link_shortening_yourls' ]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'link_shortening'
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'adfly_api_key',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'adfly_user_id',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'adfly_domain',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'adfly_advert_type',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'bitly_login',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'bitly_api_key',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'shortest_api_key',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'yourls_signature',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		register_setting(
			$this->plugin_name . '-settings-links',
			$this->options_prefix . 'yourls_domain',
			[
				'sanitize_callback' => 'sanitize_text_field',
			]
		);

		add_settings_field(
			$this->options_prefix . 'javascript',
			__( 'Javascript Redirect Text', $this->plugin_name ),
			[ $this, 'javascript_cb' ],
			$this->plugin_name . '-settings-advanced',
			$this->options_prefix . 'settings_advanced_section',
			''
		);

		register_setting(
			$this->plugin_name . '-settings-advanced',
			$this->options_prefix . 'redirect_message'
		);

		add_settings_field(
			$this->options_prefix . 'redirect_page',
			__( 'Redirect Page', $this->plugin_name ),
			[ $this, 'redirect_page' ],
			$this->plugin_name . '-settings-advanced',
			$this->options_prefix . 'settings_advanced_section',
			''
		);

		register_setting(
			$this->plugin_name . '-settings-advanced',
			$this->options_prefix . 'redirect_page'
		);

		add_settings_field(
			$this->options_prefix . 'include',
			__( 'Include', $this->plugin_name ),
			[ $this, 'include_cb' ],
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
			[ $this, 'exclude_cb' ],
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

		add_settings_section(
			$this->options_prefix . 'settings_plugins_section',
			'',
			[ $this, 'plugins_cb' ],
			$this->plugin_name . '-settings-plugins'
		);
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function masking_type_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'masking_type' ); ?>"
					value="no"
					<?php checked( $this->options->masking_type, 'no' ); ?>
				/>
				<?php esc_html_e( 'No Redirect', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'masking_type' ); ?>"
					value="301"
					<?php checked( $this->options->masking_type, '301' ); ?>
				/>
				<?php esc_html_e( '301 (Moved Permanently)', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'masking_type' ); ?>"
					value="302"
					<?php checked( $this->options->masking_type, '302' ); ?>
				/>
				<?php esc_html_e( '302 (Found/Temporary Redirect)', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'masking_type' ); ?>"
					value="307"
					<?php checked( $this->options->masking_type, '307' ); ?> />
				<?php esc_html_e( '307 (Temporary Redirect)', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'masking_type' ); ?>"
					value="javascript"
					<?php checked( $this->options->masking_type, 'javascript' ); ?> />
				<?php esc_html_e( 'Javascript Redirect', $this->plugin_name ); ?>
			</label>
			<ul>
				<li>
					<?php esc_html_e( 'Redirect after', $this->plugin_name ); ?>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'redirect_time' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'redirect_time' ); ?>"
							size="3"
							maxlength="4"
							value="<?php echo $this->options->redirect_time ? absint( $this->options->redirect_time ) : 3; ?>"
							<?php echo 'javascript' === $this->options->masking_type ? '' : 'readonly'; ?> />
						<?php esc_html_e( 'seconds', $this->plugin_name ); ?>
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
	public function mask_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'mask_links' ); ?>"
					value="all"
					<?php checked( $this->options->mask_links, 'all' ); ?>
					<?php disabled( (bool) ini_get( 'output_buffering' ), false ); ?> />
				<?php esc_html_e( 'Mask All Links', $this->plugin_name ); ?>
				&nbsp;
				<strong><small><em>(<?php esc_html_e( 'Recommended', $this->plugin_name ); ?>)</em></small></strong>
			</label>
			<br>
			<label>
				<input
					type="radio"
					name="<?php echo esc_attr( $this->options_prefix . 'mask_links' ); ?>"
					value="specific"
					<?php checked( $this->options->mask_links, 'specific' ); ?> />
				<?php esc_html_e( 'Mask Specific Links (select below)', $this->plugin_name ); ?>
			</label>
			<div class="list-tree">
				<ul>
					<li>
						<label>
							<input
								type="checkbox"
								name="<?php echo esc_attr( $this->options_prefix . 'mask_posts_pages' ); ?>"
								id="<?php echo esc_attr( $this->options_prefix . 'mask_posts_pages' ); ?>"
								value="1"
								<?php checked( $this->options->mask_posts_pages ); ?>
								<?php checked( $this->options->mask_links, 'all' ); ?>
								<?php disabled( $this->options->mask_links, 'all' ); ?> />
							<?php esc_html_e( 'Mask links in posts and pages', $this->plugin_name ); ?>
						</label>
					</li>
					<li>
						<label>
							<input
								type="checkbox"
								name="<?php echo esc_attr( $this->options_prefix . 'mask_comments' ); ?>"
								id="<?php echo esc_attr( $this->options_prefix . 'mask_comments' ); ?>"
								value="1"
								<?php checked( $this->options->mask_comments ); ?>
								<?php checked( $this->options->mask_links, 'all' ); ?>
								<?php disabled( $this->options->mask_links, 'all' ); ?> />
							<?php esc_html_e( 'Mask links in comments', $this->plugin_name ); ?>
						</label>
					</li>
					<li>
						<label>
							<input
								type="checkbox"
								name="<?php echo esc_attr( $this->options_prefix . 'mask_comment_author' ); ?>"
								id="<?php echo esc_attr( $this->options_prefix . 'mask_comment_author' ); ?>"
								value="1"
								<?php checked( $this->options->mask_comment_author ); ?>
								<?php checked( $this->options->mask_links, 'all' ); ?>
								<?php disabled( $this->options->mask_links, 'all' ); ?> />
							<?php esc_html_e( 'Mask comment authors\'s homepage link', $this->plugin_name ); ?>
						</label>
					</li>
				</ul>
			</div>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'mask_rss' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'mask_rss' ); ?>"
					value="1"
					<?php checked( $this->options->mask_rss ); ?> />
				<?php esc_html_e( 'Mask links in your RSS post content', $this->plugin_name ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>_description">
				<?php esc_html_e( 'May result in invalid RSS if used with some masking options.', $this->plugin_name ); ?>
			</p>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'mask_rss_comments' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'mask_rss_comments' ); ?>"
					value="1"
					<?php checked( $this->options->mask_rss_comments ); ?> />
				<?php esc_html_e( 'Mask links in RSS comments', $this->plugin_name ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>_description">
				<?php esc_html_e( 'May result in invalid RSS if used with some masking options.', $this->plugin_name ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function general_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'nofollow' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'nofollow' ); ?>"
					value="1"
					<?php checked( $this->options->nofollow ); ?> />
				<?php esc_html_e( 'No Follow Masked Links', $this->plugin_name ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>_description">
				<?php esc_html_e( 'Add rel="nofollow" to masked links.', $this->plugin_name ); ?>
			</p>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'target_blank' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'target_blank' ); ?>"
					value="1"
					<?php checked( $this->options->target_blank ); ?> />
				<?php esc_html_e( 'Open Masked Links in a New Window', $this->plugin_name ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>_description">
				<?php esc_html_e( 'Add target="_blank" to masked links.', $this->plugin_name ); ?>
			</p>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>"
					value="1"
					<?php checked( $this->options->noindex_tag ); ?> />
				<?php
				esc_html_e(
					'Surround Masked Links with &#x3C;noindex/&#x3E; tags',
					$this->plugin_name
				);
				?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_tag' ); ?>_description">
				<?php esc_html_e( 'For yandex search engine.', $this->plugin_name ); ?>
			</p>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'noindex_comment' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'noindex_comment' ); ?>"
					value="1"
					<?php checked( $this->options->noindex_comment ); ?> />
				<?php
				esc_html_e(
					'Surround Masked Links with &#x3C;!--noindex--&#x3E; comments',
					$this->plugin_name
				);
				?>
			</label>
			<p class="description" id="<?php echo esc_attr( $this->options_prefix . 'noindex_comment' ); ?>_description">
				<?php
				esc_html_e(
					'For yandex search engine, better then noindex tag because valid.',
					$this->plugin_name
				);
				?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Print checkbox.
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function checkbox_cb( $data ): void {
		?>
		<label>
			<input
				type="checkbox"
				name="<?php echo esc_attr( $data['name'] ); ?>"
				id="<?php echo esc_attr( $data['id'] ); ?>"
				value="1"
				<?php checked( $data['value'] ); ?> />
			<?php echo esc_html( $data['title'] ); ?>
		</label>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<p class="description">
				<?php echo esc_html( $data['description'] ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Print radio.
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function radio_cb( $data ): void {
		?>
		<label>
			<input
				type="radio"
				name="<?php echo esc_attr( $data['name'] ); ?>"
				id="<?php echo esc_attr( $data['id'] ); ?>"
				value="<?php echo esc_attr( $data['value'] ); ?>"
				<?php checked( $data['checked'] ); ?> />
			<?php echo esc_html( $data['title'] ); ?>
		</label>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<p class="description">
				<?php echo esc_html( $data['description'] ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Print textarea.
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function textarea_cb( $data ): void {
		?>
		<fieldset>
			<?php if ( ! empty( $data['title'] ) ) : ?>
				<label for="<?php echo esc_attr( $data['id'] ); ?>">
					<?php echo esc_html( $data['title'] ); ?>
				</label>
				<br>
			<?php endif; ?>
			<textarea
				name="<?php echo esc_attr( $data['name'] ); ?>"
				id="<?php echo esc_attr( $data['id'] ); ?>"
				cols="50"
				class="large-text"
				rows="10"><?php echo esc_textarea( $data['value'] ); ?></textarea>
			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="description">
					<?php echo wp_kses_post( $data['description'] ); ?>
				</p>
			<?php endif; ?>
		</fieldset>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function logging_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'logging' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'logging' ); ?>"
					value="1"
					<?php checked( $this->options->logging ); ?> />
				<?php esc_html_e( 'Enable Logging', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<?php esc_html_e( 'Keep logs for ', $this->plugin_name ); ?>
				<input
					type="text"
					name="<?php echo esc_attr( $this->options_prefix . 'log_duration' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'log_duration' ); ?>"
					size="3"
					maxlength="4"
					value="<?php echo $this->options->log_duration >= 0 ? esc_attr( $this->options->log_duration ) : 30; ?>"
					<?php echo $this->options->logging ? '' : 'readonly'; ?> />
				<?php esc_html_e( 'days', $this->plugin_name ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'Set to 0 to keep logs permanently.', $this->plugin_name ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Render to anonymize settings section.
	 *
	 * @since  4.2.0
	 */
	public function anonymize_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'anonymize_links' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'anonymize_links' ); ?>"
					value="1"
					<?php checked( $this->options->anonymize_links ); ?> />
				<?php esc_html_e( 'Enable Anonymous Links', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<?php esc_html_e( 'Anonymizer prefix ', $this->plugin_name ); ?>
				<input
					type="text"
					name="<?php echo esc_attr( $this->options_prefix . 'anonymous_link_provider' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'anonymous_link_provider' ); ?>"
					value="<?php echo esc_attr( $this->options->anonymous_link_provider ); ?>"
					<?php echo $this->options->anonymize_links ? '' : 'readonly'; ?> />
				<code>https://www.example.com</code>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render the bot targeting settings section.
	 *
	 * @since  4.2.0
	 */
	public function bot_targeting_cb(): void {
		?>
		<fieldset>
			<p>
				<label>
					<input
						type="radio"
						name="<?php echo esc_attr( $this->options_prefix . 'bot_targeting' ); ?>"
						id="<?php echo esc_attr( $this->options_prefix . 'bot_targeting' ); ?>"
						value="all"
						<?php checked( $this->options->bot_targeting, 'all' ); ?> />
					<?php esc_html_e( 'Target All Bots', $this->plugin_name ); ?>
				</label>
			</p>
			<p>
				<label>
					<input
						type="radio"
						name="<?php echo esc_attr( $this->options_prefix . 'bot_targeting' ); ?>"
						id="<?php echo esc_attr( $this->options_prefix . 'bot_targeting' ); ?>"
						value="specific"
						<?php checked( $this->options->bot_targeting, 'specific' ); ?> />
					<?php esc_html_e( 'Target Specific Bot(s)', $this->plugin_name ); ?>
				</label>
			</p>
			<ul>
				<li>
					<label for="">
						<?php esc_html_e( 'Bot(s)', $this->plugin_name ); ?>
						<select name="<?php echo esc_attr( $this->options_prefix . 'bots_selector[]' ); ?>"
								id="<?php echo esc_attr( $this->options_prefix . 'bots_selector' ); ?>"
								multiple
							<?php disabled( $this->options->bot_targeting, 'all' ); ?>>
							<option value="aolspider"
								<?php
								selected( in_array( 'aolspider', $this->options->bots_selector, true ) )
								?>
							>AOL
							</option>
							<option value="askbot"
								<?php
								selected( in_array( 'askbot', $this->options->bots_selector, true ) )
								?>
							>Ask
							</option>
							<option value="baiduspider"
								<?php
								selected( in_array( 'baiduspider', $this->options->bots_selector, true ) )
								?>
							>Baidu
							</option>
							<option value="bingbot"
								<?php
								selected( in_array( 'bingbot', $this->options->bots_selector, true ) )
								?>
							>Bing
							</option>
							<option value="duckduckbot"
								<?php
								selected( in_array( 'duckduckbot', $this->options->bots_selector, true ) )
								?>
							>DuckDuckGo
							</option>
							<option value="googlebot"
								<?php
								selected( in_array( 'googlebot', $this->options->bots_selector, true ) )
								?>
							>Google
							</option>
							<option value="yahoobot"
								<?php
								selected( in_array( 'yahoobot', $this->options->bots_selector, true ) )
								?>
							>Yahoo
							</option>
							<option value="yandexbot"
								<?php
								selected( in_array( 'yandexbot', $this->options->bots_selector, true ) )
								?>
							>Yandex
							</option>
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
	public function advanced_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'check_referrer' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'check_referrer' ); ?>"
					value="1"
					<?php checked( $this->options->check_referrer ); ?> />
				<?php esc_html_e( 'Check Referrer', $this->plugin_name ); ?>
			</label>
			<p class="description">
				<?php
				esc_html_e(
					'Check for document referer and restrict redirect if it is not your own website.
                              Useful against spoofing attacks.',
					$this->plugin_name
				)
				?>
			</p>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'remove_all_links' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'remove_all_links' ); ?>"
					value="1"
					<?php checked( $this->options->remove_all_links ); ?> />
				<?php esc_html_e( 'Remove All Links', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'links_to_text' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'links_to_text' ); ?>"
					value="1"
					<?php checked( $this->options->links_to_text ); ?> />
				<?php esc_html_e( 'Convert All Links to Text', $this->plugin_name ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function debugging_cb(): void {
		?>
		<fieldset>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'debug_mode' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'debug_mode' ); ?>"
					value="1"
					<?php checked( $this->options->debug_mode ); ?> />
				<?php esc_html_e( 'Enable Debug Mode', $this->plugin_name ); ?>
			</label>
			<p class="description">
				<?php
				esc_html_e(
					'Adds comment lines like
                              <code>&#x3C;!--mihdan-noexternallinks debug: some info--&#x3E;</code> to output.',
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
	public function link_structure_default_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_html( $this->permalink_query ); ?>goto<?php echo esc_html( $this->permalink_equals ); ?>https://example.com
		</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function link_structure_custom_cb(): void {
		?>
		<code><?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_html( $this->permalink_query ); ?></code>
		<label>
			<input
				type="text"
				name="<?php echo esc_attr( $this->options_prefix . 'separator' ); ?>"
				id="<?php echo esc_attr( $this->options_prefix . 'separator' ); ?>"
				value="<?php echo 'goto' === $this->options->separator ? 'goto' : esc_attr( $this->options->separator ); ?>"/>
		</label>
		<code><?php echo esc_attr( $this->permalink_equals ); ?>https://example.com</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function link_encoding_none_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_attr( $this->permalink_query ); ?>
			<span class="link-separator">
				<?php echo $this->options->separator ? esc_html( $this->options->separator ) : 'goto'; ?>
			</span>
			<?php echo esc_attr( $this->permalink_equals ); ?>https://example.com
		</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function link_encoding_numbers_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_attr( $this->permalink_query ); ?>
			<span class="link-separator">
				<?php echo $this->options->separator ? esc_html( $this->options->separator ) : 'goto'; ?>
			</span>
			<?php echo esc_attr( $this->permalink_equals ); ?>123
		</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_shortening_none_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_attr( $this->permalink_query ); ?>
			<span class="link-separator">
				<?php echo $this->options->separator ? esc_html( $this->options->separator ) : 'goto'; ?>
			</span>
			<?php echo esc_attr( $this->permalink_equals ); ?>https://example.com
		</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_shortening_adfly_cb(): void {
		?>
		<table>
			<tr>
				<td style="width: 100%;"><?php esc_html_e( 'API Key', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'adfly_api_key' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'adfly_api_key' ); ?>"
							value="<?php echo esc_attr( $this->options->adfly_api_key ); ?>"/>
					</label>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'User ID', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'adfly_user_id' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'adfly_user_id' ); ?>"
							value="<?php echo esc_attr( $this->options->adfly_user_id ); ?>"/>
					</label>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Domain', $this->plugin_name ); ?></td>
				<td>
					<label>
						<select
							name="<?php echo esc_attr( $this->options_prefix . 'adfly_domain' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'adfly_domain' ); ?>">
							<option
								value="adf.ly" <?php selected( $this->options->adfly_domain, 'adf.ly' ); ?>><?php esc_html_e( 'adf.ly', $this->plugin_name ); ?></option>
							<option
								value="q.gs" <?php selected( $this->options->adfly_domain, 'q.gs' ); ?>><?php esc_html_e( 'q.gs', $this->plugin_name ); ?></option>
							<option
								value="j.gs" <?php selected( $this->options->adfly_domain, 'j.gs' ); ?>><?php esc_html_e( 'j.gs', $this->plugin_name ); ?></option>
							<option
								value="random" <?php selected( $this->options->adfly_domain, 'random' ); ?>><?php esc_html_e( 'random', $this->plugin_name ); ?></option>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Advert Type', $this->plugin_name ); ?></td>
				<td>
					<label>
						<select
							name="<?php echo esc_attr( $this->options_prefix . 'adfly_advert_type' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'adfly_advert_type' ); ?>">
							<option
								value="2" <?php selected( $this->options->adfly_advert_type, '2' ); ?>><?php esc_html_e( 'No advertising', $this->plugin_name ); ?></option>
							<option
								value="3" <?php selected( $this->options->adfly_advert_type, '3' ); ?>><?php esc_html_e( 'Framed banner', $this->plugin_name ); ?></option>
							<option
								value="1" <?php selected( $this->options->adfly_advert_type, '1' ); ?>><?php esc_html_e( 'Interstitial advertising', $this->plugin_name ); ?></option>
						</select>
					</label>
				</td>
			</tr>
		</table>
		<code>https://adf.ly/1npeZF</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_shortening_bitly_cb(): void {
		?>
		<table>
			<tr>
				<td style="width: 100%;"><?php esc_html_e( 'Login', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'bitly_login' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'bitly_login' ); ?>"
							value="<?php echo esc_attr( $this->options->bitly_login ); ?>"/>
					</label>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'API Key', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'bitly_api_key' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'bitly_api_key' ); ?>"
							value="<?php echo esc_attr( $this->options->bitly_api_key ); ?>"/>
					</label>
				</td>
			</tr>
		</table>
		<code>https://bit.ly/2w2V71G</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_shortening_shortest_cb(): void {
		?>
		<table>
			<tr>
				<td style="width: 100%;"><?php esc_html_e( 'API Key', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'shortest_api_key' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'shortest_api_key' ); ?>"
							value="<?php echo esc_attr( $this->options->shortest_api_key ); ?>"/>
					</label>
				</td>
			</tr>
		</table>
		<code>https://destyy.com/q15Xzx</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_shortening_yourls_cb(): void {
		?>
		<table>
			<tr>
				<td style="width: 100%;"><?php esc_html_e( 'Domain', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'yourls_domain' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'yourls_domain' ); ?>"
							value="<?php echo esc_attr( $this->options->yourls_domain ); ?>"/>
					</label>
				</td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Signature', $this->plugin_name ); ?></td>
				<td>
					<label>
						<input
							type="text"
							name="<?php echo esc_attr( $this->options_prefix . 'yourls_signature' ); ?>"
							id="<?php echo esc_attr( $this->options_prefix . 'yourls_signature' ); ?>"
							value="<?php echo esc_attr( $this->options->yourls_signature ); ?>"/>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.2.0
	 */
	public function link_encoding_aes256_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_attr( $this->permalink_query ); ?>
			<span class="link-separator">
				<?php echo $this->options->separator ? esc_html( $this->options->separator ) : 'goto'; ?>
			</span>
			<?php echo esc_html( $this->permalink_equals ); ?>
			bpAc0lhj6liv34KXZfvNxpi5VSAPxPbz2g6jbUAAgHM=:N9QaHkKpnpawbSlWgCp1iQ==
		</code>
		<?php
		if ( ! $this->options->encryption ) {
			?>
			<p class="description">
				<?php esc_html_e( 'Requires OpenSSL (Recommended) or Mcrypt (Deprecated in PHP 7).', $this->plugin_name ); ?>
			</p>
			<?php
		}
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function link_encoding_base64_cb(): void {
		?>
		<code>
			<?php echo esc_url( get_bloginfo( 'url' ) ); ?>/<?php echo esc_attr( $this->permalink_query ); ?>
			<span class="link-separator">
				<?php echo $this->options->separator ? esc_html( $this->options->separator ) : 'goto'; ?>
			</span>
			<?php echo esc_html( $this->permalink_equals ); ?>aHR0cHM6Ly9leGFtcGxlLmNvbQ%3D%3D
		</code>
		<?php
	}

	/**
	 * Render the masking type settings section.
	 *
	 * @since  4.0.0
	 */
	public function javascript_cb(): void {
		?>
		<fieldset>
			<?php if ( 'javascript' === $this->options->masking_type ) : ?>
				<label>
					<textarea
						class="large-text code" rows="10" cols="50"
						name="<?php echo esc_attr( $this->options_prefix . 'redirect_message' ); ?>"
						id="<?php echo esc_attr( $this->options_prefix . 'redirect_message' ); ?>"
					><?php echo esc_textarea( $this->options->redirect_message ); ?></textarea>
				</label>
			<?php else : ?>
				<p class="description">
					<?php esc_html_e( 'Javascript redirect not selected.', $this->plugin_name ); ?>
				</p>
			<?php endif ?>
		</fieldset>
		<?php
	}

	/**
	 * Render the redirect page settings section.
	 *
	 * @since  5.0.4
	 */
	public function redirect_page(): void {
		$pages = get_pages();
		?>
		<fieldset>
			<?php if ( 'javascript' === $this->options->masking_type ) : ?>
				<label>
					<select
						name="<?php echo esc_attr( $this->options_prefix . 'redirect_page' ); ?>"
						id="<?php echo esc_attr( $this->options_prefix . 'redirect_page' ); ?>"
					>
						<option value="0">
							<?php esc_html_e( 'Select Page', $this->plugin_name ); ?>
						</option>
						<?php foreach ( $pages as $page ) : ?>
							<option
								value="<?php echo absint( $page->ID ); ?>"
								<?php selected( $this->options->redirect_page, $page->ID ); ?>
							><?php echo esc_html( $page->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			<?php else : ?>
				<p class="description">
					<?php esc_html_e( 'Javascript redirect not selected.', $this->plugin_name ); ?>
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
	public function include_exclude_cb(): void {
		?>
		<p>
			<?php
			esc_html_e(
				'You can choose to target specific domains (include), or exclude specific domains
                (exclude), by entering their URLs below. When entering a URL you should include the
                protocol prefix - for example, https://google.com or
                ftp://microsoft.com. Please note that domains with and without
                "www" are considered different, so are "http://" and
                "https://". If you wish to include or exclude "pinterest.com"
                then you may want to specify "https://pinterest.com",
                "http://www.pinterest.com", "https://pinterest.com" and
                "https://www.pinterest.com".',
				$this->plugin_name
			);
			?>
		</p>
		<?php
	}

	/**
	 * Callback for plugins page.
	 *
	 * @return void
	 */
	public function plugins_cb(): void {
		$transient = MIHDAN_NO_EXTERNAL_LINKS_SLUG . '-plugins';
		$cached    = get_transient( $transient );

		if ( false !== $cached ) {
			echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			return;
		}

		ob_start();
		require_once ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php';
		$_POST['tab'] = MIHDAN_NO_EXTERNAL_LINKS_SLUG;
		$table        = new WP_Plugin_Install_List_Table();
		$table->prepare_items();

		$table->display();

		$content = ob_get_clean();
		set_transient( $transient, $content, DAY_IN_SECONDS );

		echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the inclusion settings section.
	 *
	 * @since  4.2.0
	 */
	public function include_cb(): void {
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $this->options_prefix . 'inclusion_list' ); ?>">
				<?php
				esc_html_e(
					'Enter URLs you wish to be masked. One URL per line. All other URLs will be ignored.',
					$this->plugin_name
				);
				?>
			</label>
			<br>
			<label>
			<textarea
				class="large-text code" rows="10" cols="50"
				name="<?php echo esc_attr( $this->options_prefix . 'inclusion_list' ); ?>"
				id="<?php echo esc_attr( $this->options_prefix . 'inclusion_list' ); ?>">
				<?php echo esc_attr( $this->options->inclusion_list ); ?>
			</textarea>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render the exclusion settings section.
	 *
	 * @since  4.0.0
	 */
	public function exclude_cb(): void {
		?>
		<fieldset>
			<label for="<?php echo esc_attr( $this->options_prefix . 'exclusion_list' ); ?>">
				<?php
				esc_html_e(
					'Enter URLs you wish to exclude from being masked. One URL per line.
                    Javascript, Magnet, Mailto, Skype and Tel links are all excluded by default.
                    To exclude a full protocol, just add a line for that prefix - for example,
                    "ftp://".',
					$this->plugin_name
				);
				?>
			</label>
			<br>
			<label>
			<textarea
				class="large-text code" rows="10" cols="50"
				name="<?php echo esc_attr( $this->options_prefix . 'exclusion_list' ); ?>"
				id="<?php echo esc_attr( $this->options_prefix . 'exclusion_list' ); ?>">
				<?php echo esc_attr( $this->options->exclusion_list ); ?>
			</textarea>
			</label>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'skip_follow' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'skip_follow' ); ?>"
					value="1"
					<?php checked( $this->options->skip_follow ); ?> />
				<?php esc_html_e( 'Do Not Mask Follow Links', $this->plugin_name ); ?>
			</label>
			<br>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $this->options_prefix . 'skip_auth' ); ?>"
					id="<?php echo esc_attr( $this->options_prefix . 'skip_auth' ); ?>"
					value="1"
					<?php checked( $this->options->skip_auth ); ?> />
				<?php esc_html_e( 'Do Not Mask Links When User Logged In', $this->plugin_name ); ?>
			</label>
			<p class="description">
				<?php esc_html_e( 'May conflict with caching plugins.', $this->plugin_name ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Render the SEO hide settings section.
	 *
	 * @since  4.7.4
	 */
	public function seo_hide_cb(): void {
		?>
		<p>Hiding links using SEO hide method.</p>
		<?php
	}

	/**
	 * Custom meta box HTML markup.
	 *
	 * @since  4.0.0
	 */
	public function custom_meta_box_markup(): void {

		global $post;

		wp_nonce_field( basename( __FILE__ ), 'meta-box-nonce' );

		$mask = get_post_meta( $post->ID, 'mask_links', true ) ?: 'default';
		?>

		<div id="mask-links-select">
			<fieldset>
				<legend class="screen-reader-text">Mask Links</legend>
				<input
					type="radio"
					name="mask_links"
					id="mask_links_default"
					value="default"
					<?php checked( $mask, 'default' ); ?>>
				<label for="mask_links_default">
					<?php esc_html_e( 'Use default settings', $this->plugin_name ); ?>
				</label>
				<br>
				<input
					type="radio"
					name="mask_links"
					id="mask_links_disabled"
					value="disabled"
					<?php checked( $mask, 'disabled' ); ?>>
				<label for="mask_links_disabled">
					<?php esc_html_e( 'Do not mask links', $this->plugin_name ); ?>
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
	public function add_custom_meta_box(): void {

		add_meta_box(
			$this->plugin_name . '-meta-box',
			__( 'Link Masking', $this->plugin_name ),
			[ $this, 'custom_meta_box_markup' ],
			[ 'post', 'page' ],
			'side',
			'low',
			null
		);

	}

	/**
	 * Saves the custom meta data against a post/page.
	 *
	 * @since    4.0.0
	 *
	 * @param string $post_id Post ID.
	 *
	 * @return string
	 */
	public function save_custom_meta_box( $post_id ): string {

		if (
			! isset( $_POST['meta-box-nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['meta-box-nonce'] ) ), basename( __FILE__ ) )
		) {
			return $post_id;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		$mask_links = 'default';

		if ( isset( $_POST['mask_links'] ) ) {

			$values = [ 'default', 'disabled' ];

			if ( in_array( $_POST['mask_links'], $values, true ) ) {
				$mask_links = sanitize_text_field( wp_unslash( $_POST['mask_links'] ) );
			}
			// TODO: Return error message if not in the array.

		}

		update_post_meta( $post_id, 'mask_links', $mask_links );

		return $post_id;
	}

	/**
	 * Add admin notices.
	 *
	 * @since    4.2.0
	 */
	public function admin_notices(): void {

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : null;

		if ( 'mihdan-no-external-links-settings' === $page || 'mihdan-no-external-links' === $page ) {
			if ( 'aes256' === $this->options->link_encoding && 'mcrypt' === $this->options->encryption ) {
				add_action( 'admin_notices', [ $this, 'mcrypt_deprecation_notice' ] );
			}

			if ( false === (bool) ini_get( 'output_buffering' ) ) {
				add_action( 'admin_notices', [ $this, 'output_buffer_notice' ] );
			}
		}

	}

	/**
	 * Display mcrypt deprecation notice.
	 *
	 * @since    4.2.0
	 */
	public function mcrypt_deprecation_notice(): void {
		?>
		<div class="notice notice-warning">
			<p>
				<?php
				esc_html_e(
					'AES-256 Encoding - mcrypt has been deprecated in favour of OpenSSL.',
					$this->plugin_name
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Display output buffer notice.
	 *
	 * @since    4.2.1
	 */
	public function output_buffer_notice(): void {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				esc_html_e(
					'Output Buffering is disabled, Mask All Links will not work. Contact your server administrator to get this feature enabled.',
					$this->plugin_name
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add plugin action links
	 *
	 * @param array  $actions     Default actions.
	 * @param string $plugin_file Plugin file.
	 *
	 * @return array
	 */
	public function add_settings_link( $actions, $plugin_file ): array {
		if ( MIHDAN_NO_EXTERNAL_LINKS_BASENAME === $plugin_file ) {
			$actions[] = sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=' . MIHDAN_NO_EXTERNAL_LINKS_SLUG ),
				esc_html__( 'Settings', 'mihdan-no-external-links' )
			);
		}

		return $actions;
	}
}
