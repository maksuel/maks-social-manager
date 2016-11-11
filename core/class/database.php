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

	private $version_key = 'maks_database_version';
	private $version     = 0.1;

	private $maks_prefix          = 'maks_';
	private $table_name_options   = 'options';
	private $table_name_instagram = 'instagram';
	private $table_name_facebook  = 'facebook';
	private $table_name_youtube   = 'youtube';

	private $column_name_key   = 'data_key';
	private $column_name_value = 'data_value';
	private $column_name_time  = 'timestamp';

	/**
	 * CONSTRUCTOR based in Wordpress structure.
	 *
	 * First step: check IF EXISTS $wpdb object.
	 * This is necessary to work with Wordpress database.
	 *
	 * GET Wordpress tables prefix to normalized structure;
	 * GET MAKS Solutions prefix;
	 * Create full prefix to tables;
	 *
	 * GET , RENAME and SET tables names.
	 */
	public function __construct() {

		global $wpdb;

		/** IMPORTANT */
		if( !isset($wpdb) ) { $this->error('Cannot found $wpdb'); return false; };

		$wp_prefix   = $wpdb->prefix;
		$maks_prefix = $this->maks_prefix;
		$full_prefix = $wp_prefix . $maks_prefix;

		$table_name_options   = $full_prefix . $this->table_name_options;
		$table_name_instagram = $full_prefix . $this->table_name_instagram;
		$table_name_facebook  = $full_prefix . $this->table_name_facebook;
		$table_name_youtube   = $full_prefix . $this->table_name_youtube;

		$this->table_name_options   = $table_name_options;
		$this->table_name_instagram = $table_name_instagram;
		$this->table_name_facebook  = $table_name_facebook;
		$this->table_name_youtube   = $table_name_youtube;
	}

	public function database_activation() {

		/** skip if has error and print log */
		if( $this->has_error() ) { $this->print_errors(); return false; };

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_options = "CREATE TABLE {$this->get_table_name_options()} (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			{$this->get_column_name_key()} VARCHAR(50) NOT NULL,
			{$this->get_column_name_value()} VARCHAR(255) NOT NULL,			
			PRIMARY KEY  (id)
		) {$charset_collate};";

		$table_instagram = "CREATE TABLE {$this->get_table_name_instagram()} (
			id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			{$this->get_column_name_time()} datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			{$this->get_column_name_key()} VARCHAR(50) NOT NULL,
			{$this->get_column_name_value()} TEXT NOT NULL,				
			PRIMARY KEY  (id)
		) {$charset_collate};";

		$table_youtube = "CREATE TABLE {$this->get_table_name_youtube()} (
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

		add_option( $this->version_key , $this->version );
	}

	public function database_deactivation() {

		/** skip if has error and print log */
		if( $this->has_error() ) { $this->print_errors(); return false; };
	}

	public function database_uninstall() {

		/** skip if has error and print log */
		if( $this->has_error() ) { $this->print_errors(); return false; };

		global $wpdb;

		/** Remove database version control */
		delete_option($this->version_key);

		/** Drop plugin tables */
		$wpdb->query( "DROP TABLE IF EXISTS 
			{$this->get_table_name_options()},
			{$this->get_table_name_instagram()},
			{$this->get_table_name_youtube()}"
		);
	}

	public function get_options( $keys ) {

		global $wpdb;
		$table_name        = $this->get_table_name_options();
		$column_name_key   = $this->get_column_name_key();

		$query = '';

		foreach( $keys as $key ) {
			if( $query != '' ) $query .= ",";
			$query .= '\'' . $key . '\'';
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				 FROM {$table_name}
				 WHERE {$column_name_key}
				 IN ({$query})", '')
		);

		return $results;
	}

	public function get_instagram( $keys , $filter_limit ) {

		global $wpdb;
		$table_name        = $this->get_table_name_instagram();
		$column_name_key   = $this->get_column_name_key();
		$column_name_time  = $this->get_column_name_time();

		$query = '';

		foreach( $keys as $key ) {
			if( $query != '' ) $query .= ",";
			$query .= '\'' . $key . '\'';
		}

		$limit = '';

		if($filter_limit) {

			$limit = 'LIMIT ' . (int)$filter_limit;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
				 FROM {$table_name}
				 WHERE {$column_name_key}
				 IN ({$query})
				 ORDER BY {$column_name_time}
				 DESC {$limit}", '')
		);

		return $results;
	}

	public function insert_instagram( $key , $value , $time ) {

		global $wpdb;
		$table_name        = $this->get_table_name_instagram();
		$column_name_key   = $this->get_column_name_key();
		$column_name_value = $this->get_column_name_value();
		$column_name_time  = $this->get_column_name_time();

		if( empty($time) ) {

			$time = $this->get_current_time_string();
		}

		$response = $wpdb->insert(
			$table_name,
			array(
				$column_name_key   => $key,
				$column_name_value => $value,
				$column_name_time  => $time
			)
		);

		return $response;
	}

	public function update_options( $key , $value ) {

		global $wpdb;
		$table_name        = $this->get_table_name_options();
		$column_name_key   = $this->get_column_name_key();
		$column_name_value = $this->get_column_name_value();

		$response = $wpdb->update(
			$table_name,
			array(
				$column_name_value => $value
			),
			array(
				$column_name_key => $key
			)
		);

		return $response;
	}

	public function update_instagram( $id , $value , $time ) {

		global $wpdb;
		$table_name        = $this->get_table_name_instagram();
		$column_name_value = $this->get_column_name_value();
		$column_name_time  = $this->get_column_name_time();

		if( empty($time) ) {

			$time = $this->get_current_time_string();
		}

		$response = $wpdb->update(
			$table_name,
			array(
				$column_name_time  => $time,
				$column_name_value => $value
			),
			array(
				'id' => $id
			)
		);

		return $response;
	}



	public function multiple_insert_database($query) {

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


	/**
	 * GETTERS: tables names / column names.
	 *
	 * @return string
	 */
	public function get_table_name_options()   { return $this->table_name_options;   }
	public function get_table_name_instagram() { return $this->table_name_instagram; }
	public function get_table_name_youtube()   { return $this->table_name_youtube;   }
	public function get_column_name_key()      { return $this->column_name_key;      }
	public function get_column_name_value()    { return $this->column_name_value;    }
	public function get_column_name_time()     { return $this->column_name_time;     }
}