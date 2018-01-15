<?php
/**
 * Database specific functionality.
 *
 * @since         4.2.0
 * @package       WP_NoExternalLinks
 * @subpackage    WP_NoExternalLinks/Includes
 * @author        SteamerDevelopment
 */

class WP_NoExternalLinks_Database {

    /**
     * Migrates any database changes.
     *
     * @since      4.2.0
     * @param      string    $table_name    null
     * @return     array
     */
    public static function migrate( $table_name = null ) {

        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();
        $sql = array();

        if ( null === $table_name || 'external_links_logs' === $table_name ) {
            $new_table_name = $wpdb->prefix . 'external_links_logs';
            $sql[] = "CREATE TABLE $new_table_name (
                   id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                   url varchar(255) NOT NULL,
                   referring_url varchar(255),
                   user_agent varchar(255),
                   ip_address varchar(255),
                   restricted varchar(255),
                   date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                   PRIMARY KEY  (id)
               ) $charset_collate;";
        }

        if ( null === $table_name || 'external_links_masks' === $table_name ) {
            $new_table_name = $wpdb->prefix . 'external_links_masks';
            $sql[] = "CREATE TABLE $new_table_name (
                   id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                   url varchar(255) NOT NULL,
                   mask varchar(255) NOT NULL,
                   short_url varchar(255) NOT NULL,
                   PRIMARY KEY  (id)
               ) $charset_collate;";
        }

        return dbDelta( $sql );

    }

}
