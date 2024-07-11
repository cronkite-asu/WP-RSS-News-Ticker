<?php

class Rssnewsticker_Remote {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * Request method
	 * @var string
	 */
	protected $method = "";

	/**
	 * URL where to send the request
	 * @var string
	 */
	protected $url = '';

	/**
	 * Arguments applied to the request
	 * @var array
	 */
	protected $arguments = array();

	/**
	 * Request response
	 * @var [type]
	 */
	protected $response;

	/**
	 * Body of the response
	 * @var [type]
	 */
	protected $body;

	/**
	 * Headers of the response
	 * @var [type]
	 */
	protected $headers;

	/**
	 * Response Code from the Request
	 * @var [type]
	 */
	protected $response_code;

	/**
	 * Response Message from the Request
	 * @var [type]
	 */
	protected $response_message;

	/**
	 * Creating the object
	 * @param string $url
	 * @param array  $array
	 * @param string $method
	 */
	public function __construct( $plugin_name, $version, $url, $array = array(), $method = "get" ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->method = strtoupper( $method );
		$this->url = $url;
		$this->arguments = $array;
		$this->arguments['method'] = $this->method;
	}

	/**
	* Running the request and setting all the attributes
	* @return void
	*/
	public function run() {
		$this->response = wp_remote_request( $this->url, $this->arguments );
		$this->body = wp_remote_retrieve_body( $this->response );
		$this->headers = wp_remote_retrieve_headers( $this->response );
		$this->response_code = wp_remote_retrieve_response_code( $this->response );
		$this->response_message = wp_remote_retrieve_response_message( $this->response );
	}

	/**
	 * Get the body
	 * @return mixed
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Get the headers
	 * @return mixed
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Response Code
	 * @return string
	 */
	public function get_response_code() {
		return $this->response_code;
	}

	/**
	 * Get the response message
	 * @return string
	 */
	public function get_response_message() {
		return $this->response_message;
	}

	/**
	 * Get the whole response
	 * @return mixed
	 */
	public function get_response() {
		return $this->response;
	}

	/**
	 * If the request was a success
	 * @return mixed
	 */
	public function is_success() {
		if( $this->response_code == '200' ) {
			return true;
		}
		return false;
	}

}
