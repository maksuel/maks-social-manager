<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 23/10/16
 * Time: 21:58
 */

namespace MAKS;
use MAKS\core\services as services;

function maks_check_validate_get(array $validate_inputs, string $param_get) {

	$param_get = strtolower($param_get);

	if( in_array($param_get, $validate_inputs) ||
		filter_var($param_get, FILTER_VALIDATE_BOOLEAN) ) {

		return $param_get;

	} else {

		return false;
	}
}

$maks_instagram_validate_inputs = [ 'next' ];
$maks_instagram = $_GET['instagram'] ?? false;
if($maks_instagram)
	$maks_instagram = maks_check_validate_get( $maks_instagram_validate_inputs, $maks_instagram );

/** Only continue if user set specific parameters */
( $maks_instagram ) or die('Set the parameters.');

require_once 'core/services.php';

class update extends services {

	private $instagram;

	public function __construct(string $social, bool $next = false) {

		$this->require_wp_header();

		if($social == 'instagram') $this->instagram();

		http_response_code(200);
	}

	private function instagram() {

		require_once 'core/instagram.php';

		$this->instagram = new core\instagram('update');
		$this->instagram->update();
	}
}

if($maks_instagram) {

	$maks_instagram == 'next' ?
		new update('instagram', true) :
		new update('instagram');
}