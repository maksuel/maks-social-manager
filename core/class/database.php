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
	private $maks_prefix = 'maks_';

	private $table_name_options   = 'options';
	private $table_name_instagram = 'instagram';
	private $table_name_facebook  = 'facebook';
	private $table_name_youtube   = 'youtube';

	private $column_name_time  = 'timestamp';
	private $column_name_key   = 'data_key';
	private $column_name_value = 'data_value';

	private $tables_structure = [
		'options' => [
			'key' => [
				'type' => 'VARCHAR(50)'
			],
			'value' => [
				'type' => 'VARCHAR(255)'
			]
		],
		'instagram' => [
			'time' => [
				'type'    => 'DATETIME',
				'default' => '0000-00-00 00:00:00',
			],
			'key' => [
				'type' => 'VARCHAR(50)'
			],
			'value' => [
				'type' => 'TEXT'
			]
		]
	];

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
		if( !isset($wpdb) ) $this->error('Cannot found $wpdb');

		$wp_prefix   = $wpdb->prefix;
		$maks_prefix = $this->maks_prefix;
		$full_prefix = $wp_prefix . $maks_prefix;

		$tables_structure = [];

		foreach($this->tables_structure as $table => $structure) {

			$tables_structure[$table] = $structure;

			$table_name = $full_prefix . $table;
			$tables_structure[$table]['table_name'] = $table_name;
		}

		$this->tables_structure = $tables_structure;

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

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		foreach($this->tables_structure as $structure) {

			$query = "CREATE TABLE {$structure['table_name']} (id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,";

			foreach($structure as $key => $value) {

				$continue = false;

				if($key == 'time') {
					$query .= $this->get_column_name_time();
					$continue = true;
				}
				if($key == 'key') {
					$query .= $this->get_column_name_key();
					$continue = true;
				}
				if($key == 'value') {
					$query .= $this->get_column_name_value();
					$continue = true;
				}

				if($continue) {

					$query .= " {$value['type']} ";

					if( isset($value['default']) ) {
						$query .= "DEFAULT '{$value['default']}' ";
					}

					$query .= 'NOT NULL,';
				}
			}

			$query .= "PRIMARY KEY  (id)) {$charset_collate};";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($query);
		}

		add_option( $this->version_key , $this->version );
	}

	public function database_deactivation() {

		$this->database_uninstall(); // TEMPORARY
	}

	public function database_uninstall() {

		global $wpdb;

		/** Remove database version control */
		delete_option($this->version_key);

		$tables = '';

		foreach($this->tables_structure as $structure) {

			if( !empty($tables) ) $tables .= ',';

			$tables .= $structure['table_name'];
		}

		/** Drop plugin tables */
		$wpdb->query("DROP TABLE IF EXISTS {$tables}");
	}

	public function create_options( $key_value ) {

		global $wpdb;
		$table_name        = $this->get_table_name('options');
		$column_name_key   = $this->get_column_name_key();
		$column_name_value = $this->get_column_name_value();

		if( gettype($key_value) == 'array' ) {

			foreach( $key_value as $key => $value ) {

				$wpdb->insert(
					$table_name,
					array(
						$column_name_key   => $key,
						$column_name_value => $value
					)
				);
			}

		} else {

			$key   = key($key_value);
			$value = $key_value[$key];

			$wpdb->insert(
				$table_name,
				array(
					$column_name_key   => $key,
					$column_name_value => $value
				)
			);
		}
	}

	public function get_options( $keys ) {

		global $wpdb;
		$table_name      = $this->get_table_name('options');
		$column_name_key = $this->get_column_name_key();

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
		$table_name       = $this->get_table_name('instagram');
		$column_name_key  = $this->get_column_name_key();
		$column_name_time = $this->get_column_name_time();

		$query = '';

		if( gettype($keys) == 'array' ) {

			foreach( $keys as $key ) {
				if( $query != '' ) $query .= ",";
				$query .= '\'' . $key . '\'';
			}

		} else {

			$query .= '\'' . $keys . '\'';
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
		$table_name        = $this->get_table_name('instagram');
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
		$table_name        = $this->get_table_name('options');
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
		$table_name        = $this->get_table_name('instagram');
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

	/**
	 * GETTERS: tables names / column names.
	 *
	 * @return string
	 */
	public function get_column_name_key()   { return $this->column_name_key;   }
	public function get_column_name_value() { return $this->column_name_value; }
	public function get_column_name_time()  { return $this->column_name_time;  }

	public function get_table_name( $table ) {

		return $this->tables_structure[$table]['table_name'];
	}
}