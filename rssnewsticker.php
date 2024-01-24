<?php

/**
 *
 * @since             1.0.0
 * @package           Rssnewsticker
 *
 * @wordpress-plugin
 * Plugin Name:       RSS News Ticker
 * Description:       Create a RSS feed using each line of text as the description field of RSS entry.
 * Version:           1.0.0
 * Author:            Jeremy Leggat
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rssnewsticker
 */

namespace Rssnewsticker;
include_once 'includes/settings.php';
include_once 'includes/settings-meta-box.php';
include_once 'includes/settings-page.php';
include_once 'includes/settings-ticker.php';
include_once 'includes/remote.php';
include_once 'includes/remote_json.php';
include_once 'includes/remote_ap_headlines.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RSSNEWSTICKER_VERSION', '1.0.0' );


class Rssnewsticker {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Settings
	 * @var [type]
	 */
	protected $settings;

	/**
	 * Settings
	 * @var [type]
	 */
	protected $page;

	/**
	 * Settings
	 * @var [type]
	 */
	protected $metabox;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'RSSNEWSTICKER_VERSION' ) ) {
			$this->version = RSSNEWSTICKER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'rssnewsticker';
		$this->settings = new SettingsPage();
		$this->metabox = new SettingsTicker();

		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ));
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ));
		add_action('init', array( $this, 'add_rss_feed'));
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rssnewsticker-admin.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rssnewsticker-admin.js', [ 'jquery', 'wp-util' ], $this->version, true );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rssnewsticker-public.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rssnewsticker-public.js', [ 'jquery' ], $this->version, true );

	}

	public function add_rss_feed() {
		add_feed($this->settings->get_option('feed_name'), array( $this, 'render_rss_feed' ));
	}

	public function render_rss_feed () {
		$schoolnews = $this->fetch_local_headlines();
		$apnews = $this->fetch_ap_headlines();
		$lines = array_merge($schoolnews, $apnews);

		define('DONOTCACHEPAGE', true);
		header('Content-Type: application/rss+xml');
		echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.">";
		do_action('rss_tag_pre', 'rss2');
?>

<rss version="2.0" <?php do_action('rss2_ns'); ?>>
	<channel>
		<title><?php bloginfo_rss('name'); ?> - Ticker Feed</title>
		<link><?php bloginfo_rss('url') ?></link>
		<description><?php bloginfo_rss('description') ?></description>
<?php		do_action('rss2_head'); ?>

<?php
		foreach ($lines as $line) {
			$line = sanitize_text_field($line);
?>
		<item>
			<description><![CDATA[<?php echo wp_filter_nohtml_kses($line) ?>]]></description>
<?php			do_action('rss2_item'); ?>
		</item>
<?php		}; ?>
	</channel>
</rss>
<?php
	}

	public function fetch_local_headlines() {
		$text = $this->settings->get_option('school_news');

		$lines = explode(PHP_EOL, $text);

		return $lines;
	}

	public function fetch_ap_headlines() {
		$productid = $this->settings->get_option('ap_productid');
		$page_size = $this->settings->get_option('ap_page_size');
		$api_key = $this->settings->get_option('ap_api_key');
		$pre_feed = $this->settings->get_option('ap_pre_feed');

		$remote_request = new RemoteAPHeadlines( $productid, $api_key, $page_size );
		$remote_request->run();

		$headlines = $remote_request->get_ap_headlines();

		array_unshift($headlines, $pre_feed);
		return $headlines;
	}

}

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

}
run_rssnewsticker();
