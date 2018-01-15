<?php
/**
 * Admin log table functionality.
 *
 * @since         4.0.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Admin
 * @author        SteamerDevelopment
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WP_NoExternalLinks_Admin_Log_Table extends WP_List_Table {

    /**
     * The ID of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The options prefix of this plugin.
     *
     * @since    4.0.0
     * @access   private
     * @var      string    $options_prefix    The options prefix of this plugin.
     */
    private $options_prefix;

    /**
     * Initialize the class and set its properties.
     *
     * @since    4.0.0
     * @param    string    $plugin_name       The name of the plugin.
     * @param    string    $options_prefix    The options prefix of the plugin.
     */
    public function __construct( $plugin_name, $options_prefix ) {

        $this->plugin_name = $plugin_name;
        $this->options_prefix = $options_prefix;

        parent::__construct(
            array(
                'singular' => __( 'Log', $this->plugin_name ),
                'plural'   => __( 'Logs', $this->plugin_name ),
                'ajax'     => false
            )
        );

        add_action( 'admin_notices', array( $this, 'log_delete_notice' ) );

    }

    /**
     * Retrieve external links log data from the database
     *
     * @since     4.0.0
     * @param     int     $per_page
     * @param     int     $page_number
     * @return    mixed
     */
    public function get_logs( $per_page = 5, $page_number = 1 ) {

        global $wpdb;

        $table_name = $wpdb->prefix . 'external_links_logs';

        $sql = "SELECT * FROM $table_name";

        if ( ! empty( $_REQUEST[ 'orderby' ] ) ) {
            $order_by = $_REQUEST[ 'orderby' ];

            if ( 'title' === $_REQUEST[ 'orderby' ] ) {
                $order_by = 'url';
            } elseif ( 'datetime' === $_REQUEST[ 'orderby' ] ) {
                $order_by = 'date';
            }

            $sql .= ' ORDER BY ' . esc_sql( $order_by );
            $sql .= ! empty( $_REQUEST[ 'order' ] ) ? ' ' . esc_sql( $_REQUEST[ 'order' ] ) : ' ASC';
        } else {
            $sql .= ' ORDER BY date DESC';
        }

        $sql .= " LIMIT $per_page";

        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );

        return $result;

    }

    /**
     * Delete a log record.
     *
     * @since     4.0.0
     * @param     int    $id     log ID
     * @return    bool
     */
    public function delete_log( $id ) {

        global $wpdb;

        $delete_count = $wpdb->delete(
            $wpdb->prefix . 'external_links_logs',
            array( 'ID' => $id ),
            array( '%d' )
        );

        if ( $delete_count > 0 ) {
            return true;
        }

        return false;

    }

    /**
     * Returns the count of records in the database.
     *
     * @since    4.0.0
     * @return   null|string
     */
    public function record_count() {

        global $wpdb;

        $table_name = $wpdb->prefix . 'external_links_logs';

        $sql = "SELECT COUNT(id) FROM $table_name";

        return $wpdb->get_var( $sql );

    }

    /**
     * Text displayed when no customer data is available
     *
     * @since    4.0.0
     */
    public function no_items() {

        _e( 'No logs available.', $this->plugin_name );

    }

    /**
     * Render a column when no column specific method exists.
     *
     * @since     4.0.0
     * @param     array     $item
     * @param     string    $column_name
     * @return    mixed
     */
    public function column_default( $item, $column_name ) {

        switch ( $column_name ) {
            case 'title':
                $delete_nonce = wp_create_nonce( $this->options_prefix . 'delete_log' );

                $title = '<strong>' . $item[ 'url' ] . '</strong>';

                $actions = array(
                    'delete' => sprintf(
                        '<a href="?page=%s&action=%s&log=%s&_wpnonce=%s">Delete</a>',
                        esc_attr( $_REQUEST[ 'page' ] ),
                        'delete',
                        absint( $item[ 'id' ] ),
                        $delete_nonce
                    )
                );

                return $title . $this->row_actions( $actions );
            case 'referring_url':
                return $item[ 'referring_url' ];
            case 'user_agent':
                return $item[ 'user_agent' ];
            case 'ip_address':
                return $item[ 'ip_address' ];
            case 'datetime':
                return $item[ 'date' ];
            default:
                return print_r( $item, true );
        }

    }

    /**
     * Render the bulk edit checkbox
     *
     * @since     4.0.0
     * @param     array     $item
     * @return    string
     */
    function column_cb( $item ) {

        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );

    }

    /**
     *  Associative array of columns
     *
     * @since     4.0.0
     * @return    array    $columns
     */
    function get_columns() {

        $columns = array(
            'cb'      => '<input type="checkbox" />',
            'title'    => __( 'URL', $this->plugin_name ),
            'referring_url' => __( 'Referring URL', $this->plugin_name ),
            'user_agent' => __( 'User Agent', $this->plugin_name ),
            'ip_address' => __( 'IP Address', $this->plugin_name ),
            'datetime' => __( 'Date/Time', $this->plugin_name )
        );

        return $columns;

    }

    /**
     * Columns to make sortable.
     *
     * @since     4.0.0
     * @return    array     $sortable_columns
     */
    public function get_sortable_columns() {

        $sortable_columns = array(
            'title' => array( 'title', true ),
            'referring_url' => array( 'referring_url', true ),
            'user_agent' => array( 'user_agent', true ),
            'ip_address' => array( 'ip_address', true ),
            'datetime' => array( 'datetime', true )
        );

        return $sortable_columns;

    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @since     4.0.0
     * @return    array    $actions
     */
    public function get_bulk_actions() {

        $actions = array(
            'bulk-delete' => 'Delete'
        );

        return $actions;

    }

    /**
     * Handles data query and filter, sorting, and pagination.
     *
     * @since     4.0.0
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $per_page     = $this->get_items_per_page( 'logs_per_page' );
        $current_page = $this->get_pagenum();
        $total_items  = $this->record_count();

        $this->set_pagination_args(
            array(
                'total_items' => $total_items,
                'per_page'    => $per_page
            )
        );

        $this->items = $this->get_logs( $per_page, $current_page );

    }

    /**
     * Processes any bulk actions.
     *
     * @since     4.0.0
     */
    public function process_bulk_action() {

        $redirect = wp_get_raw_referer();

        if ( 'delete' === $this->current_action() ) {

            $nonce = esc_attr( $_REQUEST['_wpnonce'] );

            if ( ! wp_verify_nonce( $nonce, $this->options_prefix . 'delete_log' ) ) {
                wp_die( __( 'Are you sure you want to do this?' ) );
            } else {
                $delete = $this->delete_log( absint( $_GET['log'] ) );

                $delete_count = 0;
                if ( $delete ) {
                    ++$delete_count;
                }

                $redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

                wp_redirect( $redirect );
                exit;
            }

        }

        if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
            || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
        ) {

            $delete_ids = esc_sql( $_POST['bulk-delete'] );

            $delete_count = 0;
            foreach ( $delete_ids as $id ) {
                $delete = $this->delete_log( $id );

                if ( $delete ) {
                    ++$delete_count;
                }
            }

            $redirect = add_query_arg( 'delete_count', $delete_count, $redirect );

            wp_redirect( $redirect );
            exit;
        }

    }

    /**
     * Display delete notice.
     *
     * @since    4.2.0
     */
    function log_delete_notice() {
        $delete_count = isset( $_GET['delete_count'] ) ? ( int ) $_GET['delete_count'] : 0;

        if ( 1 == $delete_count ) {
            ?>
            <div class="notice notice-success">
                <p>
                    <?php _e(
                        'Log deleted.',
                        $this->plugin_name
                    ); ?>
                </p>
            </div>
            <?php
        } elseif ( $delete_count > 1 ) {
            ?>
            <div class="notice notice-success">
                <p>
                    <?php echo sprintf(
                            __( '%s logs deleted.', $this->plugin_name ), number_format_i18n( $delete_count )
                    ); ?>
                </p>
            </div>
            <?php
        }
    }

}