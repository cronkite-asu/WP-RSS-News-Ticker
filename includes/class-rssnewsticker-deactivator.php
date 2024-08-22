<?php

namespace RSS_News_Ticker;

/**
 * Fired during plugin deactivation
 *
 * @link       https://asu.edu
 * @since      1.3.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.3.0
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 * @author     jleggat <jleggat@asu.edu>
 */
class Rssnewsticker_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.3.0
	 */
	public static function deactivate($plugin_name) {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-transients.php';

		Rssnewsticker_Transients::delete_transients_with_prefix($plugin_name . '_');
	}

}
