<?php
/**
 * Admin mask table functionality.
 *
 * @since         4.2.0
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
 * Class MaskTable.
 */
class MaskTable extends WP_List_Table {

	/**
	 * The ID of this plugin.
	 *
	 * @since    4.2.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The options prefix of this plugin.
	 *
	 * @since    4.2.0
	 * @access   private
	 * @var      string $options_prefix The options prefix of this plugin.
	 */
	private $options_prefix;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    4.2.0
	 *
	 * @param string $plugin_name    The name of the plugin.
	 * @param string $options_prefix The options prefix of the plugin.
	 */
	public function __construct( $plugin_name, $options_prefix ) {

		$this->plugin_name    = $plugin_name;
		$this->options_prefix = $options_prefix;

		parent::__construct(
			[
				'singular' => __( 'Mask', 'mihdan-no-external-links' ),
				'plural'   => __( 'Masks', 'mihdan-no-external-links' ),
				'ajax'     => false,
			]
		);

		add_action( 'admin_notices', [ $this, 'mask_delete_notice' ] );

	}

	/**
	 * Retrieve external links mask data from the database
	 *
	 * @since     4.2.0
	 *
	 * @param int $per_page    Number of items per page.
	 * @param int $page_number Page number.
	 *
	 * @return array
	 */
	public function get_masks( $per_page = 5, $page_number = 1 ): array {

		global $wpdb;

		$order_by = 'id';
		$order    = 'ASC';
		$offset   = ( $page_number - 1 ) * $per_page;
		$mapping  = [
			'title'   => 'url',
			'mask'    => 'mask',
			'numeric' => 'id',
		];

		// Nonce is verified in the WP_List_table class.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$order    = isset( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : $order;
		$orderby  = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
		$order_by = array_key_exists( $orderby, $mapping ) ? $mapping[ $orderby ] : $order_by;
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (array) $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}external_links_masks ORDER BY %s %s LIMIT %d OFFSET %d",
				[
					$order_by,
					$order,
					$per_page,
					$offset,
				]
			),
			ARRAY_A
		);

	}

	/**
	 * Delete a mask record.
	 *
	 * @since     4.2.0
	 *
	 * @param int $id Mask ID.
	 *
	 * @return bool
	 */
	public function delete_mask( $id ): bool {

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$delete_count = $wpdb->delete(
			$wpdb->prefix . 'external_links_masks',
			[ 'ID' => $id ],
			[ '%d' ]
		);

		return $delete_count > 0;

	}

	/**
	 * Returns the count of records in the database.
	 *
	 * @since    4.2.0
	 *
	 * @return int
	 * @noinspection SqlResolve
	 */
	public function record_count(): int {

		global $wpdb;

		$table_name = $wpdb->prefix . 'external_links_masks';

		$sql = "SELECT COUNT(id) FROM $table_name";

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );

	}

	/**
	 * Text displayed when no customer data is available
	 *
	 * @since    4.2.0
	 */
	public function no_items(): void {

		esc_html_e( 'No masks available.', $this->plugin_name );

	}

	/**
	 * Render a column when no column specific method exists.
	 *
	 * @since     4.2.0
	 *
	 * @param array  $item        Item.
	 * @param string $column_name Column name.
	 *
	 * @return string|null
	 */
	public function column_default( $item, $column_name ): ?string {

		switch ( $column_name ) {
			case 'title':
				$delete_nonce = wp_create_nonce( $this->options_prefix . 'delete_mask' );

				$title = '<strong>' . $item['url'] . '</strong>';

				$page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : '';
				$actions = [
					'delete' => sprintf(
						'<a href="?page=%s&action=%s&mask=%s&_wpnonce=%s">Delete</a>',
						esc_attr( $page ),
						'delete',
						absint( $item['id'] ),
						$delete_nonce
					),
				];

				return $title . $this->row_actions( $actions );
			case 'mask':
				return (string) $item['mask'];
			case 'numeric':
				return (string) $item['id'];
			default:
				break;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		return (string) print_r( $item, true );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @since     4.2.0
	 *
	 * @param array $item Item.
	 *
	 * @return string
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
	 * @since     4.2.0
	 *
	 * @return array $columns
	 */
	public function get_columns(): array {

		return [
			'cb'      => '<input type="checkbox" />',
			'title'   => __( 'URL', $this->plugin_name ),
			'mask'    => __( 'Mask' ),
			'numeric' => __( 'Numeric' ),
		];

	}

	/**
	 * Columns to make sortable.
	 *
	 * @since     4.2.0
	 *
	 * @return array $sortable_columns
	 */
	public function get_sortable_columns(): array {

		return [
			'title'   => [ 'title', true ],
			'mask'    => [ 'mask', true ],
			'numeric' => [ 'numeric', true ],
		];

	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @since     4.2.0
	 *
	 * @return array $actions
	 */
	public function get_bulk_actions(): array {

		return [ 'bulk-delete' => 'Delete' ];

	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 *
	 * @since     4.2.0
	 */
	public function prepare_items(): void {

		$this->_column_headers = $this->get_column_info();

		$per_page     = $this->get_items_per_page( 'masks_per_page' );
		$current_page = $this->get_pagenum();
		$total_items  = $this->record_count();

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->items = $this->get_masks( $per_page, $current_page );

	}

	/**
	 * Processes any bulk actions.
	 *
	 * @since        4.2.0
	 *
	 * @noinspection ForgottenDebugOutputInspection
	 */
	public function process_bulk_action(): void {

		$redirect = wp_get_raw_referer();

		$nonce = isset( $_REQUEST['_wpnonce'] )
			? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) )
			: '';

		if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, $this->options_prefix . 'delete_mask' ) ) {
			wp_die( esc_html__( 'Are you sure you want to do this?' ) );
		}

		if ( 'delete' === $this->current_action() ) {

			$mask   = isset( $_GET['mask'] ) ? absint( $_GET['mask'] ) : '';
			$delete = $this->delete_mask( $mask );

			$delete_count = 0;
			if ( $delete ) {
				++ $delete_count;
			}

			$redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

			wp_safe_redirect( $redirect );
			exit;
		}

		$action  = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$action2 = isset( $_POST['action2'] ) ? sanitize_text_field( wp_unslash( $_POST['action2'] ) ) : '';

		if ( 'bulk-delete' === $action || 'bulk-delete' === $action2 ) {

			$delete_count = 0;
			$delete_ids   = isset( $_POST['bulk-delete'] ) ?
				array_map( 'intval', (array) wp_unslash( $_POST['bulk-delete'] ) ) :
				[];

			foreach ( $delete_ids as $id ) {
				if ( $this->delete_mask( $id ) ) {
					$delete_count ++;
				}
			}

			$redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

			wp_safe_redirect( $redirect );
			exit;
		}

	}

	/**
	 * Display delete notice.
	 *
	 * @since    4.2.0
	 */
	public function mask_delete_notice(): void {
		$delete_count = isset( $_GET['delete_count'] ) ? (int) $_GET['delete_count'] : 0;

		if ( 1 === $delete_count ) {
			?>
			<div class="notice notice-success">
				<p>
					<?php
					esc_html_e(
						'Mask deleted.',
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
					echo esc_html(
						sprintf(
						// translators: 1: Count.
							__( '%s masks deleted.', $this->plugin_name ),
							number_format_i18n( $delete_count )
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	}

}
