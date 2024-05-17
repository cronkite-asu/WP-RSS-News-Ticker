<?php

/**
 *
 * @since             1.0.0
 * @package           Rssnewsticker
 *
 * @wordpress-plugin
 * Plugin Name:       RSS News Ticker
 * Description:       Create a RSS feed using each line of text as the description field of RSS entry.
 * Version:           1.2.1
 * Author:            Jeremy Leggat
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rssnewsticker
 * GitHub Plugin URI: https://github.com/cronkite-asu/WP-RSS-News-Ticker
 * Primary Branch:    main
 */

namespace ASU\CSJ\Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RSSNEWSTICKER_VERSION', '1.2.1' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rssnewsticker.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rssnewsticker() {

	$plugin = new Rssnewsticker();
	$plugin->run();

}
run_rssnewsticker();
