<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 12/12/16
 * Time: 09:42
 */

namespace MAKS;
use MAKS\core\services as services;

require_once 'core/services.php';

class requirements extends services {

    public function __construct() {

	    $this->require_wp_header();
    }
}

new requirements();

global $wp_version;

$language = strtolower( get_bloginfo('language') );
$default  = 'en-us';

$text = array(
	'title' => array(
		'en-us' => 'Requirements',
		'pt-br' => 'Requerimentos'
	),
	'php_requirement' => array(
		'en-us' => 'version 7 or greater',
		'pt-br' => 'versão 7 ou maior'
	),
	'wp_requirement' => array(
		'en-us' => 'version 4.7 or greater',
		'pt-br' => 'versão 4.7 ou maior'
	),
	'current_php' => array(
		'en-us' => 'Current PHP version',
		'pt-br' => 'Versão atual do PHP'
	),
	'current_wp' => array(
		'en-us' => 'Current WordPress version',
		'pt-br' => 'Versão atual do WordPress'
	)
);

foreach($text as $key => $item) {

	$item = $item[$language] ?? $item[$default];
	$text[$key] = $item;
}
?>
<h1><?=$text['title']?></h1>
<ul>
	<li><a href="http://www.php.net/">PHP</a> <?=$text['php_requirement']?></li>
	<li><a href="https://wordpress.org/">WordPress</a> <?=$text['wp_requirement']?></li>
</ul>
<p>
	<em><?=$text['current_php']?>: <?=PHP_VERSION?><br /><?=$text['current_wp']?>: <?=$wp_version?></em>
</p>