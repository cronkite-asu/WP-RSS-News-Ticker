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
include_once 'includes/settings-page.php';
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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rssnewsticker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rssnewsticker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rssnewsticker-admin.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_admin_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rssnewsticker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rssnewsticker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rssnewsticker-admin.js', [ 'jquery' ], $this->version, false );

	}

	public function add_menu() {
		// Add the menu item and page
		$page_title = 'RSS News Ticker Settings Page';
		$menu_title = 'RSS News Ticker';
		$capability = 'manage_options';

		add_submenu_page('options-general.php', $page_title, $menu_title, $capability, $this->plugin_name, array( $this, 'page_settings' ));
	}

	public function page_settings() {
		include(plugin_dir_path(__FILE__) . 'partials/rssnewsticker-admin-display.php');
	}

	public function add_sections() {
		add_settings_section('config_section', 'Configuration', array( $this, 'section_callback' ), $this->plugin_name);
	}

	public function section_callback($arguments) {
		switch($arguments['id']) {
			case 'config_section':
				echo 'Set to read text from an Associated Press to RSS feed';
				break;
			case 'test_section':
				echo 'Test reading feed from a Associated Press to RSS feed';
				break;
		}
	}

	public function add_fields() {
		$fields = array(
			array(
				'uid' => $this->plugin_name . '_feed_name',
				'label' => 'Feed Name',
				'section' => 'config_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'feedName',
				'helper' => 'Keep this name simple as it is used to forms your this feed URL.',
				'supplemental' => sprintf("The feed will be available at %s<em>%s</em>", site_url('/feed/'), get_option($this->plugin_name . '_feed_name')),
				'default' => 'ticker'
			),
			array(
				'uid' => $this->plugin_name . '_ap_productid',
				'label' => 'AP product ID',
				'section' => 'config_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'ID',
				'helper' => 'AP product ID.',
				'default' => ''
			),
			array(
				'uid' => $this->plugin_name . '_ap_page_size',
				'label' => 'Required Request Header',
				'section' => 'config_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'headerName',
				'helper' => 'Number of news stories to retrieve.',
				'default' => '5'
			),
			array(
				'uid' => $this->plugin_name . '_ap_api_key',
				'label' => 'API key',
				'section' => 'config_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'apiKey',
				'helper' => 'Key to send for API auth.',
				'default' => ''
			),
			array(
				'uid' => $this->plugin_name . '_ap_pre_feed',
				'label' => 'Intro Text',
				'section' => 'config_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'Prefeed',
				'helper' => 'Text to display before the AP headlines.',
				'supplemental' => 'Text to display before the AP headlines.',
				'default' => 'The latest headlines from the Associated Press'
			)
		);
		foreach($fields as $field) {
			add_settings_field($field['uid'], $field['label'], array( $this, 'field_callback' ), $this->plugin_name, $field['section'], $field);
			register_setting($this->plugin_name, $field['uid']);
		}
	}

	public function field_callback($arguments) {
		$value = get_option($arguments['uid']); // Get the current value, if there is one
		if(! $value) { // If no value exists
			$value = $arguments['default']; // Set to our default
		}

		// Check which type of field we want
		switch($arguments['type']) {
			case 'text': // If it is a text field
				printf('<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
				break;
			case 'button': // If it is a button
				printf('<input name="%1$s" id="%1$s" type="%2$s" value="%3$s" />', $arguments['uid'], $arguments['type'], $value);
				break;
		}

		// If there is help text
		if(!empty($arguments['helper'])) {
			printf('<span class="helper"> %s</span>', $arguments['helper']); // Show it
		}

		// If there is supplemental text
		if(!empty($arguments['supplemental'])) {
			printf('<p class="description">%s</p>', $arguments['supplemental']); // Show it
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rssnewsticker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rssnewsticker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rssnewsticker-public.css', [], $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_public_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rssnewsticker_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rssnewsticker_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rssnewsticker-public.js', [ 'jquery' ], $this->version, false );

	}

	public function add_rss_feed() {
		add_feed($this->settings->get_option('feed_name'), array( $this, 'rss_callback' ));
	}

	public function rss_callback() {
		include(plugin_dir_path(__FILE__) . 'partials/rssnewsticker-public-display.php');
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
