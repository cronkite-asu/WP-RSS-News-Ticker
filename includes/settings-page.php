<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsPage extends Settings {

	public function __construct() {
		$this->id = 'rssnewsticker';
		$this->page_title = 'Ticker Feed';
		$this->menu_title = 'Ticker';

		$this->define_fields();

		parent::__construct();
	}

	protected function define_fields() {
		$this->fields['feed_config_section'] = [
			'name' => 'feed_config_section',
			'title' => 'Feed Configuration',
			'description' => 'Settings for the RSS feed.',
			'type' => 'section'
		];


		$this->fields['feed_name'] = [
			'name' => 'feed_name',
			'title' => 'Feed Name',
			'description' => 'Keep this name simple as it is used to forms your this feed URL. The feed will be available at ' . site_url('/feed/'),
			'type' => 'text',
			'default' => 'ticker',
			'section' => 'feed_config_section'
		];

		$this->fields['local_config_section'] = [
			'name' => 'local_config_section',
			'title' => 'Local Feed Configuration',
			'description' => 'Settings for the local feed.',
			'type' => 'section'
		];


		$this->fields['school_news'] = [
			'name' => 'school_news',
			'title' => 'School News',
			'description' => 'News from the school to display on in the feed. ',
			'type' => 'textarea',
			'section' => 'local_config_section'
		];

		$this->fields['ap_config_section'] = [
			'name' => 'ap_config_section',
			'title' => 'AP Configuration',
			'description' => 'Settings for AP News feed.',
			'type' => 'section'
		];

		$this->fields['ap_productid'] = [
			'name' => 'ap_productid',
			'title' => 'AP product ID',
			'description' => 'AP product ID.',
			'type' => 'text',
			'section' => 'ap_config_section'
		];

		$this->fields['ap_api_key'] = [
			'name' => 'ap_api_key',
			'title' => 'API key',
			'description' => 'Key to send for API auth.',
			'type' => 'text',
			'class' => 'regular-text',
			'section' => 'ap_config_section'
		];

		$this->fields['ap_page_size'] = [
			'name' => 'ap_page_size',
			'title' => 'Page Size',
			'description' => 'Number of news stories to retrieve.',
			'type' => 'number',
			'class' => 'tiny-text',
			'default' => 5,
			'max' => 10,
			'section' => 'ap_config_section'
		];

		$this->fields['ap_pre_feed'] = [
			'name' => 'ap_pre_feed',
			'title' => 'Intro Text',
			'description' => 'Text to display before the AP headlines.',
			'type' => 'text',
			'class' => 'large-text',
			'default' => 'The latest headlines from the Associated Press',
			'section' => 'ap_config_section'
		];
	}
}
