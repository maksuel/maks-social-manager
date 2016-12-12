<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 09/11/16
 * Time: 12:02
 */

namespace MAKS\core;

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

require_once 'services.php';

class database extends services  {

	private $version_key = 'maks_database_version';
	private $version     = 0.1;
	private $maks_prefix = 'maks_';

	private $tables = [
		'instagram' => [
			'structure' => [
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
		]
	];

	private $columns = [
		'time'  => 'timestamp',
		'key'   => 'data_key',
		'value' => 'data_value'
	];

	public function __construct() {

		global $wpdb;

		$wp_prefix   = $wpdb->prefix;
		$maks_prefix = $this->maks_prefix;
		$full_prefix = $wp_prefix . $maks_prefix;

		foreach($this->tables as $table => $config) {

			$table_name = $full_prefix . $table;
			$this->tables[$table]['name'] = $table_name;
		}
	}

	public function register_activation() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		foreach($this->tables as $table) {

			$query = "CREATE TABLE {$table['name']} (id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,";

			foreach($table['structure'] as $column => $structure) {

				$query .= $this->get_column_name($column);
				$query .= " {$structure['type']} ";

				if( isset($structure['default']) ) {
					$query .= "DEFAULT '{$structure['default']}' ";
				}

				$query .= 'NOT NULL,';
			}

			$query .= "PRIMARY KEY  (id)) {$charset_collate};";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta($query);
		}

		add_option( $this->get_database_version_key() , $this->get_database_version() );
	}

	public function register_deactivation() {

		$this->register_uninstall(); // TODO REMOVE TEMPORARY
	}

	public function register_uninstall() {

		global $wpdb;

		/** Remove database version control */
		delete_option($this->get_database_version_key() );

		/** Construct query */
		$query = '';

		foreach($this->tables as $table) {

			if( !empty($query) ) $query .= ',';

			$query .= $table['name'];
		}

		/** Drop plugin tables */
		$wpdb->query("DROP TABLE IF EXISTS {$query}");
	}

	/** GETTERS */
	public function get_table_name(string $table)   : string { return $this->tables[$table]['name']; }
	public function get_column_name(string $column) : string { return $this->columns[$column];       }
	public function get_database_version_key()      : string { return $this->version_key;            }
	public function get_database_version()          : string { return $this->version;                }










	/** TODO REFACTORING */
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
}