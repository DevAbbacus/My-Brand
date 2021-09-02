<?php

/**
 * Fired during plugin activation
 *
 * @link       http://techxperts.co.in/
 * @since      1.0.0
 *
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Xperts_Crms_Wc
 * @subpackage Xperts_Crms_Wc/includes
 * @author     Rajeev <rajeev@techxperts.co.in>
 */
class Xperts_Crms_Wc_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'crms_category_map';
        $table_name_color = $wpdb->prefix . 'crms_color_map';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,		
            crms_category text NOT NULL,
            category_mappings text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        $sql_color = "CREATE TABLE $table_name_color (
            id int(11) NOT NULL AUTO_INCREMENT,		
            crms_color text NOT NULL,
            color_mappings text NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( [$sql, $sql_color]  );
	}

}
