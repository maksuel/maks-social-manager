<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 07/10/16
 * Time: 01:40
 */

/** Protection direct access */
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * @param $atts
 * @param $content
 */
function maks_instagram_print( $atts , $content ) {

	global $wpdb;
	$database = MAKS_DB_INSTAGRAM;
	$display = $atts['display'];

	$header_result = $wpdb->get_results(
		$wpdb->prepare("SELECT data_value FROM {$database} WHERE data_key NOT LIKE '%%^_%%' ESCAPE '^' ORDER BY date_time DESC LIMIT 1", '')
	);
	$header = json_decode($header_result[0]->data_value);
	$header_id = $header->id;

	$main_results = $wpdb->get_results(
		$wpdb->prepare("SELECT data_value FROM {$database} WHERE data_key LIKE '%%_{$header_id}' ORDER BY date_time DESC LIMIT {$display}", '')
	);

	wp_enqueue_style( 'maks-sm-reset' , MAKS_SOCIAL_MANAGER_URI . "/css/reset.css" , array() , '2.0.0' , 'all' );
	wp_enqueue_style( 'maks-sm-style' , MAKS_SOCIAL_MANAGER_URI . "/css/style.css" , array() , '0.1.0' , 'all' );

?>


<style>
	<?php
	if($atts['header']) :?>
	#maks-sm header::before {
		background-image: url('<?=$header->profile_picture?>');
	}
	<?php endif;
	if($atts['main']) :	?>
	#maks-sm main section {
		width: calc(100% / <?=$atts['col']?>);
	}
	<?php endif; ?>
</style>

<!--Instagram feed by MAKS Solutions-->
<article id="maks-sm">

<?php if($atts['header']) : ?>
	<header>
		<h1 id="igf-username"><?=$header->username?></h1>
		<h2 id="igf-full_name"><?=$header->full_name?></h2>
		<p id="igf-bio"><?=$header->bio?></p>
		<a id="igf-website" href="<?=$header->website?>"><?=preg_replace('(^https?://)', '', $header->website)?></a>
		<ul>
			<li id="igf-media"><?=$header->counts->media?></li>
			<li id="igf-followed_by"><?=$header->counts->followed_by?></li>
			<li id="igf-follows"><?=$header->counts->follows?></li>
		</ul>
	</header>
<?php endif; ?>

<?php if($atts['main']) : ?>
	<main>
	<?php foreach($main_results as $main) :

		$data = json_decode($main->data_value);
		$types = ["image", "video"]; // supported types
		if(!in_array($data->type, $types)) continue;

		?>
		<section>
			<h1>Type: <?=$data->type?></h1>
			<h2>Created time: <?=date('m/d/Y',$data->created_time)?></h2>
			<div>
				<h1>Location: ?</h1>
				<?php
				if($data->type == "image") :
					?>
					<img alt="<?=$data->caption->text?>"
					     src="<?=$data->images->standard_resolution->url?>"
					     width="<?=$data->images->standard_resolution->width?>"
					     height="<?=$data->images->standard_resolution->height?>">
					<?php
				elseif($data->type == "video") :
					?>
					<video poster="<?=$data->images->standard_resolution->url?>"
					       preload="none"
					       src="<?=$data->videos->standard_resolution->url?>"
					       type="video/mp4"
					       width="<?=$data->videos->standard_resolution->width?>"
					       height="<?=$data->videos->standard_resolution->height?>"
					       muted autoplay loop></video>
					<?php
				endif;
				?>
				<span>Filter: <?=$data->filter?></span>
			</div>
			<p><?=$data->caption->text?></p>
			<ul>
				<li value="likes"><?=$data->likes->count?></li>
				<li value="comments"><?=$data->comments->count?></li>
			</ul>
		</section>
	<?php endforeach; ?>
	</main>
<?php endif; ?>

<?php if($atts['footer']) : ?>
	<footer>
		<button>Loading more...</button>
	</footer>
<?php endif; ?>

</article>

<script src="<?= MAKS_SOCIAL_MANAGER_URI . "/js/scripts.js" ?>"></script>

<?php
}