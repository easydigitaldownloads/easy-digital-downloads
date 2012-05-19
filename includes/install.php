<?php
/**
 * Install
 *
 * @package     Easy Digital Downloads
 * @subpackage  Install
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Install
 *
 * Runs on plugin install.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_install() {
	global $wpdb, $edd_options;
	
	// Check if the purchase page option exists
	if(!isset($edd_options['purchase_page'])) {
	    // Checkout
		$checkout = wp_insert_post(
			array(
				'post_title' => __('Checkout', 'edd'),
				'post_content' => '[download_checkout]',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
		// Success
		$success = wp_insert_post(
			array(
				'post_title' => __('Purchase Confirmation', 'edd'),
				'post_content' => __('Thank you for your purchase!', 'edd'),
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
		// History
		$history = wp_insert_post(
			array(
				'post_title' => __('Purchase History', 'edd'),
				'post_content' => '[download_history]',
				'post_status' => 'publish',
				'post_author' => 1,
				'post_type' => 'page'
			)
		);
	}
	
	// Setup the download custom post type
	edd_setup_download_post_type();
	
	// Setup the download custom taxonomies
	edd_setup_download_taxonomies();
	
	// Clear permalinks
	flush_rewrite_rules();
}
register_activation_hook(EDD_PLUGIN_FILE, 'edd_install');