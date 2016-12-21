<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 20/12/16
 * Time: 19:39
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

wp_enqueue_style( 'bootstrap', MAKS_SOCIAL_MANAGER_URI . '/css/bootstrap.min.css', array(), '3.3.7', 'all' );

$instagram = new \MAKS\core\instagram();
$database  = new \MAKS\core\database();

$column_name_value = $this->database->get_column_name( 'value' );

$options = get_option( $instagram->get_option_key() );
$header  = $options['display_header'];
$media   = $options['display_media'];
$footer  = $options['display_load_more_button'];

$background_image_url = '';
$section_width = 3;

$content = ''."
<article id=\"container-fluid\">";

if($header) {

	function counts(int $count): string {

		$limit_k = 10000;
		$limit_m = 1000000;

		if($count >= $limit_k && $count < $limit_m) {

			$number_formatted = number_format( $count, 0, ',', '.' );
			$hundred          = substr( $number_formatted, -3, 1 );
			$size             = strlen($number_formatted);

			$is_zero = (int) $hundred == 0;

			if($is_zero) {

				$number_formatted = substr( $number_formatted, 0, $size - 4 );

			} else {

				$number_formatted = substr( $number_formatted, 0, $size - 2 );
			}

			return $number_formatted . 'k';

		} else if($count >= 1000000) {

			$number_formatted = number_format( $count, 0, ',', '.' );
			$thousand         = substr( $number_formatted, -7, 1 );
			$size             = strlen($number_formatted);

			$is_zero = (int) $thousand == 0;

			if($is_zero) {

				$number_formatted = substr( $number_formatted, 0, $size - 8 );

			} else {

				$number_formatted = substr( $number_formatted, 0, $size - 6 );
			}

			return $number_formatted . 'm';

		} else {

			$number_formatted = number_format( $count, 0, ',', '.' );

			return $number_formatted;
		}
	}

	$header_results = $database->get_results( 'instagram', ['LIKE' => 'header_%%'], 1);
	$header_value   = $header_results[0]->$column_name_value;
	$header_data    = $instagram->decode($header_value);

	$bio                = $header_data['bio'];
	$counts_followed_by = $header_data['counts']['followed_by'];
	$counts_follows     = $header_data['counts']['follows'];
	$counts_media       = $header_data['counts']['media'];
	$full_name          = $header_data['full_name'];
	$profile_picture    = $header_data['profile_picture'];
	$username           = $header_data['username'];
	$website            = $header_data['website'];

	$counts_followed_by_formatted = counts($counts_followed_by);
	$counts_follows_formatted     = counts($counts_follows);
	$counts_media_formatted       = counts($counts_media);

	$website_formatted = preg_replace( '#^https?://#', '', $website );

	$content .= ''."
	<header class=\"row\">
		<div class=\"col-md-4 maks-header-img\">
			<img style=\"margin: auto; display: block; border-radius: 50%;\" alt=\"{$full_name}\" src=\"{$profile_picture}\" height=\"152px\" width=\"152px\">
		</div>
		<div class=\"col-md-8\">
			<div class=\"row\">
				<h1 style=\"display: inline-block; vertical-align: middle;\">{$username}</h1>
				<button style=\"display: inline-block; vertical-align: middle; margin-left: 20px;\" type=\"button\" class=\"btn btn-default\">Follow</button>
			</div>
			<ul class=\"row list-inline lead\" style=\"padding-left: 0; margin-bottom: 0;\">
				<li><span><strong>{$counts_media_formatted}</strong> posts</span></li>
				<li><span><strong>{$counts_followed_by_formatted}</strong> followers</span></li>
				<li><span><strong>{$counts_follows_formatted}</strong> following</span></li>
			</ul>
			<div class=\"row\">
				<h2 class=\"small\">{$full_name}</h2>
				<span style=\"white-space: pre;\">{$bio}</span>
				<a style=\"display: block;\" href=\"{$website}\">{$website_formatted}</a>
			</div>
		</div>
	</header>";
}

if($media) {

	$media_results = $database->get_results( 'instagram', ['LIKE' => 'media_%%'], $options['display_number_media'] );

	$columns = 3;

	$factor = 12/$columns;
	$col_md = 'col-md-' . $factor;

	$times = 0;

	$content .= '' . "
	<main>";

	foreach ( $media_results as $object ) {

		if($times == 0)
			$content .= '' . "
		<div  class=\"row\">";


		$json = $object->$column_name_value;
		$data = $instagram->decode($json);

		$type         = $data['type'];
		$created_time = date( 'd/m/Y', $data['created_time'] );
		$location     = '';

		$content .= '' . "
		<section class=\"col-xs-12 {$col_md}\">
			<h1>Type: {$type}</h1>
			<h2>Created time: {$created_time}</h2>
			<div>
				<h1>Location: {$location}</h1>";

		$caption_text = $data['caption']['text'];

		$images_low_resolution_height = $data['images']['low_resolution']['height'];
		$images_low_resolution_url    = $data['images']['low_resolution']['url'];
		$images_low_resolution_width  = $data['images']['low_resolution']['width'];

		$images_standard_resolution_height = $data['images']['standard_resolution']['height'];
		$images_standard_resolution_url    = $data['images']['standard_resolution']['url'];
		$images_standard_resolution_width  = $data['images']['standard_resolution']['width'];

		$images_thumbnail_height = $data['images']['thumbnail']['height'];
		$images_thumbnail_url    = $data['images']['thumbnail']['url'];
		$images_thumbnail_width  = $data['images']['thumbnail']['width'];

		if ( $type == 'image' ) {

			$content .= '' . "
				<img alt=\"{$caption_text}\"
					 src=\"{$images_standard_resolution_url}\"
					 width=\"{$images_standard_resolution_width}\"
					 height=\"{$images_standard_resolution_height}\">";
		}

		if ( $type == 'video' ) {

			$videos_low_bandwidth_height = $data['videos']['low_bandwidth']['height'];
			$videos_low_bandwidth_url    = $data['videos']['low_bandwidth']['url'];
			$videos_low_bandwidth_width  = $data['videos']['low_bandwidth']['width'];

			$videos_low_resolution_height = $data['videos']['low_resolution']['height'];
			$videos_low_resolution_url    = $data['videos']['low_resolution']['url'];
			$videos_low_resolution_width  = $data['videos']['low_resolution']['width'];

			$videos_standard_resolution_height = $data['videos']['standard_resolution']['height'];
			$videos_standard_resolution_url    = $data['videos']['standard_resolution']['url'];
			$videos_standard_resolution_width  = $data['videos']['standard_resolution']['width'];

			$content .= '' . "
				<video poster=\"{$images_standard_resolution_url}\"
					   preload=\"none\"
					   src=\"{$videos_standard_resolution_url}\"
					   type=\"video/mp4\"
					   width=\"{$videos_standard_resolution_width}\"
					   height=\"{$videos_standard_resolution_height}\"
					   muted autoplay loop></video>";
		}

		$filter = $data['filter'];

		if($filter)
			$content .= ''."
				<span>Filter: {$filter}</span>";

		$likes_count    = $data['likes']['count'];
		$comments_count = $data['comments']['count'];

		$content .= '' . "
            </div>
			<p>{$caption_text}</p>
			<ul>
				<li value=\"likes\">{$likes_count}</li>
				<li value=\"comments\">{$comments_count}</li>
			</ul>
		</section>";

		$times++;

		if($times % $columns == 0) {

			$content .= '' . "
		</div>";

			$times = 0;
		}
	}

	$content .= '' . "
	</main>";

	if($footer)
		$content .= ''."
	<footer>
		<button>Loading more...</button>
	</footer>";
}

$content .= ''."
</article>";