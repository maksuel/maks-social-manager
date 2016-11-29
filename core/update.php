<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 23/10/16
 * Time: 21:58
 */

$maks_instagram_validate = [ 'next' ];
$maks_GET_instagram      = isset( $_GET['instagram'] ) ? strtolower( $_GET['instagram'] ) : false;
$maks_instagram          = in_array( $maks_GET_instagram , $maks_instagram_validate ) ||
                           filter_var( $maks_GET_instagram , FILTER_VALIDATE_BOOLEAN ) ? $maks_GET_instagram : false;

$maks_youtube_validate = [ 'next' ];
$maks_GET_youtube      = isset( $_GET['youtube'] ) ? strtolower( $_GET['youtube'] ) : false;
$maks_youtube          = in_array( $maks_GET_youtube , $maks_youtube_validate ) ||
                         filter_var( $maks_GET_youtube , FILTER_VALIDATE_BOOLEAN ) ? $maks_GET_youtube : false;

/** Only continue if user set specific parameters */
( $maks_instagram || $maks_youtube ) or die('Set the parameters.');

/**
 * Get URI of header file Wordpress
 * IF exists require_once
 */
function maks_require_wp_header() {

	/** Wordpress file to search */
	$wp_header_file = 'wp-blog-header.php';

	/** Get the current directory */
	$current_directory = dirname(__FILE__);

	/** Treating the URI */
	$current_directory_array = explode('wp-content/plugins/', $current_directory, 2);
	$wordpress_root = $current_directory_array[0];

	/** URI of the searched file */
	$wp_header_uri = $wordpress_root . $wp_header_file;

	/** Verify if searched file exists */
	file_exists($wp_header_uri) or die('Unable to require \'' . $wp_header_file . '\' =(');

	/** Require file */
	require_once($wp_header_uri);
}
maks_require_wp_header();

/**
 * INSTAGRAM SECTION
 */
if($maks_instagram) {

	require_once 'class/instagram.php';
	$maks_instagram_instance = new maks_instagram('update');

	$maks_instagram_instance->get_current_data();
	$maks_instagram_instance->update_database();

	if($maks_instagram == 'next') {

		// TODO
	}

	//$maks_instagram_instance->print_data();
	//$maks_instagram_instance->print_errors();
}

/** RETURN RESPONSE 200 */
http_response_code(200);
die();












function maks_instagram_last_update() {

	global $wpdb;
	global $maks_current_unix_time;

	$wpdb->update(
		MAKS_DB_OPTIONS,
		array(
			'data_value' => $maks_current_unix_time
		),
		array(
			'data_key' => 'instagram_last_update'
		)
	);

}

/**
 * Update metric counts
 *
 * @param $data
 */
function maks_instagram_metric_counts($decode_json) {

	global $wpdb;
	global $maks_current_time_string;
	$database = MAKS_DB_INSTAGRAM;

	function current_counts($current_counts_decode) {

		$media = $current_counts_decode['media'];
		$followed_by = $current_counts_decode['followed_by'];
		$follows = $current_counts_decode['follows'];

		$current_counts = array(
			(int)$media,
			(int)$followed_by,
			(int)$follows
		);

		return $current_counts;
	}

	function compare_counts($last_counts_insert, $current_counts) {

		for($i = 0; $i < sizeof($last_counts_insert); $i++) {
			if( $last_counts_insert[$i] != $current_counts[$i] ) {

				return true;
			}
		}

		return false;
	}

	function new_json($current_counts) {

		global $maks_current_unix_time;

		$new_json[] = array(
			$maks_current_unix_time,
			$current_counts
		);

		return json_encode($new_json);
	}

	function counts_append($data_value_decode, $current_counts) {

		global $maks_current_unix_time;

		$new_array[] = array(
			$maks_current_unix_time,
			$current_counts
		);

		$new_data_value = array_merge($data_value_decode, $new_array);

		return json_encode($new_data_value);
	}

	$current_counts_decode = $decode_json['counts'];
	$current_counts = current_counts($current_counts_decode);

	$current_date = new DateTime($maks_current_time_string);
	$current_date = $current_date->format('Y-m-d');

	$sql_results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$database} 
			WHERE data_key = 'metric_counts' ORDER BY date_time DESC LIMIT 1", '')
	);

	$id = $sql_results[0]->id;
	$date_time = $sql_results[0]->date_time;
	$data_value = $sql_results[0]->data_value;

	$data_value_decode = !empty($data_value) ? json_decode($data_value, true) : NULL;

	$last_counts_insert = !is_null($data_value_decode) ? $data_value_decode[sizeof($data_value_decode) - 1][1] : NULL;

	$last_date_insert = new DateTime($date_time);
	$last_date_insert = $last_date_insert->format('Y-m-d');

	$is_equals_date = ($last_date_insert == $current_date);
	$is_not_equals_counts = compare_counts($last_counts_insert, $current_counts);

	$check = 'PASS without do nothing';

	if( is_null($last_counts_insert) ) {

		$check = 'EMPTY condition';

		$wpdb->update(
			$database,
			array(
				'date_time'  => $maks_current_time_string,
				'data_value' => new_json($current_counts)
			),
			array(
				'id' => $id
			)
		);

	} elseif($is_equals_date) {

		if($is_not_equals_counts) {

			$check = 'CONCATENATE AND UPDATE condition';

			$wpdb->update(
				$database,
				array(
					'date_time'  => $maks_current_time_string,
					'data_value' => counts_append($data_value_decode, $current_counts)
				),
				array(
					'id' => $id
				)
			);
		}

	} else {

		$check = 'NEW INSERT condition';

		$wpdb->insert(
			$database,
			array(
				'date_time'  => $maks_current_time_string,
				'data_key'   => 'metric_counts',
				'data_value' => new_json($current_counts)
			)
		);
	}

	http_response_code(200);
	echo $check . '<br />';
	//maks_write_log($check);
	die();
}

/**
 * Save instagram data into database
 *
 * @param $data
 */
function maks_db_instagram($data) {

	global $wpdb;
	global $maks_current_time_string;
	$database = MAKS_DB_INSTAGRAM;

	$time = is_null( $data['created_time'] ) ? $maks_current_time_string : date( 'Y-m-d H:i:s', $data['created_time'] );
	$key = $data['id'];
	$data = json_encode($data);

	$result = $wpdb->get_results(
		$wpdb->prepare("SELECT * FROM {$database} WHERE data_key = '{$key}'", '')
	);

	if( is_null($result[0]) ) {

		$wpdb->insert(
			$database,
			array(
				'date_time'  => $time,
				'data_key'   => $key,
				'data_value' => $data
			)
		);
		maks_instagram_last_update();

	} else {

		similar_text( $data, $result[0]->data, $percent );

		if( $percent < 100 ) {

			$wpdb->update(
				$database,
				array(
					'data_value' => $data
				),
				array(
					'id' => $result[0]->id
				)
			);
			maks_instagram_last_update();
		}
	}
}

/**
 * Require essentials
 */

require_once 'class/instagram.php';

new maks_instagram();

echo 'PASS?';

die();



/**
 * Defining current time
 */
$maks_current_time_string = current_time('mysql');
$maks_current_unix_time = strtotime( $maks_current_time_string );








if($maks_instagram) {

	global $wpdb;
	global $maks_current_unix_time;
	$database = MAKS_DB_OPTIONS;

	$results = $wpdb->get_results(
		$wpdb->prepare("SELECT data_value FROM {$database}
		WHERE data_key IN ('instagram_last_update','instagram_access_token')", '')
	);

	$last_update = $results[0]->data_value;
	$access_token = $results[1]->data_value;
	$count = 9;

	if( empty($last_update) || ($maks_current_unix_time - $last_update) > 60 ) {

		$url = array(
			'users_self'   => 'https://api.instagram.com/v1/users/self/?access_token=' . $access_token,
			'media_recent' => 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $access_token . '&count=' . $count
		);

		$header = maks_getJson($url['users_self']);
		if( (int)$header['meta']['code'] == 200 ) :

			$data = $header['data'];

			maks_instagram_metric_counts($data);
			maks_db_instagram($data);

		endif;

		$main = maks_getJson($url['media_recent']);
		if( (int)$main['meta']['code'] == 200 ) :

			foreach($main['data'] as $data) {

				maks_db_instagram($data);
			}

		endif;

		$next = maks_getJson($url['media_recent'] . "&max_id=" . $main['pagination']['next_max_id']);
		if( (int)$next['meta']['code'] == 200 ) :

			foreach($next['data'] as $data) {

				maks_db_instagram($data);
			}

		endif;

		var_dump($next['data']);

	}
}