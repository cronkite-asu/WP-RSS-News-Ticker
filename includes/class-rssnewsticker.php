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

namespace ASU\CSJ\Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
	 * TIcker
	 * @var [type]
	 */
	protected $ticker;

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

		$this->load_dependencies();

		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Rssnewsticker_Loader. Orchestrates the hooks of the plugin.
	 * - Rssnewsticker_i18n. Defines internationalization functionality.
	 * - Rssnewsticker_Admin. Defines all hooks for the admin area.
	 * - Rssnewsticker_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the settings and admin pages of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-settings-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-settings-ticker.php';

		/**
		 * The classes responsible for remote html connections.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-remote.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-remote-json.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-remote-ap-headlines.php';

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		add_action('init', array( $this, 'add_rss_feed'));
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->settings = new SettingsPage( $this->get_plugin_name(), $this->get_version() );
		$this->ticker = new SettingsTicker( $this->get_plugin_name(), $this->get_version() );
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

	public function add_rss_feed() {
		add_feed($this->settings->get_option('feed_name'), array( $this, 'render_rss_feed' ));
	}

	public function render_rss_feed () {
		$localnews = $this->fetch_local_headlines();
		$apnews = $this->fetch_ap_headlines();
		$lines = array_merge($localnews, $apnews);

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
			<description><![CDATA[<?php echo sanitize_text_field($line) ?>]]></description>
<?php			do_action('rss2_item'); ?>
		</item>
<?php		}; ?>
	</channel>
</rss>
<?php
	}

	public function fetch_local_headlines() {
		$text = $this->ticker->get_option('ticker_text');

		return $text;
	}

	public function fetch_ap_headlines() {
		$enabled = $this->settings->get_option('ap_enable');
		$productid = $this->settings->get_option('ap_productid');
		$page_size = $this->settings->get_option('ap_page_size');
		$api_key = $this->settings->get_option('ap_api_key');
		$pre_feed = $this->settings->get_option('ap_pre_feed');

		$headlines = [];

		if ($enabled === 1) {
			$remote_request = new RemoteAPHeadlines( $this->get_plugin_name(), $this->get_version(), $productid, $api_key, $page_size );
			$headlines = $remote_request->get_ap_headlines();

			array_unshift($headlines, $pre_feed);
		}
		return $headlines;
	}

}
