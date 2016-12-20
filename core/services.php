<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 09/11/16
 * Time: 12:04
 */

namespace MAKS\core;


class services {

	protected function set_default_if_not_exist( array $options, array $defaults ): array {

		if ( ! $options ) {
			return $defaults;
		}

		foreach ( $defaults as $key => $value ) {

			$options[ $key ] = $options[ $key ] ?? $value;
		}

		return $options;
	}

	protected function get_current_time_string() {

		$current_time_string = current_time( 'mysql' );

		return $current_time_string;
	}

	protected function get_current_unix_time() {

		$current_unix_time = strtotime(
			$this->get_current_time_string()
		);

		return $current_unix_time;
	}

	protected function require_wp_header() {

		$wp_header_file    = 'wp-blog-header.php'; // Wordpress file to search
		$current_directory = dirname( __FILE__ ); // Get the current directory

		$current_directory_array = explode( 'wp-content/plugins/', $current_directory, 2 );
		$wordpress_root          = $current_directory_array[0];
		$wp_header_uri           = $wordpress_root . $wp_header_file;

		/** Verify if searched file exists or die */
		file_exists( $wp_header_uri ) or die( 'Unable to require \'' . $wp_header_file . '\' =(' );

		require_once( $wp_header_uri ); // Require file
	}

	protected function remote_get( string $url, array $args = [], bool $json_decode = true ) {

		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		$response = wp_remote_get( $url );

		if ( wp_remote_retrieve_response_code( $response ) != 200 ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( $json_decode ) {
			$body = $this->decode( $body );
		}

		return $body;
	}

	protected function decode( string $string ): array  { return json_decode( $string, true ); }
	protected function encode( $mixed )        : string { return json_encode( $mixed ); }
}