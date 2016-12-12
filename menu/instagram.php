<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 06/10/16
 * Time: 21:29
 */

/**
 * Requiring Header
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

$host_prefix        = 'http' . ( isset($_SERVER['HTTPS']) ? 's' : '' ) . '://';
$http_host          = $_SERVER['HTTP_HOST'];
$request_uri        = $_SERVER['REQUEST_URI'];
$full_uri           = $host_prefix . $http_host . $request_uri;
$split_uri          = explode('?', $full_uri);
$valid_redirect_uri = $split_uri[0] . '?page=maks-instagram';

$instagram_instance = new maks_instagram('update');

if( isset($_POST) ) {

	$calls_values = [];

	foreach($_POST as $key => $value) {
        if( !empty($value) ) $calls_values[$key] = $value;
	}

    $instagram_instance->update_options($calls_values);
}

$client_id_response = $instagram_instance->get_option_value('client_id');
$client_id_value    = empty($client_id_response) ? 'Client ID' : $client_id_response;

$client_secret_response = $instagram_instance->get_option_value('client_secret');
$client_secret_value    = empty($client_secret_response) ? 'Client Secret' : $client_secret_response;

$access_token_response = $instagram_instance->get_option_value('access_token');
$access_token_value    = empty($access_token_response) ? '' : $access_token_response;

$url_access_token  = 'https://www.instagram.com/oauth/authorize/';
$url_access_token .= '?client_id=' . $client_id_value;
$url_access_token .= '&redirect_uri=' . $valid_redirect_uri;
$url_access_token .= '&response_type=code';

if( isset($_GET['code']) && !$access_token_response) {

	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, 'https://api.instagram.com/oauth/access_token');
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS,
		'client_id=' . $client_id_value .
		'&client_secret=' . $client_secret_value .
		'&grant_type=authorization_code' .
		'&redirect_uri=' . $valid_redirect_uri .
		'&code=' . $_GET['code']
	);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	$response = curl_exec($curl);

	curl_close ($curl);

	$response           = json_decode($response, true);
	$access_token_value = $response['access_token'];

	$calls_values['access_token'] = $access_token_value;

	$instagram_instance->update_options($calls_values);
}

$visible = false;
if($client_id_response && $client_secret_response && !$access_token_value) $visible = true;

require_once 'header.php'; /*****************************************************************************************/?>
	<header>
		<h1>Instagram Configuration</h1>
	</header>
<?php
if($visible) {
	require 'instagram/register.php';
}
?>
	<section>
        <form method="post" action="">

            <div class="form-group">
                <label for="client_id" class="cols-sm-2 control-label">Client ID</label>
                <div class="cols-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="dashicons dashicons-id"></span>
                        </span>
                        <input type="text" class="form-control" name="client_id" id="client_id"
                               placeholder="<?=$client_id_value?>"
                               pattern="[a-z0-9]{32}"/>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="client_secret" class="cols-sm-2 control-label">Client Secret</label>
                <div class="cols-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="dashicons dashicons-shield-alt"></span>
                        </span>
                        <input type="text" class="form-control" name="client_secret" id="client_secret"
                               placeholder="<?=$client_secret_value?>"
                               pattern="[a-z0-9]{32}"/>
                    </div>
                </div>
            </div>

            <?php

            if($visible) {

	            ?>
                <div class="form-group ">
                    <a href="<?=$url_access_token?>" class="btn btn-success">Get Access Token</a>
                </div>
	            <?php
            }
            ?>

            <div class="form-group">
                <label for="access_token" class="cols-sm-2 control-label">Access Token</label>
                <div class="cols-sm-10">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <span class="dashicons dashicons-admin-network"></span>
                        </span>
                        <input type="text" class="form-control" id="access_token"
                               placeholder="<?=$access_token_value?>" readonly/>
                    </div>
                </div>
            </div>

            <div class="form-group ">
                <button type="submit" class="btn btn-primary btn-lg btn-block login-button">Save</button>
            </div>

            <div class="checkbox">
                <label>
                    <input type="checkbox" value="">
                    Preserve settings when plugin is removed ???
                </label>
            </div>

        </form>
	</section>
<?php require_once 'footer.php'; /*************************************************************************************/