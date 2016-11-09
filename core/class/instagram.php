<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 28/10/16
 * Time: 11:24
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

require_once 'database.php';

class maks_instagram extends maks_services {

	private $options_call_key_value = [
		'last_update'              => [ 'instagram_last_update'              => ''   ],
		'access_token'             => [ 'instagram_access_token'             => ''   ],
		'next_url'                 => [ 'instagram_next_url'                 => ''   ],
		'display_header'           => [ 'instagram_display_header'           => true ],
		'metric_header'            => [ 'instagram_metric_header'            => true ],
		'display_media'            => [ 'instagram_display_media'            => true ],
		'number_media_display'     => [ 'instagram_number_media_display'     => 9    ],
		'metric_media'             => [ 'instagram_metric_media'             => true ],
		'display_likes_comments'   => [ 'instagram_display_likes_comments'   => true ],
		'display_caption'          => [ 'instagram_display_caption'          => true ],
		'display_load_more_button' => [ 'instagram_display_load_more_button' => true ]
	];
	private $options_key      = [];
	private $options_key_call = [];

	private $instagram_call_key_value = [
		'metric_counts' => [ 'metric_counts' => '' ]
	];
	private $instagram_key      = [];
	private $instagram_key_call = [];

	private $database_instance;

	private $rate_limit = 288;
	private $users_self_url;
	private $media_recent_url;

	private function get_value_from_options_by_call($call) {

		$key   = array_search( $call , $this->options_key_call );
		$value = $this->options_call_key_value[$call][$key];

		return $value;
	}

	public function __construct( $type ) {

		foreach( $this->options_call_key_value as $call => $key_value ) {

			$key = key($key_value);

			array_push( $this->options_key , $key );
			$this->options_key_call[$key] = $call;
		}

		foreach( $this->instagram_call_key_value as $call => $key_value ) {

			$key = key($key_value);

			array_push( $this->instagram_key , $key );
			$this->instagram_key_call[$key] = $call;
		}

		$this->database_instance = new maks_database();
		$table_name_options      = $this->database_instance->get_table_name_options();
		$column_name_key         = $this->database_instance->get_column_name_key();
		$column_name_value       = $this->database_instance->get_column_name_value();


		if( $type == 'new' ) {

		}

		if( $type == 'update' ) {

			$options_results =
				$this->database_instance->get_options_in_database( $table_name_options , $this->options_key );

			/** IMPORTANT */
			if( empty($options_results) ) { $this->error('Empty return $options_results'); return false; };

			foreach( $options_results as $result ) {

				$key   = $result->$column_name_key;
				$value = $result->$column_name_value;
				$call  = $this->options_key_call[$key];

				$this->options_call_key_value[$call][$key] = $value;
			}

			$access_token         = $this->get_value_from_options_by_call('access_token');
			$number_media_display = $this->get_value_from_options_by_call('number_media_display');

			$this->users_self_url   = 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token;
			$this->media_recent_url =
				'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $access_token .
				'&count=' . $number_media_display;
		}

		echo 'PASS';
	}




	public function get_current_data() {

	}


	public function update() {

		$this->set_vars();

		$this->get_header_counts();

		//$this->compare_values();
		//$this->save_header_counts();

		$this->get_error();
	}

	public function construct_database() {

		/** TODO simplify syntax */

		$query = array(
			array(
				MAKS_DB_OPTIONS,
				array(
					$this->get_column_name_key()   => $this->last_update_key,
					$this->get_column_name_value() => $this->last_update
				),
				array(
					$this->get_column_name_key()   => $this->access_token_key,
					$this->get_column_name_value() => $this->access_token
				),
				array(
					$this->get_column_name_key()   => $this->display_header_key,
					$this->get_column_name_value() => $this->display_header
				),
				array(
					$this->get_column_name_key()   => $this->metric_header_key,
					$this->get_column_name_value() => $this->metric_header
				),
				array(
					$this->get_column_name_key()   => $this->display_media_key,
					$this->get_column_name_value() => $this->display_media
				),
				array(
					$this->get_column_name_key()   => $this->number_media_display_key,
					$this->get_column_name_value() => $this->number_media_display
				),
				array(
					$this->get_column_name_key()   => $this->metric_media_key,
					$this->get_column_name_value() => $this->metric_media
				),
				array(
					$this->get_column_name_key()   => $this->display_likes_comments_key,
					$this->get_column_name_value() => $this->display_likes_comments
				),
				array(
					$this->get_column_name_key()   => $this->display_caption_key,
					$this->get_column_name_value() => $this->display_caption
				),
				array(
					$this->get_column_name_key()   => $this->display_load_more_button_key,
					$this->get_column_name_value() => $this->display_load_more_button
				)
			),
			array(
				MAKS_DB_INSTAGRAM,
				array(
					$this->get_column_name_time()  => $this->get_current_time_string(),
					$this->get_column_name_key()   => $this->metric_counts_key,
					$this->get_column_name_value() => $this->metric_counts
				)
			)
		);

		$this->multiple_insert_database($query);
	}

	private function set_vars() {

		/** skip if has error */
		if( $this->has_error() ) { return false; };

		echo 'set_vars()' . '<br />'; // TEMPORARY

		global $wpdb;

		$database       = MAKS_DB_OPTIONS;
		$database_key   = $this->get_column_name_key();
		$database_value = $this->get_column_name_value();

		$last_update_key          = $this->last_update_key;
		$access_token_key         = $this->access_token_key;
		$number_media_display_key = $this->number_media_display_key;

		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT {$database_key},{$database_value} FROM {$database} WHERE
				{$database_key} IN ('{$last_update_key}','{$access_token_key}','{$number_media_display_key}')", '')
		);

		/** IMPORTANT */
		if( empty($results) ) { $this->error('return SQL = EMPTY'); return false; };

		$data = [];

		foreach($results as $result) {
			$data[$result->$database_key] = $result->$database_value;
		}

		/** IMPORTANT */
		if(
			!isset($data[$last_update_key]) ||
			!isset($data[$access_token_key]) ||
			!isset($data[$number_media_display_key])
		) { $this->error('miss var in return'); return false; };

		$this->last_update          = $data[$last_update_key];
		$this->access_token         = $data[$access_token_key];
		$this->number_media_display = $data[$number_media_display_key];

		$last_update          = $this->last_update;
		$access_token         = $this->access_token;
		$number_media_display = $this->number_media_display;
		$rate_limit           = $this->rate_limit;
		$current_time         = $this->get_current_unix_time();

//		var_dump($last_update); echo '<br />';
//		var_dump($access_token); echo '<br />';
//		var_dump($number_media_display); echo '<br />';
//		var_dump($rate_limit); echo '<br />';
//		var_dump($current_time); echo '<br />';
//		die();


		$check_rate_limit   = empty($last_update) ? true : ( $current_time - $last_update ) > ( 1440 / $rate_limit );
		$check_access_token = empty($access_token) ? false : true;

		if( $check_rate_limit && $check_access_token ) {

			$this->access_token = !empty( $data[$this->access_token_key] ) ? $data[$this->access_token_key] : NULL;

			$this->users_self_url = 'https://api.instagram.com/v1/users/self/?access_token=' . $this->access_token;

		} else {

			die('Problems with: private function set_instagram_vars()');
		}
	}

	private function get_header_counts() {

		/** skip if has error */
		if( $this->has_error() ) { return false; };

		echo 'get_header_counts()' . '<br />'; // TEMPORARY

		die();

		global $wpdb;

		$essential = new maks_essential();
		$header = $essential->get_json($this->users_self_url);


	}
}