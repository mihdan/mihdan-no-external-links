<?php
/**
 * Admin log table functionality.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Admin
 * @author        mihdan
 */

// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

namespace Mihdan\No_External_Links\Admin;

use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * LogTable class.
 */
class LogTable extends WP_List_Table {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.0.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private string $options_prefix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.0.0
	 *
	 * @param string $plugin_name    The name of the plugin.
	 * @param string $options_prefix The options prefix of the plugin.
	 */
	public function __construct( $plugin_name, $options_prefix ) {

		$this->plugin_name    = $plugin_name;
		$this->options_prefix = $options_prefix;

		parent::__construct(
			[
				'singular' => __( 'Log', $this->plugin_name ),
				'plural'   => __( 'Logs', $this->plugin_name ),
				'ajax'     => false,
			]
		);

		add_action( 'admin_notices', [ $this, 'log_delete_notice' ] );

	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since    4.0.0
	 */
	public function no_items(): void {
		esc_html_e( 'No logs available.', $this->plugin_name );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @since     4.0.0
	 *
	 * @param array  $item        Item array.
	 * @param string $column_name Column name.
	 *
	 * @return string|null
	 */
	public function column_default( $item, $column_name ): ?string {

		// Nonce is verified in the WP_List_Table class.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		switch ( $column_name ) {
			case 'title':
				$delete_nonce = wp_create_nonce( $this->options_prefix . 'delete_log' );

				$title = '<strong>' . $item['url'] . '</strong>';

				$actions = [
					'delete' => sprintf(
						'<a href="?page=%s&action=%s&log=%s&_wpnonce=%s">Delete</a>',
						isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1,
						'delete',
						absint( $item['id'] ),
						$delete_nonce
					),
				];

				return $title . $this->row_actions( $actions );
			case 'referring_url':
				return $item['referring_url'];
			case 'user_agent':
				return $item['user_agent'];
			case 'ip_address':
				return $item['ip_address'];
			case 'datetime':
				return $item['date'];
			default:
				break;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		return (string) print_r( $item, true );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since     4.0.0
	 *
	 * @param array $item Item array.
	 *
	 * @return    string
	 */
	public function column_cb( $item ): string {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />',
			$item['id']
		);

	}

	/**
	 *  Associative array of columns
	 *
	 * @since     4.0.0
	 * @return    array    $columns
	 */
	public function get_columns(): array {
		return [
			'cb'            => '<input type="checkbox" />',
			'title'         => __( 'URL', $this->plugin_name ),
			'referring_url' => __( 'Referring URL', $this->plugin_name ),
			'user_agent'    => __( 'User Agent', $this->plugin_name ),
			'ip_address'    => __( 'IP Address', $this->plugin_name ),
			'datetime'      => __( 'Date/Time', $this->plugin_name ),
		];
	}

	/**
	 * Columns to make sortable.
	 *
	 * @since     4.0.0
	 * @return    array     $sortable_columns
	 */
	public function get_sortable_columns(): array {
		return [
			'title'         => [ 'title', true ],
			'referring_url' => [ 'referring_url', true ],
			'user_agent'    => [ 'user_agent', true ],
			'ip_address'    => [ 'ip_address', true ],
			'datetime'      => [ 'datetime', true ],
		];
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since     4.0.0
	 * @return    array    $actions
	 */
	public function get_bulk_actions(): array {
		return [ 'bulk-delete' => 'Delete' ];
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 *
	 * @since     4.0.0
	 */
	public function prepare_items(): void {

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'logs_per_page' );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = $this->get_logs( $per_page, $current_page );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @since    4.0.0
	 *
	 * @return int
	 * @noinspection SqlResolve
	 */
	public function record_count(): int {
		global $wpdb;

		$table_name = $wpdb->prefix . 'external_links_logs';

		$sql = "SELECT COUNT(id) FROM $table_name";

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Retrieve external links log data from the database
	 *
	 * @since     4.0.0
	 *
	 * @param int $per_page    Items per page.
	 * @param int $page_number Page number.
	 *
	 * @return array
	 */
	public function get_logs( int $per_page = 5, int $page_number = 1 ): array {

		global $wpdb;

		$order_by = 'date';
		$order    = 'DESC';
		$offset   = ( $page_number - 1 ) * $per_page;
		$mapping  = [
			'title'         => 'url',
			'datetime'      => 'date',
			'referring_url' => 'referring_url',
			'user_agent'    => 'user_agent',
			'ip_address'    => 'ip_address',
		];

		// Nonce is verified in the WP_List_Table class.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		}

		if ( ! empty( $_REQUEST['orderby'] ) && array_key_exists( sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ), $mapping ) ) {
			$order_by = $mapping[ sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) ];
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}external_links_logs ORDER BY %s %s LIMIT %d OFFSET %d",
				[
					$order_by,
					$order,
					$per_page,
					$offset,
				]
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	}

	/**
	 * Processes any bulk actions.
	 *
	 * @since        4.0.0
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function process_bulk_action(): void {

		$redirect = wp_get_raw_referer();

		$nonce = ! empty( $_REQUEST['_wpnonce'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) )
			: '';

		if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, $this->options_prefix . 'delete_log' ) ) {
			wp_die( esc_html__( 'Are you sure you want to do this?' ) );
		}

		if ( 'delete' === $this->current_action() ) {

			$delete_count = 0;

			if ( $this->delete_log( ! empty( $_GET['log'] ) ? absint( $_GET['log'] ) : 0 ) ) {
				$delete_count ++;
			}

			$redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

			wp_safe_redirect( $redirect );
			exit;
		}

		if (
			( isset( $_POST['action'] ) && 'bulk-delete' === $_POST['action'] ) ||
			( isset( $_POST['action2'] ) && 'bulk-delete' === $_POST['action2'] )
		) {

			$delete_ids = ! empty( $_POST['bulk-delete'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['bulk-delete'] ) )
				: [];

			$delete_count = 0;
			foreach ( $delete_ids as $id ) {
				$delete = $this->delete_log( $id );

				if ( $delete ) {
					++ $delete_count;
				}
			}

			$redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

			wp_safe_redirect( $redirect );
			exit;
		}

	}

	/**
	 * Delete a log record.
	 *
	 * @since     4.0.0
	 *
	 * @param int $id log ID.
	 *
	 * @return bool
	 */
	public function delete_log( int $id ): bool {

		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$delete_count = $wpdb->delete(
			$wpdb->prefix . 'external_links_logs',
			[ 'ID' => $id ],
			[ '%d' ]
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return $delete_count > 0;

	}

	/**
	 * Display delete notice.
	 *
	 * @since    4.2.0
	 */
	public function log_delete_notice(): void {
		// Nonce is verified in the WP_List_Table class.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$delete_count = ! empty( $_GET['delete_count'] ) ? (int) $_GET['delete_count'] : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( 1 === $delete_count ) {
			?>
			<div class="notice notice-success">
				<p>
					<?php
					esc_html_e(
						'Log deleted.',
						$this->plugin_name
					);
					?>
				</p>
			</div>
			<?php
		} elseif ( $delete_count > 1 ) {
			?>
			<div class="notice notice-success">
				<p>
					<?php
					// translators: number of deleted items.
					echo esc_html( sprintf( __( '%s logs deleted.', $this->plugin_name ), $delete_count ) );
					?>
				</p>
			</div>
			<?php
		}
	}

}
