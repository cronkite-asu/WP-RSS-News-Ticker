<?php
namespace Rssnewsticker;

class RemoteJSON extends Remote {

	/**
	* Prepare the headers for JSON requests and then run the main method run()
	**/
	public function run() {
		$this->arguments['headers']['Content-type'] = 'application/json';
		parent::run();
	}

}
