<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 09/11/16
 * Time: 12:04
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

class maks_services {

	private $has_error = false;
	private $log_error = '';


	/** ERROR BLOCK */
	protected function error($error_string) {

		$this->has_error = true;
		$this->log_error = $error_string;

		die($error_string);  // TEMPORARY
	}

	protected function has_error() {

		return $this->has_error;
	}

	protected function get_log_error() {

		return $this->log_error;
	}

	protected function print_error() {

		if($this->has_error) {

			echo $this->log_error;

		} else {

			return false;
		}
	}


	/** Method to make connection to get json data */
	protected function get_json($url) {

		/** skip if has error */
		if( $this->has_error() ) { return false; };

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$response = curl_exec($curl);
		curl_close($curl);

		if( $response == false ) { $this->error('Connection curl FAIL'); return false; };

		return json_decode($response, true);
	}
}