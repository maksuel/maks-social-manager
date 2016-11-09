<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 09/11/16
 * Time: 12:02
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

require_once 'services.php';

class maks_database extends maks_services  {

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
		if( $this->has_error() ) { echo $this->get_log_error(); return false; };

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
		if( $this->has_error() ) { echo $this->get_log_error(); return false; };
	}

	public function database_uninstall() {

		/** skip if has error and print log */
		if( $this->has_error() ) { echo $this->get_log_error(); return false; };

		global $wpdb;

		/** Remove database version control */
		delete_option($this->database_version_key);

		/** Drop plugin tables */
		$wpdb->query( "DROP TABLE IF EXISTS 
			{$this->get_database_name_options()},
			{$this->get_database_name_instagram()},
			{$this->get_database_name_youtube()}"
		);
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


	/**
	 * GETTERS of current time
	 */
	public function get_current_time_string() {

		$current_time_string = current_time('mysql');

		return $current_time_string;
	}

	public function get_current_unix_time() {

		$current_unix_time = strtotime(
			$this->get_current_time_string()
		);

		return $current_unix_time;
	}
}