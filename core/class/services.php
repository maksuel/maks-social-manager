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

	private $has_error      = false;
	private $error_messages = [];

	/**
	 * die() showing error or push errors into private variable.
	 *
	 * @param $error_message
	 * @param bool $die
	 */
	protected function error( $error_message , $die = true ) {

		if($die) die($error_message);

		$this->has_error = true;

		array_push(
			$this->error_messages,
			$error_message
		);
	}

	/**
	 * @return bool
	 */
	protected function has_error() {

		return $this->has_error;
	}

	/**
	 * @return array
	 */
	protected function get_error_messages() {

		return $this->error_messages;
	}

	/**
	 * Print errors or
	 * @return bool
	 */
	public function print_errors() {

		if($this->has_error) {

			foreach($this->error_messages as $error_message) {

				print_r( $error_message . PHP_EOL );
			}

		} else {

			return false;
		}
	}

	/**
	 * Maker of requests to APIs
	 *
	 * @param $url
	 * @param bool $decode
	 *
	 * @return array|bool|mixed
	 */
	protected function make_request( $url , $decode = true ) {

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_HEADER, false);
		$response = curl_exec($curl);
		curl_close($curl);

		if( $response === false ) { $this->error( 'Connection url FAIL => ' . $url , false ); return false; };

		if($decode) { $response = $this->decode($response); }

		return $response;
	}

	/**
	 * @param $string
	 *
	 * @return array
	 */
	protected function decode( $string ) {

		return json_decode( $string , true );
	}

	/**
	 * @param $mixed
	 *
	 * @return string
	 */
	protected function encode( $mixed ) {

		return json_encode( $mixed );
	}

	/**
	 * @return string
	 */
	protected function get_current_time_string() {

		$current_time_string = current_time('mysql');

		return $current_time_string;
	}

	/**
	 * @return false|int
	 */
	protected function get_current_unix_time() {

		$current_unix_time = strtotime(
			$this->get_current_time_string()
		);

		return $current_unix_time;
	}
}