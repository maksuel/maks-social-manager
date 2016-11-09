<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 28/10/16
 * Time: 15:40
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

class maks_database {

	private $has_error = false;
	private $log_error = '';

	private $database_version_key = 'maks_database_version';
	private $database_version     = 0.1;

	private $database_maks_prefix    = 'maks_';
	private $database_name_options   = 'options';
	private $database_name_instagram = 'instagram';
	private $database_name_youtube   = 'youtube';

	private $column_name_key   = 'data_key';
	private $column_name_value = 'data_value';
	private $column_name_time  = 'timestamp';


	public function __construct() {

		global $wpdb;

		/** IMPORTANT */
		if( !isset($wpdb) ) { $this->error('Cannot found $wpdb'); return false; };

		$wp_prefix   = $wpdb->prefix;
		$maks_prefix = $this->database_maks_prefix;
		$full_prefix = $wp_prefix . $maks_prefix;

		$database_name_options   = $full_prefix . $this->database_name_options;
		$database_name_instagram = $full_prefix . $this->database_name_instagram;
		$database_name_youtube   = $full_prefix . $this->database_name_youtube;

		$this->database_name_options   = $database_name_options;
		$this->database_name_instagram = $database_name_instagram;
		$this->database_name_youtube   = $database_name_youtube;
	}


	public function database_activation() {

		/** skip if has error and print log */
		if( $this->has_error() ) { echo $this->log_error; return false; };

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_options = "CREATE TABLE {$this->get_database_name_options()} (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			{$this->get_column_name_key()} VARCHAR(50) NOT NULL,
			{$this->get_column_name_value()} VARCHAR(255) NOT NULL,			
			PRIMARY KEY  (id)
		) {$charset_collate};";

		$table_instagram = "CREATE TABLE {$this->get_database_name_instagram()} (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			{$this->get_column_name_time()} datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			{$this->get_column_name_key()} VARCHAR(50) NOT NULL,
			{$this->get_column_name_value()} TEXT NOT NULL,				
			PRIMARY KEY  (id)
		) {$charset_collate};";

		$table_youtube = "CREATE TABLE {$this->get_database_name_youtube()} (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			{$this->get_column_name_time()} datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			{$this->get_column_name_key()} VARCHAR(50) NOT NULL,
			{$this->get_column_name_value()} TEXT NOT NULL,				
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($table_options);
		dbDelta($table_instagram);
		dbDelta($table_youtube);

		add_option( $this->database_version_key , $this->database_version );
	}

	public function database_deactivation() {

		/** skip if has error and print log */
		if( $this->has_error() ) { echo $this->log_error; return false; };

	}

	public function database_uninstall() {

		/** skip if has error and print log */
		if( $this->has_error() ) { echo $this->log_error; return false; };

		global $wpdb;

		/** Remove database version control */
		delete_option($this->database_version_key);

		/** Drop plugin tables */
		$wpdb->query( "DROP TABLE IF EXISTS " . MAKS_DB_OPTIONS . "," . MAKS_DB_INSTAGRAM . "," . MAKS_DB_YOUTUBE );
	}


	protected function multiple_insert_database($query) {

		global $wpdb;

		foreach($query as $data_insert) {
			$database = $data_insert[0];
			for($i = 1; $i < sizeof($data_insert); $i++) {
				$wpdb->insert(
					$database,
					$data_insert[$i]
				);
			}
		}
	}

	/** ERROR BLOCK */
	protected function error($error_string) {

		$this->has_error = true;
		$this->log_error = $error_string;
	}

	public function has_error() {

		return $this->has_error;
	}

	public function get_error() {

		if($this->has_error) {

			echo $this->log_error;

		} else {

			return false;
		}
	}

	/** GETTERS DATABASE */
	public function get_database_name_options() {

		return $this->database_name_options;
	}

	public function get_database_name_instagram() {

		return $this->database_name_instagram;
	}

	public function get_database_name_youtube() {

		return $this->database_name_youtube;
	}

	public function get_column_name_key() {

		return $this->column_name_key;
	}

	public function get_column_name_value() {

		return $this->column_name_value;
	}

	public function get_column_name_time() {

		return $this->column_name_time;
	}

	/** Method to get current time */
	public function get_current_time_string() {

		$current_time_string = current_time('mysql');

		return $current_time_string;
	}

	/** Method to get current time in unix format */
	public function get_current_unix_time() {

		$current_unix_time = strtotime(
			$this->get_current_time_string()
		);

		return $current_unix_time;
	}

	/** Method to make connection to get json data */
	protected function get_json($url) {

		/** skip if has error */
		if( $this->has_error() ) { return false; };

		echo 'set_vars()' . '<br />'; // TEMPORARY

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