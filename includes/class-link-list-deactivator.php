<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Link_List_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// Get links page id.
		$links_page_id = get_option( 'links_save_page_id' );

		// Check if the links page id exists.
		if ( $links_page_id ) {

			// Delete links page.
			wp_delete_post( $links_page_id, true );

			// Delete links page id record in the database.
			delete_option( 'links_save_page_id' );

		}
	}

}
