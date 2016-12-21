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

class database extends services {

	private $version_key = 'maks_database_version';
	private $version = 0.2;
	private $maks_prefix = 'maks_';

	private $tables = [
		'instagram' => [
			'structure' => [
				'time'  => [
					'type'    => 'DATETIME',
					'default' => '0000-00-00 00:00:00',
				],
				'key'   => [
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

		foreach ( $this->tables as $table => $config ) {

			$table_name                     = $full_prefix . $table;
			$this->tables[ $table ]['name'] = $table_name;
		}
	}

	public function register_activation() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		foreach ( $this->tables as $table ) {

			$query = "CREATE TABLE {$table['name']} (id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,";

			foreach ( $table['structure'] as $column => $structure ) {

				$query .= $this->get_column_name( $column );
				$query .= " {$structure['type']} ";

				if ( isset( $structure['default'] ) ) {
					$query .= "DEFAULT '{$structure['default']}' ";
				}

				$query .= 'NOT NULL,';
			}

			$query .= "PRIMARY KEY  (id)) {$charset_collate};";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $query );
		}

		add_option( $this->get_database_version_key(), $this->get_database_version() );
	}

	public function register_deactivation() {

		$this->register_uninstall(); // TODO REMOVE TEMPORARY
	}

	public function register_uninstall() {

		global $wpdb;

		/** Remove database version control */
		delete_option( $this->get_database_version_key() );

		/** Construct query */
		$query = '';

		foreach ( $this->tables as $table ) {

			if ( ! empty( $query ) ) {
				$query .= ',';
			}

			$query .= $table['name'];
		}

		/** Drop plugin tables */
		$wpdb->query( "DROP TABLE IF EXISTS {$query}" );
	}

	public function get_results( $table, $keys, $filter_limit = false, $where_column = 'key', $order_by_column = 'time' ) {

		global $wpdb;
		$table_name = $this->get_table_name( $table );
		$where      = $this->get_column_name( $where_column );
		$order_by   = $this->get_column_name( $order_by_column );

		$search = '';
		$query = "SELECT * " .
		         "FROM {$table_name} " .
		         "WHERE {$where} ";

		$array_keys = array_keys($keys);
		$keys = $keys[ $array_keys[0] ];

		if($array_keys[0] == 'IN') {

			if ( gettype( $keys ) == 'array' ) {

				foreach ( $keys as $key ) {

					if ( $search != '' ) {
						$search .= ",";
					}
					$search .= '\'' . $key . '\'';
				}

			} else {

				$search .= '\'' . $keys . '\'';
			}

			$query .= "IN ({$search}) ";

		} else if($array_keys[0] == 'LIKE') {

			$query .= "LIKE '{$keys}' ";
		}

		$query .= "ORDER BY {$order_by} DESC";

		if($filter_limit)
			$query .= ' LIMIT ' . (int) $filter_limit;

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, '' )
		);

		return $results;
	}

	public function insert( $table, $key, $value, $time_string = null ) {

		global $wpdb;
		$table_name = $this->get_table_name( $table );
		$time       = $time_string ?? $this->get_current_time_string();

		$response = $wpdb->insert(
			$table_name,
			array(
				$this->get_column_name( 'time' )  => $time,
				$this->get_column_name( 'key' )   => $key,
				$this->get_column_name( 'value' ) => $value
			)
		);

		return $response;
	}

	public function update( $table, $id, $value, $time_string = null ) {

		global $wpdb;
		$table_name = $this->get_table_name( $table );
		$time       = $time_string ?? $this->get_current_time_string();

		$response = $wpdb->update(
			$table_name,
			array(
				$this->get_column_name( 'time' )  => $time,
				$this->get_column_name( 'value' ) => $value
			),
			array(
				'id' => $id
			)
		);

		return $response;
	}

	/** GETTERS */
	public function get_table_name( string $table )  : string { return $this->tables[ $table ]['name']; }
	public function get_column_name( string $column ): string { return $this->columns[ $column ]; }
	public function get_database_version_key()       : string { return $this->version_key; }
	public function get_database_version()           : string { return $this->version; }
}