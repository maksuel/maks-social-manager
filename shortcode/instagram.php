<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 20/12/16
 * Time: 19:39
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

wp_enqueue_style( 'bootstrap', MAKS_SOCIAL_MANAGER_URI.'/css/bootstrap.min.css', array(), '3.3.7', 'all' );

wp_enqueue_style( 'shortcode-instagram', MAKS_SOCIAL_MANAGER_URI.'/css/shortcode-instagram.css' );
wp_enqueue_script( 'shortcode-instagram', MAKS_SOCIAL_MANAGER_URI.'/js/shortcode-instagram.js' );

$instagram = new \MAKS\core\instagram();
$database  = new \MAKS\core\database();

$column_name_value = $this->database->get_column_name( 'value' );
$options           = get_option( $instagram->get_option_key() );

$content = '
<article id="maks-instagram" class="container-fluid">';

if( $options['display_header'] ) {

	$header_results = $database->get_results( 'instagram', ['LIKE' => 'header_%%'], 1);
	$header_value   = $header_results[0]->$column_name_value;

	$content .= '
	<header maks-header class="row" style="display:none !important;">
		<div maks-json-header style="display:none;">'.$header_value.'</div>
		<div class="col-md-4">
			<img maks-profile-picture style="margin:auto;display:block;border-radius:50%;">
		</div>
		<div class="col-md-8">
			<div class="row">
				<h1 maks-username style="display: inline-block; vertical-align: middle;"></h1>
				<button style="display: inline-block; vertical-align: middle; margin-left: 20px;" type="button" class="btn btn-default">Follow</button>
			</div>
			<ul class="maks-row list-inline lead" style="padding-left: 0; margin-bottom: 0;">
				<li><span><strong maks-counts-media></strong> posts</span></li>
				<li><span><strong maks-counts-followed_by></strong> followers</span></li>
				<li><span><strong maks-counts-follows></strong> following</span></li>
			</ul>
			<div class="maks-row">
				<h2 maks-full_name class="small"></h2>
				<span maks-bio style="white-space: pre;"></span>
				<a maks-website style="display: block;"></a>
			</div>
		</div>
	</header>';
}

$rows = 4;

if($options['display_media']) {

	$media_results = $database->get_results( 'instagram', ['LIKE' => 'media_%%'], $options['display_number_media'] );

	$content .= '
	<main maks-media class="row" style="display:none;">
		<div maks-json-config style="display:none;">'.MAKS_SOCIAL_MANAGER_URI.'</div>';

	foreach ( $media_results as $object ) {

		$media_value = $object->$column_name_value;

		$content .= '
		<div maks-json-media style="display:none;">'.$media_value.'</div>';
	}

	$content .= '
		<section maks-media-template class="col-xs-12 col-md-4">
			<h1 maks-caption-text style="display:none;"></h1>
			<h2 maks-created-time style="display:none;"></h2>
			<div>
				<img images>
				<video videos preload="none" type="video/mp4" muted autoplay loop></video>
			</div>
			<ul style="display:none;">
				<li maks-likes-count></li>
				<li maks-comments-count></li>
			</ul>
		</section>';

	$content .= '
	</main>';

	if($options['display_load_more_button'])
		$content .= '
	<footer>
		<button type="button" class="btn btn-primary">Loading more...</button>
	</footer>';
}

$content .= '
</article>';