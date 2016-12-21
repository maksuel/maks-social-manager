<?php
/*
Plugin Name: MAKS Social Manager
Plugin URI:
Description: Manage yours socials with professional plugin.
Version: 0.2.0
Author: MAKS Solutions
Author URI:
License:
License URI:
Text Domain:
Domain Path:
*/

namespace MAKS;

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

define( 'MAKS_SOCIAL_MANAGER_URI', plugins_url( '' , __FILE__ ) );
define( 'MAKS_SOCIAL_MANAGER_DIR', trailingslashit( dirname(__FILE__) ) );

class social_manager {

	private static $_this;

	private $database;
	private $instagram;

	function __construct() {

		// Don't allow more than one instance of the class
		if( isset( self::$_this ) ) {
			wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.', get_class( $this ) ) );
		}
		self::$_this = $this;

		require_once 'core/database.php';
		require_once 'core/instagram.php';
		$this->database  = new core\database();
		$this->instagram = new core\instagram();

		register_activation_hook( __FILE__ , [$this, 'register_activation'] );
		register_deactivation_hook( __FILE__ , [$this, 'register_deactivation'] );
		register_uninstall_hook( __FILE__ , [$this, 'register_uninstall'] );

		add_action( 'admin_menu' , [$this, 'admin_menu'] );

		add_shortcode( 'maks-instagram', [$this, 'shortcode_instagram'] );
	}

	private function check_requirements() {

		global $wpdb;

		$message = file_get_contents( __DIR__ . '/requirements.php' );

		version_compare( PHP_VERSION , '7.0.0' , '>=' ) &&
		isset($wpdb)
		or wp_die($message);
	}

	public function admin_menu() {

		add_menu_page(
			'Social Manager',       // $page_title
			'Social Manager',       // $menu_title
			4,                      // $capability
			'maks-social-manager',  // $menu_slug
			'',                     // $function
			'dashicons-smiley',     // $icon_url
			59                      // $position
		);
		add_submenu_page(
			'maks-social-manager',        // $parent_slug
			'Settings',                   // $page_title
			'Settings',                   // $menu_title
			4,                            // $capability
			'maks-social-manager',        // $menu_slug
			[$this, 'menu_settings'] // $function
		);

		add_submenu_page(
			'maks-social-manager',
			'Instagram',
			'Instagram',
			4,
			'maks-instagram',
			[$this, 'menu_instagram']
		);
	}

	public function shortcode_instagram() {

		$content = '';

		include 'shortcode/instagram.php';

		return $content;
	}

	public function menu_settings()  { require_once 'menu/settings.php';  }
	public function menu_instagram() { require_once 'menu/instagram.php'; }

	public function register_activation() {

		$this->check_requirements();

		$this->database->register_activation();
		$this->instagram->register_activation();
	}

	public function register_deactivation() {

		$this->database->register_deactivation();
		$this->instagram->register_deactivation();
	}

	public function register_uninstall() {

		$this->database->register_uninstall();
		$this->instagram->register_uninstall();
	}
}

new social_manager();

/**
 * TODO > internationalize plugin
 */