<?php

/**
 * Renders RSS Feed for the plugin
 *
 * @since      1.3.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 */

/**
 * Renders RSS Feed for the plugin.
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/includes
 * @author     Jeremy Leggat <jleggat@asu.edu>
 */
class Rssnewsticker_Public_RSS {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.3.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.3.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Ticker
	 * @var [type]
	 */
	protected $ticker;

	/**
	 * Settings
	 * @var [type]
	 */
	protected $settings;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.3.0
	 */
	public function __construct($plugin_name, $version, $ticker, $settings) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->ticker = $ticker;
		$this->settings = $settings;

	}

	public function render_rss_feed() {
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
			$remote_request = new Rssnewsticker_Remote_AP_Headlines( $this->plugin_name, $this->version, $productid, $api_key, $page_size );
			$headlines = $remote_request->read_ap_headlines();

			array_unshift($headlines, $pre_feed);
		}
		return $headlines;
	}

}
