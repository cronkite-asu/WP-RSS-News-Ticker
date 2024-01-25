<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class SettingsTicker extends SettingsMetaBox {

	/**
	 * Metabox
	 * @var [type]
	 */
	protected $metabox;

	public function __construct() {
		$this->id = 'cronkiteticker';
		$this->page_title = 'Cronkite Ticker';
		$this->menu_title = 'Cronkite Ticker';
		$this->icon_url = 'dashicons-rss';
		$this->position = 30;

		parent::__construct();
	}

	protected function define_fields() {
		$this->fields['ticker_config_section'] = [
			'name' => 'ticker_config_section',
			'title' => 'Ticker Configuration',
			'description' => 'Settings for the RSS Ticker feed.',
			'type' => 'section'
		];

		$this->fields['ticker_text'] = [
			'name' => 'ticker_text',
			'title' => 'Ticker Text',
			'description' => 'Custom text to display on the news ticker.',
			'type' => 'repeater',
			'section' => 'ticker_config_section'
		];
	}

}
