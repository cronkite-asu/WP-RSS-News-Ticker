<?php

class Rssnewsticker_Remote_AP_Headlines extends Rssnewsticker_Remote {

	/**
	* AP product id to request
	* @var string
	*/
	const ENDPOINT = "https://api.ap.org/media/v/content/feed";

	/**
	* AP API key for request
	* @var string
	*/
	protected $api_key = "";

	/**
	* AP product id to request
	* @var string
	*/
	protected $productid = "";

	/**
	* Number of headlines to request
	* @var integer
	*/
	protected $page_size = "";

	/**
	* Time to store headlines in trainsient to avoid remote requests in seconds
	* @var integer
	*/
	protected $expiration = 15*MINUTE_IN_SECONDS;

	/**
	* Creating the object
	* @param string $url
	* @param array  $array
	* @param string $method
	*/
	public function __construct( $plugin_name, $version, $productid, $api_key, $page_size = 5 ) {
		$this->productid = $productid;
		$this->api_key = $api_key;
		$this->page_size = $page_size;
		$this->url = $this->build_url(self::ENDPOINT, array('q' => 'productid:' . $productid, 'include' => 'headline', 'in_my_plan' => 'true', 'page_size' => $page_size));
		$this->arguments = $this->build_arguments();
		parent::__construct($plugin_name, $version, $this->url, $this->arguments, "get");
	}

	/**
	* Creating the url
	* @param string $endpoint
	* @param array  $query
	*/
	public function build_url( $endpoint, array $query = array() ) {
		$url = $endpoint;

		if (!empty($query)) {
			$url .= '?'.http_build_query($query);
		}

		return $url;
	}

	/**
	* Creating the arguments
	*
	*/
	public function build_arguments() {
		$arguments = [];
		$arguments['timeout'] = 30;
		$arguments['redirection'] = 10;
		$arguments['httpversion'] = '1.1';
		$arguments['headers'] = [
			'x-api-key' => $this->api_key,
			'Accept-Encoding' => 'gzip',
		];

		return $arguments;
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

	public function get_ap_headlines() {
		$this->run();
		$headlines = $this->parse_ap_headlines($this->body);
		return $headlines;
	}

	public function read_ap_headlines() {
		$transient_name = Rssnewsticker_Transients::get_transient_name($this->plugin_name, __FUNCTION__);
		$headlines = Rssnewsticker_Transients::set_transient($transient_name, array($this, 'get_ap_headlines'), $this->expiration);
		return $headlines;
	}

}
