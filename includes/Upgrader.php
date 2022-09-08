<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Includes
 * @author        mihdan
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

namespace Mihdan\No_External_Links;

/**
 * Class Upgrader.
 */
class Upgrader {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private $options_prefix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name    The name of the plugin.
	 * @param string $version        The version of this plugin.
	 * @param string $options_prefix The options prefix of this plugin.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function __construct( $plugin_name, $version, $options_prefix ) {

		$this->plugin_name    = $plugin_name;
		$this->options_prefix = $options_prefix;

	}

	/**
	 * Runs the upgrade scripts.
	 *
	 * Updates database tables, fields, and data.
	 *
	 * @since        4.0.0
	 * @noinspection PhpUndefinedClassInspection
	 */
	public function upgrade(): void {

		global $wpdb;

		$installed_version = get_option( $this->options_prefix . 'version' );

		if ( false === $installed_version || version_compare( $installed_version, '4.0.0', '<' ) ) {

			$current_options = get_option( 'Main' );

			if ( false !== $current_options ) {

				if ( isset( $current_options['no302'] ) && '1' === $current_options['no302'] ) {
					update_option( $this->options_prefix . 'masking_type', 'javascript' );
				}

				if ( isset( $current_options['disable_mask_links'] ) && '1' === $current_options['disable_mask_links'] ) {
					update_option( $this->options_prefix . 'masking_type', 'no' );
				}

				if ( isset( $current_options['redtime'] ) ) {
					update_option( $this->options_prefix . 'redirect_time', $current_options['redtime'] );
				}

				if ( isset( $current_options['mask_mine'] ) ) {
					if ( '1' === $current_options['mask_mine'] ) {
						update_option( $this->options_prefix . 'mask_links', 'specific' );
						update_option( $this->options_prefix . 'mask_posts_pages', true );
					} else {
						update_option( $this->options_prefix . 'mask_posts_pages', '' );
					}
				}

				if ( isset( $current_options['mask_comment'] ) ) {
					if ( '1' === $current_options['mask_comment'] ) {
						update_option( $this->options_prefix . 'mask_links', 'specific' );
						update_option( $this->options_prefix . 'mask_comments', true );
					} else {
						update_option( $this->options_prefix . 'mask_comments', '' );
					}
				}

				if ( isset( $current_options['mask_author'] ) ) {
					if ( '1' === $current_options['mask_author'] ) {
						update_option( $this->options_prefix . 'mask_links', 'specific' );
						update_option( $this->options_prefix . 'mask_comment_author', true );
					} else {
						update_option( $this->options_prefix . 'mask_comment_author', '' );
					}
				}

				if ( isset( $current_options['mask_rss'] ) ) {
					if ( '1' === $current_options['mask_rss'] ) {
						update_option( $this->options_prefix . 'mask_links', 'specific' );
						update_option( $this->options_prefix . 'mask_rss', true );
					} else {
						update_option( $this->options_prefix . 'mask_rss', '' );
					}
				}

				if ( isset( $current_options['mask_rss_comment'] ) ) {
					if ( '1' === $current_options['mask_rss_comment'] ) {
						update_option( $this->options_prefix . 'mask_links', 'specific' );
						update_option( $this->options_prefix . 'mask_rss_comments', true );
					} else {
						update_option( $this->options_prefix . 'mask_rss_comments', '' );
					}
				}

				if ( isset( $current_options['fullmask'] ) && '1' === $current_options['fullmask'] ) {
					update_option( $this->options_prefix . 'mask_links', 'all' );
				}

				if ( isset( $current_options['add_nofollow'] ) ) {
					if ( '1' === $current_options['add_nofollow'] ) {
						update_option( $this->options_prefix . 'nofollow', true );
					} else {
						update_option( $this->options_prefix . 'nofollow', '' );
					}
				}

				if ( isset( $current_options['add_blank'] ) ) {
					if ( '1' === $current_options['add_blank'] ) {
						update_option( $this->options_prefix . 'target_blank', true );
					} else {
						update_option( $this->options_prefix . 'target_blank', '' );
					}
				}

				if ( isset( $current_options['put_noindex'] ) ) {
					if ( '1' === $current_options['put_noindex'] ) {
						update_option( $this->options_prefix . 'noindex_tag', true );
					} else {
						update_option( $this->options_prefix . 'noindex_tag', '' );
					}
				}

				if ( isset( $current_options['put_noindex_comment'] ) ) {
					if ( '1' === $current_options['put_noindex_comment'] ) {
						update_option( $this->options_prefix . 'noindex_comment', true );
					} else {
						update_option( $this->options_prefix . 'noindex_comment', '' );
					}
				}

				if ( isset( $current_options['LINK_SEP'] ) && 'goto' !== $current_options['LINK_SEP'] ) {
					update_option( $this->options_prefix . 'link_structure', 'custom' );
					update_option( $this->options_prefix . 'separator', $current_options['LINK_SEP'] );
				} else {
					update_option( $this->options_prefix . 'link_structure', 'default' );
					update_option( $this->options_prefix . 'separator', '' );
				}

				if ( isset( $current_options['maskurl'] ) && '1' === $current_options['maskurl'] ) {
					if ( isset( $current_options['base64'] ) && '1' === $current_options['base64'] ) {
						update_option( $this->options_prefix . 'link_encoding', 'none' );
					} else {
						update_option( $this->options_prefix . 'link_encoding', 'numbers' );
					}
				}

				if ( isset( $current_options['base64'] ) && '1' === $current_options['base64'] ) {
					if ( isset( $current_options['maskurl'] ) && '1' === $current_options['maskurl'] ) {
						update_option( $this->options_prefix . 'link_encoding', 'none' );
					} else {
						update_option( $this->options_prefix . 'link_encoding', 'base64' );
					}
				}

				if ( isset( $current_options['stats'] ) ) {
					if ( '1' === $current_options['stats'] ) {
						update_option( $this->options_prefix . 'logging', true );
					} else {
						update_option( $this->options_prefix . 'logging', '' );
					}
				}

				if ( isset( $current_options['keep_stats'] ) ) {
					update_option( $this->options_prefix . 'log_duration', $current_options['keep_stats'] );
				}

				if ( isset( $current_options['restrict_referer'] ) ) {
					if ( '1' === $current_options['restrict_referer'] ) {
						update_option( $this->options_prefix . 'check_referrer', true );
					} else {
						update_option( $this->options_prefix . 'check_referrer', '' );
					}
				}

				if ( isset( $current_options['remove_links'] ) ) {
					if ( '1' === $current_options['remove_links'] ) {
						update_option( $this->options_prefix . 'remove_all_links', true );
					} else {
						update_option( $this->options_prefix . 'remove_all_links', '' );
					}
				}

				if ( isset( $current_options['link2text'] ) ) {
					if ( '1' === $current_options['link2text'] ) {
						update_option( $this->options_prefix . 'links_to_text', true );
					} else {
						update_option( $this->options_prefix . 'links_to_text', '' );
					}
				}

				if ( isset( $current_options['debug'] ) ) {
					if ( '1' === $current_options['debug'] ) {
						update_option( $this->options_prefix . 'debug_mode', true );
					} else {
						update_option( $this->options_prefix . 'debug_mode', '' );
					}
				}

				if (
					isset( $current_options['redtxt'] ) &&
					'This page demonstrates link redirect with "WP-NoExternalLinks" plugin. You will be redirected in 3 seconds. Otherwise, please click on <a href="LINKURL">this link</a>.' !== $current_options['redtxt']
				) {
					$redirect_text = str_replace( 'LINKURL', '%linkurl%', $current_options['redtxt'] );

					update_option( $this->options_prefix . 'redirect_message', $redirect_text );
				}

				if ( isset( $current_options['exclude_links'] ) && '' !== $current_options['exclude_links'] && '0' !== $current_options['exclude_links'] ) {
					update_option( $this->options_prefix . 'exclusion_list', $current_options['exclude_links'] );
				}

				if ( isset( $current_options['noforauth'] ) ) {
					if ( '1' === $current_options['noforauth'] ) {
						update_option( $this->options_prefix . 'skip_auth', true );
					} else {
						update_option( $this->options_prefix . 'skip_auth', '' );
					}
				}

				if ( isset( $current_options['dont_mask_admin_follow'] ) ) {
					if ( '1' === $current_options['dont_mask_admin_follow'] ) {
						update_option( $this->options_prefix . 'skip_follow', true );
					} else {
						update_option( $this->options_prefix . 'skip_follow', '' );
					}
				}

				if ( false !== get_option( 'mihdan_noexternallinks_flush' ) ) {
					update_option(
						$this->options_prefix . 'last_cleared_logs',
						get_option( 'mihdan_noexternallinks_flush' )
					);
				}

				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				$wpdb->update(
					$wpdb->prefix . 'postmeta',
					[ 'meta_value' => 'default' ],
					[
						'meta_key'   => 'mihdan_noextrenallinks_mask_links',
						'meta_value' => 0,
					]
				);

				$wpdb->update(
					$wpdb->prefix . 'postmeta',
					[ 'meta_value' => 'disabled' ],
					[
						'meta_key'   => 'mihdan_noextrenallinks_mask_links',
						'meta_value' => 2,
					]
				);

				$wpdb->update(
					$wpdb->prefix . 'postmeta',
					[ 'meta_key' => 'mask_links' ],
					[ 'meta_key' => 'mihdan_noextrenallinks_mask_links' ]
				);
				// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_value

				// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$table_name     = $wpdb->prefix . 'links_stats';
				$new_table_name = $wpdb->prefix . 'external_links_logs';
				$wpdb->query( "ALTER TABLE $table_name RENAME $new_table_name" );
				$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

				$table_name     = $wpdb->prefix . 'masklinks';
				$new_table_name = $wpdb->prefix . 'external_links_masks';
				$wpdb->query( "ALTER TABLE $table_name RENAME $new_table_name" );
				$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				delete_option( 'Main' );
				delete_option( 'mihdan_noexternallinks_flush' );

				$installed_version = '4.0.0';
				update_option( $this->options_prefix . 'version', $installed_version );

				add_action( 'admin_notices', [ $this, 'update_notice' ] );

			}
		}

		if ( '4.0.0' === $installed_version ) {

			Mihdan_NoExternalLinks_Database::migrate();

			$installed_version = '4.2.0';
			update_option( $this->options_prefix . 'version', $installed_version );

		}

		if ( version_compare( $installed_version, '4.2.0', '<=' ) ) {

			Mihdan_NoExternalLinks_Database::migrate();

			$installed_version = '4.5.1';
			update_option( $this->options_prefix . 'version', $installed_version );

		}

	}

	/**
	 * Display update notice.
	 *
	 * @since    4.0.0
	 */
	public function update_notice(): void {
		?>
		<div class="notice notice-warning is-dismissible">
			<p>
				<?php
				esc_html_e(
					'Mihdan: No External Links Settings have been imported from an older version of the plugin,
                    please double check them!',
					$this->plugin_name
				);
				?>
			</p>
		</div>
		<?php
	}

}
