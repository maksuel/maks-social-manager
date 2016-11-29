<?php
/*
Plugin Name: MAKS Social Manager
Plugin URI:
Description: Manage yours socials with professional plugin.
Version: 0.1
Author: MAKS Solutions
Author URI: http://tripdomak.com.br
License: GPL
License URI: https://www.gnu.org/licenses/gpl.html
Text Domain:
Domain Path: /languages
*/

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

define( 'MAKS_SOCIAL_MANAGER_URI', plugins_url('', __FILE__) );
define( 'MAKS_SOCIAL_MANAGER_DIR', trailingslashit( dirname(__FILE__) ) );

class MAKS_Social_Manager {

	private static $_this;

	private $database_instance;
	private $instagram_instance;

	/**
	 * MAKS_Social_Manager constructor.
	 */
	function __construct() {

		// Don't allow more than one instance of the class
		if( isset( self::$_this ) ) {
			wp_die( sprintf( '%s is a singleton class and you cannot create a second instance.', get_class( $this ) ) );
		}
		self::$_this = $this;


		add_action( 'admin_menu', 'admin_menu' );


		require_once 'core/class/instagram.php';



		function maks_database_activation() {

			$database_instance  = new maks_database();
			$instagram_instance = new maks_instagram();

			$database_instance->database_activation();

			$instagram_instance->populate();
		}

		function maks_database_deactivation() {

			$database_instance = new maks_database();

			$database_instance->database_deactivation();
		}

		function maks_database_uninstall() {

			$database_instance = new maks_database();

			$database_instance->database_uninstall();
		}

//		function maks_database_update() {
//
//			global $maks_db_version;
//			$check = get_option( 'maks_db_version' );
//
//			if( $check < $maks_db_version ) {
//
//				maks_db_uninstall();
//				maks_db_activation();
//
//			} elseif( $check == false ) {
//
//				maks_db_activation();
//			}
//		}

		register_activation_hook(   __FILE__ , 'maks_database_activation'   );
		register_deactivation_hook( __FILE__ , 'maks_database_deactivation' );
		register_uninstall_hook(    __FILE__ , 'maks_database_uninstall'    );


		/**
		 * Function to register shortcodes
		 *
		 * Example: [shortcode_name id="0"]text[/shortcode_name]
		 * $atts['id'] = 0
		 * $content = text
		 */
		function maks_shortcodes() {

			require_once 'includes/instagram-print.php';

			function instagram_shortcode( $atts = [] , $content = null , $tag = '' ) {

				// normalize attribute keys, lowercase
				$atts = array_change_key_case( (array)$atts , CASE_LOWER );

				// default attributes
				$default = array(
					'header' => true,
					'main' => true,
					'footer' => true,
					'col' => 4,
					'display' => 12
				);

				// override default attributes with user attributes
				$atts = shortcode_atts( $default , $atts , $tag );

				maks_instagram_print( $atts , $content );
			}

			add_shortcode( 'maks-instagram', 'instagram_shortcode' );
			//add_shortcode( 'maks-youtube', 'youtube_shortcode' );
		}

		/**
		 * Function to remove shortcodes
		 *
		 * Before check if exists
		 */
		function maks_remove_shortcode( $shortcode_name ) {

			if( shortcode_exists( $shortcode_name ) ) {

				remove_shortcode( $shortcode_name );
			}
		}

		/**
		 * Calling function to registering shortcodes
		 *
		 * Wordpress recommend the 'init' action hook.
		 * Reference: https://developer.wordpress.org/plugins/shortcodes/basic-shortcodes/
		 */
		add_action('init', 'maks_shortcodes');



		function admin_menu() {

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
				'maks-social-manager',  // $parent_slug
				'Settings',             // $page_title
				'Settings',             // $menu_title
				4,                      // $capability
				'maks-social-manager',  // $menu_slug
				'settings_menu'         // $function
			);

			add_submenu_page('maks-social-manager', 'Instagram', 'Instagram', 4, 'maks-instagram', 'instagram_menu');
			//add_submenu_page('maks-sm', 'YouTube', 'YouTube', 4, 'maks-sm-yt', 'youtube_menu');
		}

		function settings_menu() {

			require_once 'admin-menu/settings.php';
		}

		function instagram_menu() {

			require_once 'admin-menu/instagram.php';
		}
	}
}

new MAKS_Social_Manager();