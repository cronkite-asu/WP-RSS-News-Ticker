<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class RemoteAPHeadlines extends RemoteJSON {

	/**
	* AP product id to request
	* @var string
	*/
	protected $productid = "";

	/**
	* AP API key for request
	* @var string
	*/
	protected $api_key = "";

	/**
	* Number of headlines to request
	* @var integer
	*/
	protected $page_size = "";

	/**
	* Headlines returned by the request
	* @var array
	*/
	protected $headlines = array();

	/**
	* Creating the object
	* @param string $url
	* @param array  $array
	* @param string $method
	*/
	public function __construct( $productid, $api_key, $page_size = 5 ) {
		$this->productid = $productid;
		$this->api_key = $api_key;
		$this->page_size = $page_size;
		$this->url = sprintf("https://api.ap.org/media/v/content/feed?q=productid:%s&include=headline&in_my_plan=true&page_size=%d", $this->productid, $this->page_size);
		$this->arguments['headers']['x-api-key'] = $this->api_key;
		parent::__construct($this->url, $this->arguments, "get");
	}

	/**
	* Run the main method run() and prepare the headlines.
	**/
	public function run() {
		parent::run();
		$this->headlines = $this->parse_ap_headlines($this->body);
	}

	public function get_ap_headlines() {
		return $this->headlines;
	}

	protected function parse_ap_headlines($json) {
		$obj = json_decode($json);
		$items = $obj->data->items;
		$lines = [];
		foreach ($items as $item) {
			array_push($lines, $item->item->headline);
		}
		return $lines;
	}
}
