<?php

class Rssnewsticker {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.3.0
	 * @access   protected
	 * @var      Rssnewsticker_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

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
	 * Ticker
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

		if ( defined( 'RSSNEWSTICKER_PLUGIN_NAME' ) ) {
			$this->plugin_name = RSSNEWSTICKER_PLUGIN_NAME;
		} else {
			$this->plugin_name = 'rssnewsticker';
		}

		$this->load_dependencies();
		$this->set_locale();
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
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rssnewsticker-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rssnewsticker-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-transients.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-rssnewsticker-public-rss.php';

		/**
		 * The class responsible for orchestrating the settings and admin pages of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rssnewsticker-admin-settings.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rssnewsticker-admin-settings-page.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-rssnewsticker-admin-settings-ticker.php';

		/**
		 * The classes responsible for remote html connections.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-remote.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-rssnewsticker-remote-ap-headlines.php';

		$this->loader = new Rssnewsticker_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Rssnewsticker_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.3.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Rssnewsticker_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Rssnewsticker_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Rssnewsticker_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $this, 'add_rss_feed' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->settings = new Rssnewsticker_Admin_Settings_Page( $this->get_plugin_name(), $this->get_version() );
		$this->ticker = new Rssnewsticker_Admin_Settings_Ticker( $this->get_plugin_name(), $this->get_version() );
		$this->loader->run();
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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.3.0
	 * @return    Rssnewsticker_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
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
	 * Retrieve the ticker options.
	 *
	 * @since     1.3.0
	 * @return    ticker    The ticker options.
	 */
	public function get_ticker() {
		return $this->ticker;
	}

	/**
	 * Retrieve the plugin's admin settings.
	 *
	 * @since     1.3.0
	 * @return    ticker    The admin settings.
	 */
	public function get_settings() {
		return $this->settings;
	}

	public function add_rss_feed() {
		$rss_feed = new Rssnewsticker_Public_RSS( $this->get_plugin_name(), $this->get_version(), $this->get_ticker(), $this->get_settings() );
		add_feed($this->settings->get_option('feed_name'), array( $rss_feed, 'render_rss_feed' ));
	}

}
