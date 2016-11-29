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