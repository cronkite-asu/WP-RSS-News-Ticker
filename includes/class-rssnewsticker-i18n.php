<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.3.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.3.0
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 * @author     Jeremy Leggat <jleggat@asu.edu>
 */
class Rssnewsticker_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.3.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'rssnewsticker',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}

}
