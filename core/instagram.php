<?php

/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 28/10/16
 * Time: 11:24
 */

namespace MAKS\core;

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

require_once 'services.php';

class instagram extends services {

	private $option_key = 'maks_social_manager_instagram';

	private $options = [
		'last_update' => 0,    // int
		'rate_limit'  => 60,   // int -> limit per hour

		'client_id'     => '',   // string
		'client_secret' => '',   // string
		'access_token'  => '',   // string

		'metric_header'  => true, // bool
		'display_header' => true, // bool

		'metric_media'             => true, // bool
		'display_media'            => true, // bool
		'display_number_media'     => 9,    // int
		'display_likes_comments'   => true, // bool
		'display_caption'          => true, // bool
		'display_load_more_button' => true, // bool

		'preserve_settings' => false // bool
	];

	private $users_self_url = 'https://api.instagram.com/v1/users/self/';
	private $users_self_response = '';

	private $media_recent_url = 'https://api.instagram.com/v1/users/self/media/recent/';
	private $media_recent_response = '';
	private $media_recent_next_max_id = '';

	private $key_metric = 'metrics_';

	private $database;

	public function __construct( string $type = 'new' ) {

		if ( $type == 'update' ) {

			$option_key = $this->get_option_key();
			$options    = get_option( $option_key );
			if ( ! $options ) {
				wp_die( 'unset option -> ' . $option_key );
			}

			$this->options = $options;
		}
	}

	public function register_activation() {

		$options         = get_option( $this->get_option_key() );
		$checked_options = $options ?
			$this->set_default_if_not_exist( $options, $this->options ) :
			$this->options;

		update_option( $this->get_option_key(), $checked_options );
	}

	public function register_deactivation() {

		$this->register_uninstall(); // TODO REMOVE TEMPORARY
	}

	public function register_uninstall() {

		$options = get_option( $this->get_option_key() );

		if ( ! $options['preserve_settings'] ) {
			delete_option( $this->get_option_key() );
		}
	}

	public function update() {

		$this->get_current_data();
		$this->update_database();
	}

	private function get_current_data() {

		if ( $this->is_validate() ) {

			$has_response = false;

			if ( $this->options['display_header'] ||
			     $this->options['metric_header']
			) {

				$args                      = [
					'access_token' => $this->options['access_token']
				];
				$this->users_self_response = $this->remote_get( $this->users_self_url, $args );
				$has_response              = true;
			}

			if ( $this->options['display_media'] ||
			     $this->options['metric_media']
			) {

				$args                        = [
					'access_token' => $this->options['access_token'],
					'count'        => $this->options['display_number_media']
				];
				$this->media_recent_response = $this->remote_get( $this->media_recent_url, $args );
				$has_response                = true;
			}

			if ( $has_response ) {

				$this->options['last_update'] = $this->get_current_unix_time();
				update_option( $this->get_option_key(), $this->options );
			}
		}
	}

	private function is_validate(): bool {

		if ( empty( $this->options['access_token'] ) ) {
			wp_die( 'undefined instagram access token' );
		}

		$last_update  = $this->options['last_update'];
		$current_time = $this->get_current_unix_time();

		if ( ! empty( $last_update ) &&
		     $current_time - $last_update < ceil( 3600 / $this->options['rate_limit'] )
		) {
			wp_die( 'rate limit exceeded, try later' );
		}

		return true;
	}

	private function update_database() {

		$metric_header  = $this->options['metric_header'];
		$display_header = $this->options['display_header'];
		$metric_media   = $this->options['metric_media'];
		$display_media  = $this->options['display_media'];

		$users_self_response   = $this->users_self_response;
		$media_recent_response = $this->media_recent_response;

		$check_header = ! empty( $users_self_response ) && ( $metric_header || $display_header );
		$check_media  = ! empty( $media_recent_response ) && ( $metric_media || $display_media );

		if ( $check_header || $check_media ) {

			$this->database = new database();

		} else {

			return;
		}

		if ( $check_header ) {

			$users_self_data = $users_self_response['data'];
			$key_id          = $users_self_data['id'];

			unset( $users_self_data['id'] ); // Remove id from data

			if ( $display_header ) {

				$current_data     = $this->encode( $users_self_data );
				$last_data_return = $this->database->get_results( 'instagram', $key_id, 1 );

				if ( empty( $last_data_return ) ) {

					$this->database->insert( 'instagram', $key_id, $current_data );

				} else {

					$column_name_value = $this->database->get_column_name( 'value' );

					$last_data_id = $last_data_return[0]->id;
					$last_data    = $last_data_return[0]->$column_name_value;

					$last_data_array = $this->decode( $last_data );
					$is_not_equals   = $users_self_data != $last_data_array;

					if ( $is_not_equals ) {
						$this->database->update( 'instagram', $last_data_id, $current_data );
					}
				}
			}

			if ( $metric_header ) {

				$key_metric = $this->key_metric . $key_id;

				$media       = $users_self_data['counts']['media'];
				$followed_by = $users_self_data['counts']['followed_by'];
				$follows     = $users_self_data['counts']['follows'];

				$last_data_return = $this->database->get_results( 'instagram', $key_metric, 1 );

				$current_time = $this->get_current_unix_time();

				$metric_counts_array = [ $media, $followed_by, $follows ];
				$metrics_array       = [ $current_time, $metric_counts_array ];
				$new_metric_values   = [
					'metrics'     => [ $metrics_array ],
					'last_counts' => $metric_counts_array
				];

				if ( empty( $last_data_return ) ) {

					$metric_data = $this->encode( $new_metric_values );
					$this->database->insert( 'instagram', $key_metric, $metric_data );

				} else {

					$column_name_time = $this->database->get_column_name( 'time' );

					$last_update          = $last_data_return[0]->$column_name_time;
					$last_update_datetime = new \DateTime( $last_update );
					$last_update_datetime = $last_update_datetime->format( 'Y-m-d' );

					$current_time_string   = $this->get_current_time_string();
					$current_time_datetime = new \DateTime( $current_time_string );
					$current_time_datetime = $current_time_datetime->format( 'Y-m-d' );

					if ( $last_update_datetime != $current_time_datetime ) {

						$metric_data = $this->encode( $new_metric_values );
						$this->database->insert( 'instagram', $key_metric, $metric_data );

					} else {

						$column_name_value = $this->database->get_column_name( 'value' );

						$last_update_value = $last_data_return[0]->$column_name_value;
						$last_update_array = $this->decode( $last_update_value );
						$last_update       = $last_update_array['last_counts'];

						$last_update_media       = $last_update[0];
						$last_update_followed_by = $last_update[1];
						$last_update_follows     = $last_update[2];

						if ( $media == $last_update_media             ) { $media = 0; }
						if ( $followed_by == $last_update_followed_by ) { $followed_by = 0; }
						if ( $follows == $last_update_follows         ) { $follows = 0; }

						if ( $media || $followed_by || $follows ) {

							$new_metric_counts_array = [ $media, $followed_by, $follows ];
							$new_metrics_array       = [ $current_time, $new_metric_counts_array ];

							$last_data_id = $last_data_return[0]->id;

							$last_metrics = $last_update_array['metrics'];
							array_push( $last_metrics, $new_metrics_array );

							$last_update_array['metrics']     = $last_metrics;
							$last_update_array['last_counts'] = $metric_counts_array;

							$metric_data = $this->encode( $last_update_array );

							$this->database->update( 'instagram', $last_data_id, $metric_data );
						}
					}
				}
			}
		}

		if ( $check_media ) {

			$this->media_recent_next_max_id = $media_recent_response['pagination']['next_max_id'];
			$media_recent_data              = $media_recent_response['data'];

			foreach ( $media_recent_data as $media_recent ) {

				if ( $display_media ) {

					$key_id             = $media_recent['id'];
					$value_created_time = $media_recent['created_time'];
					$created_time       = date( 'Y-m-d H:i:s', $value_created_time );

					unset( $media_recent['id'] ); // Remove id from data
					unset( $media_recent['attribution'] ); // REMOVE UNNECESSARY

					$current_data     = $this->encode( $media_recent );
					$last_data_return = $this->database->get_results( 'instagram', $key_id, 1 );

					if ( empty( $last_data_return ) ) {

						$this->database->insert( 'instagram', $key_id, $current_data, $created_time );

					} else {

						$column_name_value = $this->database->get_column_name('value');

						$last_data_id = $last_data_return[0]->id;
						$last_data    = $last_data_return[0]->$column_name_value;

						$last_data_array = $this->decode( $last_data );
						$is_not_equals   = $media_recent != $last_data_array;

						if ( $is_not_equals ) {
							$this->database->update( 'instagram', $last_data_id, $current_data, $created_time );
						}
					}
				}

				if ( $metric_media ) {

					// TODO
				}
			}
		}

		$print_array = [
			'display_header' => $display_header ? $users_self_response['data']   : '',
			'display_media'  => $display_media  ? $media_recent_response['data'] : ''
		];

		wp_send_json( $print_array );
	}

	public function get_option_key(): string { return $this->option_key; }
}