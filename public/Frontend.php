<?php
/**
 * Public facing specific functionality.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/public
 * @author        mihdan
 */

// phpcs:ignore Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpMultipleClassDeclarationsInspection */
// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain
// phpcs:disable PHPCompatibility.Constants.RemovedConstants.mcrypt_mode_ecbDeprecatedRemoved
// phpcs:disable PHPCompatibility.Constants.RemovedConstants.mcrypt_randDeprecatedRemoved
// phpcs:disable PHPCompatibility.Constants.RemovedConstants.mcrypt_rijndael_256DeprecatedRemoved
// phpcs:disable PHPCompatibility.Constants.RemovedConstants.mcrypt_dev_randomDeprecatedRemoved
// phpcs:disable PHPCompatibility.Extensions.RemovedExtensions.mcryptDeprecatedRemoved
// phpcs:disable PHPCompatibility.FunctionUse.RemovedFunctions.mcrypt_create_ivDeprecatedRemoved
// phpcs:disable PHPCompatibility.FunctionUse.RemovedFunctions.mcrypt_decryptDeprecatedRemoved
// phpcs:disable PHPCompatibility.FunctionUse.RemovedFunctions.mcrypt_encryptDeprecatedRemoved
// phpcs:disable PHPCompatibility.FunctionUse.RemovedFunctions.mcrypt_get_iv_sizeDeprecatedRemoved

namespace Mihdan\No_External_Links;

use JsonException;
use stdClass;
use WP_Post;

/**
 * Class Frontend
 */
class Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The options of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      object $options The current options of this plugin.
	 */
	private $options;

	/**
	 * The exclusion list of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string[] $version The exclusion list of this plugin.
	 */
	private $exclusion_list;

	/**
	 * A data layer to store miscellaneous data.
	 *
	 * @since    4.2.0
	 * @access   private
	 * @var      mixed $data A data layer to store miscellaneous data.
	 */
	private $data;

	/**
	 * Debug log.
	 *
	 * @var array
	 */
	private array $debug_log;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 * @param object $options     The current options of this plugin.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function __construct( $plugin_name, $version, $options ) {

		$this->plugin_name = $plugin_name;
		$this->options     = $options;

		$this->setup_hooks();
		$this->initiate();
	}

	/**
	 * Setup hooks.
	 *
	 * @return void
	 */
	public function setup_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Start full page filtering.
	 *
	 * @since    4.2.0
	 */
	public function fullpage_filter(): void {

		if ( defined( 'DOING_CRON' ) ) {
			// Do not try to use output buffering on cron.
			return;
		}

		ob_start( [ $this, 'ob_filter' ] );

	}

	/**
	 * Output buffering function.
	 *
	 * @since     4.2.1
	 *
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public function ob_filter( $content ): string {

		global $post;

		if ( $content ) {
			$content = (string) preg_replace( '/(<body[^>]*?>)/', '$1' . $this->data->buffer, $content );

			if (
				is_object( $post ) &&
				function_exists( 'is_feed' ) &&
				get_post_meta( $post->ID, 'mask_links', true ) !== 'disabled' &&
				! is_feed()
			) {
				// Excludes custom redirect page.
				if ( $this->options->redirect_page > 0 && $post->ID === (int) $this->options->redirect_page ) {
					return $content;
				} else {
					$content = $this->filter( $content );
				}
			}
		}

		return $content;

	}

	/**
	 * Check if post/page should be masked.
	 *
	 * @since     4.0.0
	 *
	 * @param string $content Content.
	 *
	 * @return string mixed
	 */
	public function check_post( $content ): string {

		global $post;

		if ( ! $post instanceof WP_Post ) {
			return $content;
		}

		$this->debug_info( 'Checking post for meta.' );

		$content = $this->data->before . $content . $this->data->after;

		if ( 'disabled' === get_post_meta( $post->ID, 'mask_links', true ) ) {
			$this->debug_info( 'Meta nomask. No masking will be applied' );

			return $content;
		}

		if ( $this->options->redirect_page > 0 && $post->ID === (int) $this->options->redirect_page ) {
			$this->debug_info( 'Missing custom redirect page' );

			return $content;
		}

		$this->debug_info( 'Filter will be applied' );

		return $this->filter( $content );
	}

	/**
	 * Filters content to find links.
	 *
	 * @since     4.0.0
	 *
	 * @param string $content Content.
	 *
	 * @return    string
	 */
	public function filter( $content ): string {

		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return $content;
		}

		$this->debug_info( "Processing text: \n" . str_replace( '-->', '--&gt;', $content ) );

		if (
			function_exists( 'is_feed' ) &&
			! $this->options->mask_rss && ! $this->options->mask_rss_comments &&
			is_feed()
		) {
			$this->debug_info( 'It is feed, no processing' );

			return $content;
		}

		$pattern = '/<a (.*?)href=[\"\'](.*?)[\"\'](.*?)>(.*?)<\/a>/si';

		$content = preg_replace_callback( $pattern, [ $this, 'parser' ], $content, - 1, $count );

		$this->debug_info( $count . " replacements done.\nFilter returned: \n" . str_replace( '-->', '--&gt;', $content ) );

		return $content;

	}

	/**
	 * Determines whether to mask a link or not.
	 *
	 * @since     4.0.0
	 *
	 * @param array $matches Matches.
	 *
	 * @return string
	 * @noinspection MultiAssignmentUsageInspection
	 */
	public function parser( $matches ): string {

		$anchor = $matches[0];
		$href   = $matches[2];

		// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
		$this->debug_info( 'Parser called. Parsing argument {' . var_export( $matches, 1 ) . "}\nAgainst link {" . $href . "}\n " );

		if ( preg_match( '/ rel=[\"\']exclude[\"\']/i', $anchor, $match ) ) {
			$this->exclusion_list[] = $href;

			return str_replace( $match[0], '', $anchor );
		}

		if ( '' !== $this->options->inclusion_list ) {
			$inclusion_list = explode( "\r\n", $this->options->inclusion_list );

			foreach ( $inclusion_list as $inclusion_url ) {
				if ( strpos( $href, $inclusion_url ) === 0 ) {
					return $this->mask_link( $matches );
				}
			}
		} else {
			$this->debug_info( 'Checking link "' . $href . '" VS exclusion list {' . var_export( $this->exclusion_list, 1 ) . '}' );

			foreach ( $this->exclusion_list as $exclusion_url ) {
				if ( stripos( $href, $exclusion_url ) === 0 ) {
					$this->debug_info( 'In exclusion list (' . $exclusion_url . '), not masking...' );

					return $matches[0];
				}
			}

			$this->debug_info( 'Not in exclusion list, masking...' );

			return $this->mask_link( $matches );
		}

		// phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_var_export

		return $matches[0];

	}

	/**
	 * Performs link masking functionality.
	 *
	 * @since     4.2.0
	 *
	 * @param array $matches Matches.
	 *
	 * @return string $anchor
	 * @noinspection MultiAssignmentUsageInspection
	 */
	public function mask_link( $matches ): string {

		global $wp_rewrite;

		$anchor      = $matches[0];
		$anchor_text = $matches[4];
		$attributes  = trim( $matches[1] ) . ' ' . trim( $matches[3] );
		$url         = $matches[2];

		if ( 'all' !== $this->options->bot_targeting && ! in_array( $this->data->user_agent_name, $this->options->bots_selector, true ) ) {
			$this->debug_info( 'User agent targeting does not match, not masking it.' );

			return $anchor;
		}

		if ( $this->options->skip_follow ) {
			if ( preg_match( '/rel=[\"\'].*?(?<!no)follow.*?[\"\']/i', $anchor ) ) {
				$this->debug_info( 'This link has a follow attribute not masking it.' );

				return $anchor;
			}

			$this->debug_info( 'it does not have rel follow, masking it.' );
		}

		$separator = $wp_rewrite->using_permalinks() ? $this->options->separator . '/' : '?' . $this->options->separator . '=';
		$blank     = $this->options->target_blank ? ' target="_blank"' : '';
		$nofollow  = $this->options->nofollow ? ' rel="nofollow"' : '';

		if ( $this->options->seo_hide ) {
			// Get classes.
			$classes = 'waslinkname';

			preg_match( '/class="([^"]+)"/si', $attributes, $maybe_classes );

			if ( ! empty( $maybe_classes[1] ) ) {
				$classes .= ' ' . $maybe_classes[1];
			}

			// Получает доменное имя из URL.
			$current_domain = $this->get_domain_from_url( $url );

			// Список включений.
			$seo_hide_include_list = $this->textarea_to_array( $this->options->seo_hide_include_list );

			// Список исключений.
			$seo_hide_exclude_list = $this->textarea_to_array( $this->options->seo_hide_exclude_list );

			// Маскировать только указанные ссылки.
			if ( 'specific' === $this->options->seo_hide_mode ) {
				if ( in_array( $current_domain, $seo_hide_include_list, true ) ) {
					return sprintf(
						'<span class="%s" data-link="%s"%s>%s</span>',
						esc_attr( $classes ),
						esc_attr( base64_encode( $url ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						str_replace( 'target', 'data-target', $blank ),
						$anchor_text
					);
				}
			}

			// Маскировать все ссылки, кроме указанных.
			if ( 'all' === $this->options->seo_hide_mode ) {
				if ( ! in_array( $current_domain, $seo_hide_exclude_list, true ) ) {
					return sprintf(
						'<span class="%s" data-link="%s"%s>%s</span>',
						esc_attr( $classes ),
						esc_attr( base64_encode( $url ) ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
						str_replace( 'target', 'data-target', $blank ),
						$anchor_text
					);
				}
			}
		}

		if ( 'none' !== $this->options->link_shortening ) {
			$url = $this->shorten_link( $url );

			$this->exclusion_list[] = $url;

			return '<a' . $blank . $nofollow . ' href="' . $url . '" ' . $attributes . '>' . $anchor_text . '</a>';
		}

		if ( 'no' !== $this->options->masking_type ) {
			$url = $this->encode_link( $url );

			if ( ! $wp_rewrite->using_permalinks() ) {
				$url = rawurlencode( $url );
			}

			$url = trim( $this->data->site, '/' ) . '/' . $separator . $url;
		}

		if ( $this->options->remove_all_links ) {
			return '<span class="waslinkname">' . $anchor_text . '</span>';
		}

		if ( $this->options->links_to_text ) {
			return '<span class="waslinkname">' . $anchor_text . '</span> ^(<span class="waslinkurl">' . $url . ')</span>';
		}

		$anchor = '<a' . $blank . $nofollow . ' href="' . $url . '" ' . $attributes . '>' . $anchor_text . '</a>';

		if ( $this->options->noindex_tag ) {
			$anchor = '<noindex>' . $anchor . '</noindex>';
		}

		if ( $this->options->noindex_comment ) {
			$anchor = '<!--noindex-->' . $anchor . '<!--/noindex-->';
		}

		return $anchor;

	}

	/**
	 * Convert textarea value to PHP array.
	 *
	 * @param string $field Textarea value.
	 *
	 * @return array
	 */
	private function textarea_to_array( $field ): array {
		return array_map(
			'trim',
			(array) explode( PHP_EOL, trim( $field ) )
		);
	}

	/**
	 * Get domain name from absolute URL.
	 *
	 * @param string $url Given URL.
	 *
	 * @return string
	 */
	private function get_domain_from_url( string $url ): string {
		return (string) wp_parse_url( $url, PHP_URL_HOST );
	}

	/**
	 * Checks if current page is a redirect page.
	 *
	 * @since    4.0.0
	 */
	public function check_redirect(): void {
		$goto = '';
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$p    = strpos( $uri, '/' . $this->options->separator . '/' );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$sep = isset( $_REQUEST[ $this->options->separator ] ) ?
			sanitize_key( wp_unslash( $_REQUEST[ $this->options->separator ] ) ) :
			'';
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( $sep ) {
			$goto = $sep;
		} elseif ( false !== $p ) {
			$goto = substr( $uri, $p + strlen( $this->options->separator ) + 2 );
		}

		$goto = wp_strip_all_tags( $goto );

		if ( ! empty( $goto ) ) {
			$this->redirect( $goto );
		}

	}

	/**
	 * Initiates the redirect.
	 *
	 * @since    4.0.0
	 *
	 * @param string $url Url.
	 */
	public function redirect( $url ): void {

		global $wp_query, $wp_rewrite, $hyper_cache_stop;

		// Disable Hyper Cache plugin (http://www.satollo.net/plugins/hyper-cache) from caching this page.
		$hyper_cache_stop = true;

		// Disable WP Super Cache, WP Rocket caching.
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', 1 );
		}

		// Disable WP Rocket optimize.
		if ( ! defined( 'DONOTROCKETOPTIMIZE' ) ) {
			define( 'DONOTROCKETOPTIMIZE', true );
		}

		// Prevent 404.
		if ( $wp_query->is_404 ) {
			$wp_query->is_404 = false;
			header( 'HTTP/1.1 200 OK', true );
		}

		// Checking for spammer attack, redirect should happen from your own website.
		if ( $this->options->check_referrer ) {
			$referer = wp_get_referer();

			if ( $referer && stripos( $referer, $this->data->site ) !== 0 ) {
				$this->show_referrer_warning();
			}
		}

		$url = $this->decode_link( $url );

		$this->add_log( $url );

		if ( ! $wp_rewrite->using_permalinks() ) {
			$url = urldecode( $url );
		}

		// Restore &#038; and &amp; to &.
		$url = html_entity_decode( $url, ENT_HTML5 | ENT_QUOTES, get_option( 'blog_charset' ) );

		if ( $this->options->anonymize_links ) {
			$url = $this->options->anonymous_link_provider . $url;
		}

		$this->show_redirect_page( $url );

	}

	/**
	 * Initiate required info for functions.
	 *
	 * @since    4.2.0
	 * @noinspection SqlResolve
	 * @noinspection HttpUrlsUsage
	 */
	public function initiate(): void {

		global $wpdb;

		$this->data                = new stdClass();
		$this->data->after         = null;
		$this->data->before        = null;
		$this->data->buffer        = null;
		$this->data->client_ip     = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : null;
		$this->data->ip            = isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : null;
		$this->data->referring_url = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : null;
		$this->data->site          = get_option( 'home' ) ?: get_option( 'siteurl' );
		$request_uri               = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
		$this->data->url           = $this->data->site . $request_uri;
		$this->data->user_agent    = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : null;

		if ( $this->data->user_agent ) {
			if (
				preg_match(
					'/(compatible;\sMSIE(?:[a-z\-]+)?\s(?:\d\.\d);\sAOL\s(?:\d\.\d);\sAOLBuild)/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 2;
				$this->data->user_agent_name = 'aol';
			} elseif (
				preg_match(
					'/(compatible;\sBingbot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.bing\.com\/bingbot\.htm\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 1;
				$this->data->user_agent_name = 'bingbot';
			} elseif (
				preg_match(
					'/(msnbot\/(?:\d\.\d)(?:[a-z]?)[\s\+]+\(\+http\:\/\/search\.msn\.com\/msnbot\.htm\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 1;
				$this->data->user_agent_name = 'bingbot';
			} elseif (
				preg_match(
					'/(compatible;\sGooglebot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.google\.com\/bot\.html\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 3;
				$this->data->user_agent_name = 'googlebot';
			} elseif (
				preg_match(
					'/(compatible;\sAsk Jeeves\/Teoma)/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 4;
				$this->data->user_agent_name = 'ask';
			} elseif (
				preg_match(
					'/(compatible;\sYahoo!(?:[a-z\-]+)?.*;[\s\+]+http\:\/\/help\.yahoo\.com\/)/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 5;
				$this->data->user_agent_name = 'yahoo';
			} elseif (
				preg_match(
					'/(compatible;\sBaiduspider(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.baidu\.com\/search\/spider\.html\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 6;
				$this->data->user_agent_name = 'baiduspider';
			} elseif (
				preg_match(
					'/(Baiduspider[\+]+\(\+http\:\/\/www\.baidu\.com\/search)/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 6;
				$this->data->user_agent_name = 'baiduspider';
			} elseif (
				preg_match(
					'/(DuckDuckBot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s]+\(\+http\:\/\/duckduckgo\.com\/duckduckbot\.html\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 8;
				$this->data->user_agent_name = 'duckduckbot';
			} elseif (
				preg_match(
					'/(compatible;\sYandexBot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/yandex\.com\/bots\))/',
					$this->data->user_agent
				)
			) {
				$this->data->user_agent_id   = 10;
				$this->data->user_agent_name = 'yandexbot';
			}
		}

		$table_name = $wpdb->prefix . 'external_links_masks';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->get_col(
			"SELECT mask FROM $table_name LIMIT 10000"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$site = str_replace( [ 'http://', 'https://' ], '', $this->data->site );

		$exclude_links = [
			'http://' . $site,
			'https://' . $site,
			'javascript',
			'magnet',
			'mailto',
			'skype',
			'tel',
			'/',
			'#',
			'https://wordpress.org/',
			'https://codex.wordpress.org/',
		];

		if ( '' !== $this->options->exclusion_list ) {
			$exclusion_list = explode( "\r\n", $this->options->exclusion_list );

			foreach ( $exclusion_list as $item ) {
				$exclude_links[] = $item;
			}
		}

		if ( is_array( $result ) && count( $result ) > 0 ) {
			$exclude_links = array_merge( $exclude_links, $result );
		}

		$this->exclusion_list = array_filter( $exclude_links );

	}

	/**
	 * Encodes url.
	 *
	 * @param string $url Url.
	 *
	 * @return string
	 * @noinspection PhpUndefinedClassInspection
	 * @noinspection SqlResolve
	 * @noinspection CryptographicallySecureAlgorithmsInspection
	 * @noinspection EncryptionInitializationVectorRandomnessInspection
	 *
	 * @since  4.0.0
	 */
	public function encode_link( string $url ): string {

		global $wpdb;

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		switch ( $this->options->link_encoding ) {
			case 'aes256':
				$encryption_key = base64_decode( $this->options->encryption_key );
				$iv             = '';

				if ( 'openssl' === $this->options->encryption ) {
					$iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'AES-256-CBC' ), $strong_result );

					if ( false === $iv || false === $strong_result ) {
						$iv = md5( wp_rand() );
					}

					$url = openssl_encrypt( $url, 'AES-256-CBC', $encryption_key, 0, $iv );
				} elseif ( 'mcrypt' === $this->options->encryption ) {
					$iv = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_DEV_RANDOM );

					$url = trim( base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $encryption_key, $url, MCRYPT_MODE_ECB, $iv ) ) );
				}

				$url .= ':' . base64_encode( $iv );

				break;
			case 'base64':
				$url = base64_encode( $url );

				break;
			case 'numbers':
				$table_name = $wpdb->prefix . 'external_links_masks';
				$sql        = 'SELECT id FROM ' . $table_name . ' WHERE LOWER(url) = LOWER(%s) LIMIT 1';

				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$result = $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					$wpdb->prepare( $sql, $url )
				);

				// No table found.
				if ( is_null( $result ) && strpos( $wpdb->last_error, "doesn't exist" ) ) {
					$create = Mihdan_NoExternalLinks_Database::migrate( 'external_links_masks' );

					if ( empty( $create ) ) {
						$this->debug_info(
							__( 'Unable to create "external_links_masks" table.', $this->plugin_name )
						);
					}
				} elseif ( is_null( $result ) ) {
					$this->debug_info(
						__( 'Failed SQL: ', $this->plugin_name ) . '<br>' .
						$sql . '<br>' .
						__( 'Error was:', $this->plugin_name ) . '<br>' .
						$wpdb->last_error
					);
				}

				if ( ! $result ) {
					$insert = $wpdb->insert(
						$wpdb->prefix . 'external_links_masks',
						[ 'url' => $url ]
					);

					if ( 0 === $insert ) {
						$this->debug_info(
							__( 'Failed SQL: ', $this->plugin_name ) . '<br>' .
							$sql . '<br>' .
							__( 'Error was:', $this->plugin_name ) . '<br>' .
							$wpdb->last_error
						);
					}

					$url = (string) $wpdb->insert_id;
				} else {
					$url = $result;
				}

				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

				break;
		}

		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return $url;

	}

	/**
	 * Decodes encoded url.
	 *
	 * @param string $url Url.
	 *
	 * @return string $url
	 * @noinspection SqlResolve
	 * @noinspection CryptographicallySecureAlgorithmsInspection
	 *
	 * @since     4.0.0
	 */
	public function decode_link( $url ): string {

		global $wpdb;

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		switch ( $this->options->link_encoding ) {
			case 'aes256':
				$encryption_key = base64_decode( $this->options->encryption_key );

				[ $encrypted, $iv ] = explode( ':', $url );

				if ( 'openssl' === $this->options->encryption ) {
					$url = (string) openssl_decrypt( $encrypted, 'AES-256-CBC', $encryption_key, 0, base64_decode( $iv ) );
				} elseif ( 'mcrypt' === $this->options->encryption ) {
					$url = trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $encryption_key, base64_decode( $encrypted ), MCRYPT_MODE_ECB, base64_decode( $iv ) ) );
				}

				break;
			case 'base64':
				$url = (string) base64_decode( $url );

				break;
			case 'numbers':
				$table_name = $wpdb->prefix . 'external_links_masks';
				$sql        = 'SELECT url FROM ' . $table_name . ' WHERE id = %s LIMIT 1';

				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
				$url = (string) $wpdb->get_var( $wpdb->prepare( $sql, $url ) );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

				break;
		}

		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

		return $url;

	}

	/**
	 * Shortens link.
	 *
	 * @since        4.2.0
	 *
	 * @param string $url Url.
	 *
	 * @return mixed
	 * @noinspection SqlResolve
	 * @noinspection HttpUrlsUsage
	 * @throws JsonException JsonException.
	 */
	public function shorten_link( $url ) {

		global $wpdb;

		$table_name = $wpdb->prefix . 'external_links_masks';
		$long_url   = rawurlencode( $url );

		// Restore original URL.
		$url = html_entity_decode( $url, ENT_HTML5 | ENT_QUOTES, get_option( 'blog_charset' ) );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		switch ( $this->options->link_shortening ) {
			case 'adfly':
				$shortener = 'adfly';

				$sql    = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'adfly' LIMIT 1";
				$result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

				if ( $result ) {
					return $result;
				}

				$api_url  = 'https://api.adf.ly/v1/shorten';
				$query    = [
					'timeout' => 2,
					'body'    => [
						'domain'      => $this->options->adfly_domain,
						'advert_type' => $this->options->adfly_advert_type,
						'url'         => urldecode( $long_url ),
						'_api_key'    => $this->options->adfly_api_key,
						'_user_id'    => $this->options->adfly_user_id,
					],
				];
				$response = wp_remote_post( $api_url, $query );
				$json     = wp_remote_retrieve_body( $response );

				if ( $json ) {
					$json      = json_decode( $json, false, 512, JSON_THROW_ON_ERROR );
					$short_url = $json->data[0]->short_url;
				}

				break;
			case 'bitly':
				$shortener = 'bitly';

				$sql    = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'bitly' LIMIT 1";
				$result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

				if ( $result ) {
					return $result;
				}

				$api_url  = 'https://api-ssl.bitly.com/v3/shorten?login=' . $this->options->bitly_login . '&apiKey=' . $this->options->bitly_api_key . '&longUrl=' . $long_url;
				$response = wp_remote_get( $api_url, [ 'timeout' => 2 ] );

				if ( $response['body'] ) {
					$data = json_decode( $response['body'], false, 512, JSON_THROW_ON_ERROR );

					$short_url = $data->data->url;
				}

				break;
			case 'shortest':
				$shortener = 'shortest';

				$sql    = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'shortest' LIMIT 1";
				$result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

				if ( $result ) {
					return $result;
				}

				$api_url  = 'https://api.shorte.st/s/' . $this->options->shortest_api_key . '/' . $long_url;
				$response = wp_remote_get( $api_url, [ 'timeout' => 2 ] );

				if ( $response['body'] ) {
					$data = json_decode( $response['body'], false, 512, JSON_THROW_ON_ERROR );

					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$short_url = $data->shortenedUrl;
				}

				break;
			case 'yourls':
				$shortener = 'yourls';

				$sql    = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'yourls' LIMIT 1";
				$result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

				if ( $result ) {
					return $result;
				}

				$host  = 'https://' . $this->options->yourls_domain . '/yourls-api.php';
				$query = [
					'action'    => 'shorturl',
					'format'    => 'json',
					'signature' => $this->options->yourls_signature,
					'url'       => $long_url,
				];
				$query = http_build_query( $query );

				$response = wp_remote_get(
					$host . '?' . $query,
					[
						'timeout' => 2,
					]
				);

				$json = wp_remote_retrieve_body( $response );

				if ( $json ) {
					$json      = json_decode( $json, false, 512, JSON_THROW_ON_ERROR );
					$short_url = $json->shorturl;
				} else {
					$short_url = $long_url;
				}

				break;
		}

		if ( isset( $short_url, $shortener ) ) {
			$wpdb->insert(
				$wpdb->prefix . 'external_links_masks',
				[
					'url'       => $url,
					'mask'      => $short_url,
					'short_url' => $shortener,
				]
			);

			return $short_url;
		}

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return $url;
	}

	/**
	 * Adds log record to database.
	 *
	 * @since    4.0.0
	 *
	 * @param string $url Url.
	 *
	 * @noinspection PhpUndefinedClassInspection*/
	public function add_log( $url ): void {

		global $wpdb;

		if ( ! $this->options->logging ) {
			return;
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
		$insert = $wpdb->insert(
			$wpdb->prefix . 'external_links_logs',
			[
				'url'           => $url,
				'user_agent'    => $this->data->user_agent,
				'referring_url' => $this->data->referring_url,
				'ip_address'    => $this->data->client_ip,
				'date'          => current_time( 'mysql' ),
			]
		);

		if ( false !== $insert ) {
			return; // All OK.
		}

		// Error - stats record could not be created.
		$this->debug_info(
			__( 'Failed SQL: ', $this->plugin_name ) . '<br>' .
			$wpdb->last_query . '<br>' .
			__( 'Error was:', $this->plugin_name ) . '<br>' .
			$wpdb->last_error
		);

		$create = Mihdan_NoExternalLinks_Database::migrate( 'external_links_logs' );

		if ( empty( $create ) ) {
			$this->debug_info(
				__( 'Unable to create "external_links_logs" table.', $this->plugin_name )
			);
		} else {
			$wpdb->insert(
				$wpdb->prefix . 'external_links_logs',
				[
					'url'  => $url,
					'date' => current_time( 'mysql' ),
				]
			);
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery

	}

	/**
	 * Renders the referrer warning page.
	 */
	public function show_referrer_warning(): void {
		header( 'Content-type: text/html; charset="utf-8"', true );
		header( 'Refresh: ' . $this->options->redirect_time . '; url=' . get_home_url() );

		include_once 'partials/referrer-warning.php';

	}

	/**
	 * Renders the redirect page.
	 *
	 * @since    4.0.0
	 *
	 * @param string $url Url.
	 */
	public function show_redirect_page( $url ): void {
		$url           = trim( $url );
		$redirect_time = absint( $this->options->redirect_time );
		$masking_type  = trim( $this->options->masking_type );

		header( 'Content-type: text/html; charset="utf-8"', true );

		// phpcs:disable WordPress.PHP.NoSilencedErrors.Discouraged
		if ( $url ) {
			if ( '301' === $masking_type ) {
				@header( 'Location: ' . $url, true, 301 );
				die;
			}

			if ( '302' === $masking_type ) {
				@header( 'Location: ' . $url, true, 302 );
				die;
			}

			if ( '307' === $masking_type ) {
				@header( 'Location: ' . $url, true, 307 );
				die;
			}

			if ( 'javascript' === $masking_type ) {
				header( 'Refresh: ' . $redirect_time . '; url=' . $url );
			}

			if ( $this->options->redirect_page > 0 ) {
				// Disable page indexing.
				header( 'X-Robots-Tag: noindex, nofollow', true );

				$page_content = wp_remote_get( get_permalink( $this->options->redirect_page ) );

				if ( $page_content ) {
					echo preg_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'#(https?://)?%linkurl%#i',
						esc_url( $url ),
						wp_remote_retrieve_body( $page_content )
					);
					die;
				}
			} else {
				include_once 'partials/redirect.php';
			}
		}

		// phpcs:enable WordPress.PHP.NoSilencedErrors.Discouraged
	}

	/**
	 * Outputs the debug log.
	 *
	 * TODO: Look to move to admin class
	 *
	 * @since    4.0.0
	 */
	public function output_debug(): void {

		echo "\n<!--wp-noexternallinks debug:\n" . esc_html( implode( "\n\n", $this->debug_log ) ) . "\n-->";

	}

	/**
	 * Adds data to the debug log.
	 *
	 * TODO: Look to move to admin class
	 *
	 * @since      4.0.0
	 *
	 * @param string $info Info.
	 * @param int    $return Whether return the logged info or empty string.
	 *
	 * @return     string
	 */
	public function debug_info( $info, $return = 0 ): string {

		if ( $this->options->debug_mode ) {
			$t                 = "\n<!--wp-noexternallinks debug:\n" . $info . "\n-->";
			$this->debug_log[] = $info;

			if ( $return ) {
				return $t;
			}
		}

		return '';

	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		if ( $this->options->seo_hide ) {
			wp_enqueue_style(
				MIHDAN_NO_EXTERNAL_LINKS_SLUG . '-seo-hide',
				MIHDAN_NO_EXTERNAL_LINKS_URL . '/public/css/seo-hide.css',
				[],
				MIHDAN_NO_EXTERNAL_LINKS_VERSION
			);
			wp_enqueue_script(
				MIHDAN_NO_EXTERNAL_LINKS_SLUG . '-seo-hide',
				MIHDAN_NO_EXTERNAL_LINKS_URL . '/public/js/seo-hide.js',
				[],
				filemtime( MIHDAN_NO_EXTERNAL_LINKS_DIR . '/public/js/seo-hide.js' ),
				true
			);
		}
	}
}
