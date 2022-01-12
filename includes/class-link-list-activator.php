<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class Link_List_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Links Page Arguments
		$links_page_args = array(
			'post_title'   => __( 'Links', 'links-save' ),
			'post_content' => '[linklist]',
			'post_status'  => 'publish',
			'post_type'    => 'page'
		);
		// Insert the links page and get its id.
		$links_page_id = wp_insert_post( $links_page_args );

		// Save links page id to the database.
		add_option( 'links_save_page_id', $links_page_id );
	}

}