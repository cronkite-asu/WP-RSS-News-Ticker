<?php
namespace Rssnewsticker;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class RemoteJSON extends Remote {

	/**
	* Prepare the headers for JSON requests and then run the main method run()
	**/
	public function run() {
		$this->arguments['headers']['Content-type'] = 'application/json';
		parent::run();
	}

}
