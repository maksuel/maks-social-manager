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

	private $options_call_key_value_casting = [
		'last_update'              => [ 'instagram_last_update'              => ''   , 'int'    ],
		'access_token'             => [ 'instagram_access_token'             => ''   , 'string' ],
		'next_max_id'              => [ 'instagram_next_max_id'              => ''   , 'string' ],
		'display_header'           => [ 'instagram_display_header'           => true , 'bool'   ],
		'metric_header'            => [ 'instagram_metric_header'            => true , 'bool'   ],
		'display_media'            => [ 'instagram_display_media'            => true , 'bool'   ],
		'number_media_display'     => [ 'instagram_number_media_display'     => 9    , 'int'    ],
		'metric_media'             => [ 'instagram_metric_media'             => true , 'bool'   ],
		'display_likes_comments'   => [ 'instagram_display_likes_comments'   => true , 'bool'   ],
		'display_caption'          => [ 'instagram_display_caption'          => true , 'bool'   ],
		'display_load_more_button' => [ 'instagram_display_load_more_button' => true , 'bool'   ]
	];

	private $instagram_call_key_value_casting = [
		'metric_counts' => [ 'metric_counts' => '' , 'string' ]
	];

	private $rate_limit = 288;

	/**
	 * DEPENDENCIES
	 */
	private $options_key      = [];
	private $options_key_call = [];

	private $instagram_key      = [];
	private $instagram_key_call = [];

	private $users_self_url  = '';
	private $users_self_response = '';

	private $media_recent_url  = '';
	private $media_recent_response = '';

	private $next_url = '';

	private $database_instance;

	/**
	 * CONSTRUCTOR based in type of instance
	 *
	 * @param $type = new || update
	 */
	public function __construct( $type ) {

		foreach( $this->options_call_key_value_casting as $call => $key_value_casting ) {

			$key = key($key_value_casting);

			array_push( $this->options_key , $key );
			$this->options_key_call[$key] = $call;
		}

		foreach( $this->instagram_call_key_value_casting as $call => $key_value_casting ) {

			$key = key($key_value_casting);

			array_push( $this->instagram_key , $key );
			$this->instagram_key_call[$key] = $call;
		}

		$this->database_instance = new maks_database();
//		$table_name_options      = $this->database_instance->get_table_name_options();
		$column_name_key         = $this->database_instance->get_column_name_key();
		$column_name_value       = $this->database_instance->get_column_name_value();


		if( $type == 'new' ) {

		}

		if( $type == 'update' ) {

			$options_results =
				$this->database_instance->get_options( $this->options_key );

			/** IMPORTANT */
			if( empty($options_results) ) { $this->error('Empty return $options_results'); return false; };

			foreach( $options_results as $result ) {

				$key   = $result->$column_name_key;
				$value = $result->$column_name_value;
				$call  = $this->options_key_call[$key];

				$this->options_call_key_value_casting[$call][$key] = $value;
			}

			$access_token         = $this->get_value_from_options_by_call('access_token');
			$number_media_display = $this->get_value_from_options_by_call('number_media_display');
			$next_max_id          = $this->get_value_from_options_by_call('next_max_id');

			$this->users_self_url   = 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token;
			$this->media_recent_url =
				'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $access_token .
				'&count=' . $number_media_display;

			if( !empty($next_max_id) ) {

				$this->next_url = $this->media_recent_url . '&max_id=' . $next_max_id;
			}
		}
	}

	private function get_value_from_options_by_call( $call ) {

		$key     = array_search( $call , $this->options_key_call );
		$value   = $this->options_call_key_value_casting[$call][$key];
		$casting = $this->options_call_key_value_casting[$call][0];

		if( settype($value, $casting) ) {

			return $value;

		} else {

			$this->error('Cannot set type of return of function: get_value_from_options_by_call()');

			return false;
		}
	}

	private function get_key_from_options_by_call( $call ) {

		$key = array_search( $call , $this->options_key_call );

		return $key;
	}

	private function get_key_from_instagram_by_call( $call ) {

		$key = array_search( $call , $this->instagram_key_call );

		return $key;
	}

	public function get_current_data() {

		$last_update  = $this->get_value_from_options_by_call('last_update');
		$rate_limit   = $this->rate_limit;
		$current_time = $this->get_current_unix_time();

		if( empty($last_update) || ($current_time - $last_update) > (86400 / $rate_limit) ) {

			$display_header = $this->get_value_from_options_by_call('display_header');
			$metric_header  = $this->get_value_from_options_by_call('metric_header');
			$display_media  = $this->get_value_from_options_by_call('display_media');
			$metric_media   = $this->get_value_from_options_by_call('metric_media');

			$get_json = false;

			if( $display_header || $metric_header ) {

				$this->users_self_response = $this->get_json($this->users_self_url);
				$get_json = true;
			}

			if( $display_media || $metric_media ) {

				$media_recent_data = $this->get_json($this->media_recent_url);

				$last_next_max_id    = $this->get_value_from_options_by_call('next_max_id');
				$current_next_max_id = $media_recent_data['pagination']['next_max_id'];

				if( $last_next_max_id != $current_next_max_id ) {

					$next_max_id_key = $this->get_key_from_options_by_call('next_max_id');
					$this->database_instance->update_options( $next_max_id_key, $current_next_max_id );
				}

				$this->media_recent_response= $media_recent_data;

				$get_json = true;
			}

			if($get_json) {

				$last_update_key = $this->get_key_from_options_by_call('last_update');
				$current_time    = $this->get_current_unix_time();

				$this->database_instance->update_options( $last_update_key, $current_time );
			}
		}
	}

	public function update_database() {

		$display_header = $this->get_value_from_options_by_call('display_header');
		$metric_header  = $this->get_value_from_options_by_call('metric_header');
		$display_media  = $this->get_value_from_options_by_call('display_media');
		$metric_media   = $this->get_value_from_options_by_call('metric_media');

		$users_self_response   = $this->users_self_response;
		$media_recent_response = $this->media_recent_response;

		if( !empty($users_self_response) && ( $display_header || $metric_header ) ) {

			$users_self_data = $users_self_response['data'];

			if($display_header) {

			}

			if($metric_header) {

				$key[]  = $this->get_key_from_instagram_by_call('metric_counts');
				$return = $this->database_instance->get_instagram( $key , 1);

			}
		}

		if( !empty($media_recent_response) && ( $display_media || $metric_media ) ) {

			$media_recent_data = $media_recent_response['data'];

			if($display_media) {

			}

			if($metric_media) {

			}
		}

		//if display true
		// json encode header

		//if metric true



		var_dump();
		echo '<br /><br />';
		print_r($this->users_self_response);
		echo '<br /><br />';
		print_r( $this->media_recent_response);
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
}