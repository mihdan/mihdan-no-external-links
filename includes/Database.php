<?php
/**
 * Database specific functionality.
 *
 * @since         4.2.0
 * @package       mihdan-no-external-links
 * @subpackage    mihdan-no-external-links/Includes
 * @author        mihdan
 */

namespace Mihdan\No_External_Links;

/**
 * Class Database.
 */
class Database {

	/**
	 * Migrates any database changes.
	 *
	 * @since      4.2.0
	 *
	 * @param string $table_name Table name.
	 *
	 * @return     array
	 */
	public static function migrate( $table_name = null ): array {

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();
		$sql             = [];

		if ( null === $table_name || 'external_links_logs' === $table_name ) {
			$new_table_name = $wpdb->prefix . 'external_links_logs';
			$sql[]          = "CREATE TABLE $new_table_name (
                   id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                   url varchar(190000) NOT NULL,
                   referring_url varchar(190000),
                   user_agent varchar(255),
                   ip_address varchar(255),
                   restricted varchar(255),
                   date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                   PRIMARY KEY  (id)
               ) $charset_collate;";
		}

		if ( null === $table_name || 'external_links_masks' === $table_name ) {
			$new_table_name = $wpdb->prefix . 'external_links_masks';
			$sql[]          = "CREATE TABLE $new_table_name (
                   id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                   url varchar(190000) NOT NULL,
                   mask varchar(255) NOT NULL,
                   short_url varchar(190000) NOT NULL,
                   PRIMARY KEY  (id)
               ) $charset_collate;";
		}

		return dbDelta( $sql );

	}

}
