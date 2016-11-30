<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 06/10/16
 * Time: 20:53
 */

/**
 * Opening tag section for body page
 * IMPORTANT: needs to close body with footer.php
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

wp_enqueue_script(
    'bootstrap-3.3.7',
    MAKS_SOCIAL_MANAGER_URI . '/js/bootstrap.min.js',
    array(),
    '3.3.7',
    false
);
wp_enqueue_script(
	'clipboard.js',
	MAKS_SOCIAL_MANAGER_URI . '/js/clipboard.min.js',
	array(),
	'1.5.15',
	false
);
wp_enqueue_style(
    'bootstrap-3.3.7',
	MAKS_SOCIAL_MANAGER_URI . '/css/bootstrap.min.css',
    array(),
	'3.3.7',
    'all'
);

?>
<section class="container"">