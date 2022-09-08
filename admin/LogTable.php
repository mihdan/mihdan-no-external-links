<?php
/**
 * Admin log table functionality.
 *
 * @since         4.0.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Admin
 * @author        mihdan
 */

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $options_prefix The options prefix of the plugin.
	 *
	 * @since    4.0.0
	 */
	public function __construct( $plugin_name, $options_prefix ) {

		$this->plugin_name    = $plugin_name;
		$this->options_prefix = $options_prefix;

		parent::__construct(
			array(
				'singular' => __( 'Log', $this->plugin_name ),
				'plural'   => __( 'Logs', $this->plugin_name ),
				'ajax'     => false,
			)
		);

		add_action( 'admin_notices', array( $this, 'log_delete_notice' ) );

	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since    4.0.0
	 */
	public function no_items() {
		esc_html_e( 'No logs available.', $this->plugin_name );
	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @param array  $item Item array.
	 * @param string $column_name Column name.
	 *
	 * @return    mixed
	 * @since     4.0.0
	 */
	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'title':
				$delete_nonce = wp_create_nonce( $this->options_prefix . 'delete_log' );

				$title = '<strong>' . $item['url'] . '</strong>';

				$actions = array(
					'delete' => sprintf(
						'<a href="?page=%s&action=%s&log=%s&_wpnonce=%s">Delete</a>',
						isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'delete',
						absint( $item['id'] ),
						$delete_nonce
					),
				);

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
				return print_r( $item, true ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}

	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item Item array.
	 *
	 * @return    string
	 * @since     4.0.0
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
	 * @return    array    $columns
	 * @since     4.0.0
	 */
	public function get_columns(): array {
		return array(
			'cb'            => '<input type="checkbox" />',
			'title'         => __( 'URL', $this->plugin_name ),
			'referring_url' => __( 'Referring URL', $this->plugin_name ),
			'user_agent'    => __( 'User Agent', $this->plugin_name ),
			'ip_address'    => __( 'IP Address', $this->plugin_name ),
			'datetime'      => __( 'Date/Time', $this->plugin_name ),
		);
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return    array     $sortable_columns
	 * @since     4.0.0
	 */
	public function get_sortable_columns(): array {
		return array(
			'title'         => array( 'title', true ),
			'referring_url' => array( 'referring_url', true ),
			'user_agent'    => array( 'user_agent', true ),
			'ip_address'    => array( 'ip_address', true ),
			'datetime'      => array( 'datetime', true ),
		);
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return    array    $actions
	 * @since     4.0.0
	 */
	public function get_bulk_actions(): array {
		return array(
			'bulk-delete' => 'Delete',
		);
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
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);

		$this->items = $this->get_logs( $per_page, $current_page );
	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @return   null|string
	 * @since    4.0.0
	 */
	public function record_count(): ?string {
		global $wpdb;

		$table_name = $wpdb->prefix . 'external_links_logs';

		$sql = "SELECT COUNT(id) FROM $table_name";

		// phpcs:disable
		return $wpdb->get_var( $sql );
		// phpcs:enable
	}

	/**
	 * Retrieve external links log data from the database
	 *
	 * @param int $per_page    Items per page.
	 * @param int $page_number Page number.
	 *
	 * @return    mixed
	 * @since     4.0.0
	 */
	public function get_logs( int $per_page = 5, int $page_number = 1 ) {

		global $wpdb;

		$order_by = 'date';
		$order    = 'DESC';
		$offset   = ( $page_number - 1 ) * $per_page;
		$mapping  = array(
			'title'         => 'url',
			'datetime'      => 'date',
			'referring_url' => 'referring_url',
			'user_agent'    => 'user_agent',
			'ip_address'    => 'ip_address',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['order'] ) ) {
			$order = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['orderby'] ) && array_key_exists( sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ), $mapping ) ) {
			$order_by = $mapping[ sanitize_sql_orderby( wp_unslash( $_REQUEST['orderby'] ) ) ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// phpcs:disable
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}external_links_logs ORDER BY %s %s LIMIT %d OFFSET %d",
				array(
					$order_by,
					$order,
					$per_page,
					$offset,
				)
			),
			ARRAY_A
		);
		// phpcs:enable

		return $result;

	}

	/**
	 * Processes any bulk actions.
	 *
	 * @since     4.0.0
	 */
	public function process_bulk_action(): void {

		$redirect = wp_get_raw_referer();

		if ( 'delete' === $this->current_action() ) {

			$nonce = ! empty( $_REQUEST['_wpnonce'] )
				? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) )
				: '';

			if ( ! wp_verify_nonce( $nonce, $this->options_prefix . 'delete_log' ) ) {
				wp_die( __( 'Are you sure you want to do this?' ) );
			} else {
				$delete = $this->delete_log( ! empty( $_GET['log'] ) ? absint( $_GET['log'] ) : 0 );

				$delete_count = 0;
				if ( $delete ) {
					++ $delete_count;
				}

				$redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

				wp_safe_redirect( $redirect );
				exit;
			}
		}

		if (
			( isset( $_POST['action'] ) && $_POST['action'] === 'bulk-delete' ) ||
			( isset( $_POST['action2'] ) && $_POST['action2'] === 'bulk-delete' )
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
	 * @param int $id log ID.
	 *
	 * @return    bool
	 * @since     4.0.0
	 */
	public function delete_log( int $id ): bool {

		global $wpdb;

		// phpcs:disable
		$delete_count = $wpdb->delete(
			$wpdb->prefix . 'external_links_logs',
			array( 'ID' => $id ),
			array( '%d' )
		);
		// phpcs:enable

		if ( $delete_count > 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Display delete notice.
	 *
	 * @since    4.2.0
	 */
	public function log_delete_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$delete_count = ! empty( $_GET['delete_count'] )
			? (int) $_GET['delete_count'] // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			: 0;

		if ( $delete_count === 1 ) {
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
					echo sprintf( esc_html__( '%s logs deleted.', $this->plugin_name ), (int) $delete_count );
					?>
				</p>
			</div>
			<?php
		}
	}

}
