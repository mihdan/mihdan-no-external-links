<?php
/**
 * Public facing specific functionality.
 *
 * @since         4.0.0
 * @package       Mihdan_NoExternalLinks
 * @subpackage    Mihdan_NoExternalLinks/Public
 * @author        mihdan
 */

class Mihdan_NoExternalLinks_Public {

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
     * The site url.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $site    The site url.
     */
    private $site;

    /**
     * The exclusion list of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $version    The exclusion list of this plugin.
     */
    private $exclusion_list;

    /**
     * A data layer to store miscellaneous data.
     *
     * @since    4.2.0
     * @access   private
     * @var      mixed      $data    A data layer to store miscellaneous data.
     */
    private $data;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 * @param    string    $plugin_name    The name of the plugin.
	 * @param    string    $version        The version of this plugin.
     * @param    object    $options        The current options of this plugin.
	 */
	public function __construct( $plugin_name, $version, $options ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
        $this->options = $options;

        $this->setup_hooks();
        $this->initiate();
	}

	public function setup_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

    /**
     * Start full page filtering.
     *
     * @since    4.2.0
     */
    public function fullpage_filter() {

        if ( defined( 'DOING_CRON' ) ) {
            // do not try to use output buffering on cron
            return;
        }

        ob_start( array( $this, 'ob_filter' ) );

    }

    /**
     * Output buffering function.
     *
     * @since     4.2.1
     * @param     $content
     * @return    mixed
     */
    public function ob_filter( $content ) {

        global $post;

        if ( $content ) {
            $content = preg_replace( '/(<body[^>]*?>)/', '$1' . $this->data->buffer, $content );

            if (
                is_object( $post ) &&
                get_post_meta( $post->ID, 'mask_links', true ) !== 'disabled' &&
                function_exists( 'is_feed' ) &&
                ! is_feed()
            ) {
                $content = $this->filter( $content );
            }
        }

        return $content;

    }

    /**
     * Check if post/page should be masked.
     *
     * @since     4.0.0
     * @param     string    $content
     * @return    string    mixed
     */
    public function check_post( $content ) {

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

        $this->debug_info( 'Filter will be applied' );

        return $this->filter( $content );
    }

    /**
     * Filters content to find links.
     *
     * @since     4.0.0
     * @param     string    $content
     * @return    mixed
     */
    public function filter( $content ) {

        if ( function_exists( 'is_admin' ) && is_admin() ) {
            return $content;
        }

        $this->debug_info( "Processing text: \n" . str_replace( '-->', '--&gt;', $content ) );

        if ( function_exists( 'is_feed' ) && is_feed() && ! $this->options->mask_rss && ! $this->options->mask_rss_comments ) {
            $this->debug_info( 'It is feed, no processing' );

            return $content;
        }

        $pattern = '/<a (.*?)href=[\"\'](.*?)[\"\'](.*?)>(.*?)<\/a>/si';

        $content = preg_replace_callback( $pattern, array( $this, 'parser' ), $content, -1, $count );

        $this->debug_info( $count . " replacements done.\nFilter returned: \n" . str_replace( '-->', '--&gt;', $content ) );

        return $content;

    }

    /**
     * Determines whether to mask a link or not.
     *
     * @since     4.0.0
     * @param     array     $matches
     * @return    string
     */
    public function parser( $matches ) {

        $anchor = $matches[0];
        $href = $matches[2];

        $this->debug_info( 'Parser called. Parsing argument {' . var_export( $matches, 1 ) . "}\nAgainst link {" . $href . "}\n " );

        if ( preg_match( '/ rel=[\"\']exclude[\"\']/i', $anchor, $match ) ) {
            $this->exclusion_list[] = $href;
            return str_replace( $match[0], '', $anchor );
        }

        if ( '' !== $this->options->inclusion_list ) {
            $inclusion_list = explode( "\r\n", $this->options->inclusion_list );

            foreach ( $inclusion_list as $inclusion_url ) {
                if ( strpos( $href, $inclusion_url ) === 0 ) {
                    $link = $this->mask_link( $matches );

                    return $link;
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

            $link = $this->mask_link( $matches );

            return $link;
        }

        return $matches[0];

    }

    /**
     * Performs link masking functionality.
     *
     * @since     4.2.0
     * @param     array     $matches
     * @return    string    $anchor
     */
    public function mask_link( $matches ) {

        global $wp_rewrite;

        $anchor      = $matches[0];
        $anchor_text = $matches[4];
        $attributes  = trim( $matches[1] ) . ' ' . trim( $matches[3] );
        $url         = $matches[2];

        if ( 'all' !== $this->options->bot_targeting && ! in_array( $this->data->user_agent_name, $this->options->bots_selector ) ) {
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
        $blank = $this->options->target_blank ? ' target="_blank"' : '';
        $nofollow = $this->options->nofollow ? ' rel="nofollow"' : '';

        if ( 'none' !== $this->options->link_shortening ) {
            $url = $this->shorten_link( $url );

            $this->exclusion_list[] = $url;

            $anchor = '<a' . $blank . $nofollow . ' href="' . $url . '" ' . $attributes . '>' . $anchor_text . '</a>';

            return $anchor;
        }

        if ( 'no' !== $this->options->masking_type ) {
            $url = $this->encode_link( $url );

            if ( ! $wp_rewrite->using_permalinks() ) {
                $url = urlencode( $url );
            }

            $url = trim( $this->data->site, '/' ) . '/' . $separator . $url;
        }

	    if ( $this->options->seo_hide ) {
		    return sprintf( '<span class="waslinkname" data-link="%s"%s>%s</span>', esc_attr( base64_encode( $url ) ), $blank, $anchor_text );
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
     * Checks if current page is a redirect page.
     *
     * @since    4.0.0
     */
    public function check_redirect() {
	    global $wp_query;

        $goto = '';
        $p = strpos( $_SERVER[ 'REQUEST_URI' ], '/' . $this->options->separator . '/' );

        if ( isset( $_REQUEST[ $this->options->separator ] ) ) {
            $goto = sanitize_key( $_REQUEST[ $this->options->separator ] );
        } elseif ( false !== $p ) {
            $goto = substr( $_SERVER[ 'REQUEST_URI' ], $p + strlen( $this->options->separator ) + 2 );
        }

        $goto = strip_tags( $goto );

        if ( ! empty( $goto ) ) {
            $this->redirect( $goto );
        }

    }

    /**
     * Initiates the redirect.
     *
     * @since    4.0.0
     * @param    string    $url
     */
    public function redirect( $url ) {

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
     */
    public function initiate() {

        global $wpdb;

        $this->data = new stdClass();
        $this->data->after = null;
        $this->data->before = null;
        $this->data->buffer = null;
        $this->data->client_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
        $this->data->ip = isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : null;
        $this->data->referring_url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null;
        $this->data->site = get_option('home') ? get_option('home') : get_option('siteurl');
        $this->data->url = $this->data->site . $_SERVER['REQUEST_URI'];
        $this->data->user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;

        if ( $this->data->user_agent ) {
            if (
            preg_match(
                '/(compatible;\sMSIE(?:[a-z\-]+)?\s(?:\d\.\d);\sAOL\s(?:\d\.\d);\sAOLBuild)/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 2;
                $this->data->user_agent_name = 'aol';
            }
            elseif (
            preg_match(
                '/(compatible;\sBingbot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.bing\.com\/bingbot\.htm\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 1;
                $this->data->user_agent_name = 'bingbot';
            }
            elseif (
            preg_match(
                '/(msnbot\/(?:\d\.\d)(?:[a-z]?)[\s\+]+\(\+http\:\/\/search\.msn\.com\/msnbot\.htm\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 1;
                $this->data->user_agent_name = 'bingbot';
            }
            elseif (
            preg_match(
                '/(compatible;\sGooglebot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.google\.com\/bot\.html\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 3;
                $this->data->user_agent_name = 'googlebot';
            }
            elseif (
            preg_match(
                '/(compatible;\sAsk Jeeves\/Teoma)/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 4;
                $this->data->user_agent_name = 'ask';
            }
            elseif (
            preg_match(
                '/(compatible;\sYahoo!(?:[a-z\-]+)?.*;[\s\+]+http\:\/\/help\.yahoo\.com\/)/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 5;
                $this->data->user_agent_name = 'yahoo';
            }
            elseif (
            preg_match(
                '/(compatible;\sBaiduspider(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/www\.baidu\.com\/search\/spider\.html\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 6;
                $this->data->user_agent_name = 'baiduspider';
            }
            elseif (
            preg_match(
                '/(Baiduspider[\+]+\(\+http\:\/\/www\.baidu\.com\/search)/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 6;
                $this->data->user_agent_name = 'baiduspider';
            }
            elseif (
            preg_match(
                '/(DuckDuckBot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s]+\(\+http\:\/\/duckduckgo\.com\/duckduckbot\.html\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 8;
                $this->data->user_agent_name = 'duckduckbot';
            }
            elseif (
            preg_match(
                '/(compatible;\sYandexBot(?:[a-z\-]+)?.*\/(?:\d\.\d);[\s\+]+http\:\/\/yandex\.com\/bots\))/',
                $this->data->user_agent
            )
            ) {
                $this->data->user_agent_id = 10;
                $this->data->user_agent_name = 'yandexbot';
            }

        }

        $table_name = $wpdb->prefix . 'external_links_masks';
        $sql = "SELECT mask FROM $table_name LIMIT 10000";
        $result = $wpdb->get_col( $sql );

        $site = str_replace( array( 'http://', 'https://' ), '', $this->data->site );

        $exclude_links = array(
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
        );

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
     * @since     4.0.0
     * @param     string    $url
     * @return    mixed
     */
    public function encode_link( $url ) {

        global $wpdb;

        switch ( $this->options->link_encoding ) {
            case 'aes256':
                $encryption_key = base64_decode( $this->options->encryption_key );

                if ( 'openssl' === $this->options->encryption ) {
                    $iv = openssl_random_pseudo_bytes( openssl_cipher_iv_length( 'AES-256-CBC' ) );

                    $url = openssl_encrypt( $url, 'AES-256-CBC', $encryption_key, 0, $iv );
                } elseif ( 'mcrypt' === $this->options->encryption ) {
                    $iv = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND );

                    $url = trim( base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $encryption_key, $url, MCRYPT_MODE_ECB, $iv ) ) );
                }

                $url = $url . ':' . base64_encode( $iv );

                break;
            case 'base64':
                $url = base64_encode( $url );

                break;
            case 'numbers':
                $table_name = $wpdb->prefix . 'external_links_masks';
                $sql = 'SELECT id FROM ' . $table_name . ' WHERE LOWER(url) = LOWER(%s) LIMIT 1';
                $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                // no table found
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
                        array( 'url' => $url )
                    );

                    if ( 0 === $insert ) {
                        $this->debug_info(
                            __( 'Failed SQL: ', $this->plugin_name ) . '<br>' .
                            $sql . '<br>' .
                            __( 'Error was:', $this->plugin_name ) . '<br>' .
                            $wpdb->last_error
                        );
                    }

                    $url = $wpdb->insert_id;
                } else {
                    $url = $result;
                }

                break;
        }

        return $url;

    }

    /**
     * Decodes encoded url.
     *
     * @since     4.0.0
     * @param     string    $url
     * @return    mixed     $url
     */
    public function decode_link( $url ) {

        global $wpdb;

        switch ( $this->options->link_encoding ) {
            case 'aes256':
                $encryption_key = base64_decode( $this->options->encryption_key );

                list( $encrypted, $iv ) = explode( ':', $url );

                if ( 'openssl' === $this->options->encryption ) {
                    $url = openssl_decrypt( $encrypted, 'AES-256-CBC', $encryption_key, 0, base64_decode( $iv ) );
                } elseif ( 'mcrypt' === $this->options->encryption ) {
                    $url = trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $encryption_key, base64_decode( $encrypted ), MCRYPT_MODE_ECB, base64_decode( $iv ) ) );
                }

                break;
            case 'base64':
                $url = base64_decode( $url );

                break;
            case 'numbers':
                $table_name = $wpdb->prefix . 'external_links_masks';
                $sql = 'SELECT url FROM ' . $table_name . ' WHERE id = %s LIMIT 1';
                $url = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                break;
        }

        return $url;

    }

    /**
     * Shortens link.
     *
     * @since     4.2.0
     * @param     string    $url
     * @return    mixed
     */
    public function shorten_link( $url ) {

        global $wpdb;

        $table_name = $wpdb->prefix . 'external_links_masks';
        $long_url = urlencode( $url );

	    // Restore original URL.
	    $url = html_entity_decode( $url, ENT_HTML5 | ENT_QUOTES, get_option( 'blog_charset' ) );

        switch ( $this->options->link_shortening ) {
            case 'adfly':
                $shortener = 'adfly';

                $sql = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'adfly' LIMIT 1";
                $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                if ( $result ) {
                    return $result;
                }

                $api_url = 'http://api.adf.ly/v1/shorten';
                $query = [
	                'timeout' => 2,
	                'body' => [
	                	'domain'      => $this->options->adfly_domain,
	                	'advert_type' => $this->options->adfly_advert_type,
	                	'url'         => urldecode( $long_url ),
	                	'_api_key'    => $this->options->adfly_api_key,
	                	'_user_id'    => $this->options->adfly_user_id,
	                ],
                ];
                $response = wp_remote_post( $api_url, $query );
                $json = wp_remote_retrieve_body( $response );

                if ( $json ) {
                	$json = json_decode( $json );
	                $short_url = $json->data[0]->short_url;
                }

                break;
            case 'bitly':
                $shortener = 'bitly';

                $sql = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'bitly' LIMIT 1";
                $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                if ( $result ) {
                    return $result;
                }

                $api_url = 'https://api-ssl.bitly.com/v3/shorten?login=' . $this->options->bitly_login . '&apiKey=' . $this->options->bitly_api_key . '&longUrl=' . $long_url;
                $response = wp_remote_get( $api_url, array( 'timeout' => 2 ) );

                if ( $response['body'] ) {
                    $data = json_decode( $response['body'] );

                    $short_url = $data->data->url;
                }

                break;
            case 'linkshrink':
                $shortener = 'linkshrink';

                $sql = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'linkshrink' LIMIT 1";
                $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                if ( $result ) {
                    return $result;
                }

                $api_url = 'https://linkshrink.net/api.php?key=' . $this->options->linkshrink_api_key . '&url=' . $long_url;
                $response = wp_remote_get( $api_url, array( 'timeout' => 2 ) );

                if ( $response['body'] ) {
                    $short_url = $response['body'];
                }

                break;
            case 'shortest':
                $shortener = 'shortest';

                $sql = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'shortest' LIMIT 1";
                $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

                if ( $result ) {
                    return $result;
                }

                $api_url = 'https://api.shorte.st/s/' . $this->options->shortest_api_key . '/' . $long_url;
                $response = wp_remote_get( $api_url, array( 'timeout' => 2 ) );

                if ( $response['body'] ) {
                    $data = json_decode( $response['body'] );

                    $short_url = $data->shortenedUrl;
                }

                break;
	        case 'yourls':
		        $shortener = 'yourls';

		        $sql = "SELECT mask FROM $table_name WHERE LOWER(url) = LOWER(%s) AND LOWER(short_url) = 'yourls' LIMIT 1";
		        $result = $wpdb->get_var( $wpdb->prepare( $sql, $url ) );

		        if ( $result ) {
			        return $result;
		        }

		        $host = 'https://' . $this->options->yourls_domain . '/yourls-api.php';
		        $query = array(
		        	'action' => 'shorturl',
		        	'format' => 'json',
		        	'signature' => $this->options->yourls_signature,
		        	'url' => $long_url,
		        );
		        $query = http_build_query( $query );

		        $response = wp_remote_get(
			        $host . '?'. $query,
			        array(
		        	    'timeout' => 2,
		            )
		        );

		        $json = wp_remote_retrieve_body( $response );

		        if ( $json ) {
		        	$json = json_decode( $json );
			        $short_url = $json->shorturl;
		        } else {
			        $short_url = $long_url;
		        }

		        break;
        }

        if ( isset( $short_url ) && isset( $shortener ) ) {
            $wpdb->insert(
                $wpdb->prefix . 'external_links_masks',
                array(
                    'url' => $url,
                    'mask' => $short_url,
                    'short_url' => $shortener
                )
            );

            return $short_url;
        }

        return $url;
    }

    /**
     * Adds log record to database.
     *
     * @since    4.0.0
     * @param    string    $url
     */
    public function add_log( $url ) {

        global $wpdb;

        if ( ! $this->options->logging ) {
            return;
        }

        $insert = $wpdb->insert(
            $wpdb->prefix . 'external_links_logs',
            array(
                'url' => $url,
                'user_agent' => $this->data->user_agent,
                'referring_url' => $this->data->referring_url,
                'ip_address' => $this->data->client_ip,
                'date' => current_time( 'mysql' )
            )
        );

        if ( false !== $insert ) {
            return; // all ok
        }

        // error - stats record could not be created
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
                array(
                    'url' => $url,
                    'date' => current_time( 'mysql' )
                )
            );
        }

    }

    /**
     * Renders the referrer warning page.
     */
    public function show_referrer_warning() {
	    header( 'Content-type: text/html; charset="utf-8"', true );
	    header( 'Refresh: ' . $this->options->redirect_time . '; url=' . get_home_url() );

        include_once 'partials/referrer-warning.php';

    }

    /**
     * Renders the redirect page.
     *
     * @since    4.0.0
     * @param    string    $url
     */
    public function show_redirect_page( $url ) {
    	$url           = trim( $url );
    	$redirect_time = absint( $this->options->redirect_time );
    	$masking_type  = trim( $this->options->masking_type );

    	header( 'Content-type: text/html; charset="utf-8"', true );

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

		    include_once 'partials/redirect.php';
	    }
    }

    /**
     * Outputs the debug log.
     *
     * TODO: Look to move to admin class
     *
     * @since    4.0.0
     */
    public function output_debug() {

        echo "\n<!--wp-noexternallinks debug:\n" . implode( "\n\n", $this->debug_log ) . "\n-->";

    }

    /**
     * Adds data to the debug log.
     *
     * TODO: Look to move to admin class
     *
     * @since      4.0.0
     * @param      string    $info
     * @param      int       $return
     * @return     string
     */
    public function debug_info( $info, $return = 0 ) {

        if ( $this->options->debug_mode ) {
            $t = "\n<!--wp-noexternallinks debug:\n" . $info . "\n-->";
            $this->debug_log[] = $info;

            if ( $return ) {
                return $t;
            }
        }

        return '';

    }

    public function enqueue_scripts() {
	    if ( $this->options->seo_hide ) {
	    	wp_enqueue_style(
			    MIHDAN_NO_EXTERNAL_LINKS_SLUG . '-seo-hide',
			    MIHDAN_NO_EXTERNAL_LINKS_URL . '/public/css/seo-hide.css',
			    array(),
			    MIHDAN_NO_EXTERNAL_LINKS_VERSION
		    );
		    wp_enqueue_script(
			    MIHDAN_NO_EXTERNAL_LINKS_SLUG . '-seo-hide',
			    MIHDAN_NO_EXTERNAL_LINKS_URL . '/public/js/seo-hide.js',
			    array(),
			    MIHDAN_NO_EXTERNAL_LINKS_VERSION,
			    true
		    );
	    }
    }
}
