<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 30/11/16
 * Time: 02:53
 */

?>
<style>
	.maks-register {
		max-width: 768px;
		margin: auto !important;
	}
	.maks-register ul li a {
		pointer-events: none;
	}
	.maks-register form {
		padding: 20px 15px 0px 15px;
	}
	.maks-register form .form-group label {
		text-align: left;
	}
	.maks-register form .form-group .help-block {
		padding-right: 15px;
		padding-left: 15px;
	}
</style>
<section>
	<h3>
		<small>
			Look this page below, there is information that you need to know.<br />
			She's exactly equals the page that you will access to create your application, therefore, follow the next steps.
		</small>
	</h3>
</section>
<hr />
<section class="row maks-register">
	<ul class="nav nav-tabs">
		<li role="presentation" class="active"><a href="#"><strong>Details</strong></a></li>
		<li role="presentation"><a href="#">Security</a></li>
	</ul>
	<form class="form-horizontal">
		<fieldset>
			<div class="form-group has-warning">
				<label for="id_name" class="col-sm-3 control-label">Application Name:</label>
				<div class="col-sm-9">
					<input type="text" id="id_name" class="form-control"
					       value="Social Manager by MAKS Solutions" readonly>
				</div>
			</div>
			<div class="form-group">
				<span class="help-block"><em>Do not use <strong>Instagram</strong>, <strong>IG</strong>, <strong>insta</strong> or <strong>gram</strong> in your app name. Make sure to adhere to the API Terms of Use and Brand Guidelines .</em></span>
			</div>
			<div class="form-group has-warning">
				<label for="id_description" class="col-sm-3 control-label">Description:</label>
				<div class="col-sm-9">
					<textarea id="id_description" class="form-control" cols="40" rows="5" readonly>This application has the objective to allow an Instagram user to authorize it to share content from their user ID, hashtag, or location ID. With this information the app then feeds a WordPress plugin which is designed to provide a beautiful and highly customize Instagram gallery.</textarea>
				</div>
			</div>
			<div class="form-group has-warning">
				<label for="id_company_name" class="col-sm-3 control-label">Company Name:</label>
				<div class="col-sm-9">
					<input type="text" id="id_company_name" class="form-control"
					       value="MAKS Solutions" readonly>
				</div>
			</div>
			<div class="form-group has-warning">
				<label for="id_website_url" class="col-sm-3 control-label">Website URL:</label>
				<div class="col-sm-9">
					<input type="text" id="id_website_url" class="form-control"
					       value="<?=$site_url?>" readonly>
				</div>
			</div>
			<div class="form-group has-error">
				<label for="id_redirect_uri-tokenfield" class="col-sm-3 control-label">Valid redirect URIs:</label>
				<div class="col-sm-9">
					<input type="text" id="id_redirect_uri-tokenfield" class="form-control"
					       value="<?=$valid_redirect_uri?>" readonly>
				</div>
			</div>
			<div class="form-group">
				<span class="help-block"><em>The redirect_uri specifies where we redirect users after they have chosen whether or not to authenticate your application.</em></span>
			</div>
			<div class="form-group">
				<label for="id_privacy_policy_url" class="col-sm-3 control-label">Privacy Policy URL:</label>
				<div class="col-sm-9">
					<input type="text" id="id_privacy_policy_url" class="form-control" readonly>
				</div>
			</div>
			<div class="form-group">
				<label for="id_contact_email" class="col-sm-3 control-label">Contact email:</label>
				<div class="col-sm-9">
					<input type="text" id="id_contact_email" class="form-control" readonly>
				</div>
			</div>
			<div class="form-group">
				<span class="help-block"><em>An email that Instagram can use to get in touch with you. Please specify a valid email address to be notified of important information about your app.</em></span>
			</div>
			<button type="button" class="btn btn-success" disabled="disabled"><strong>Register</strong></button>
			<button type="button" class="btn btn-default" disabled="disabled"><strong>Cancel</strong></button>
		</fieldset>
	</form>
</section>
<hr />
<section>
	<h3>
		1<sup>st</sup><br />
		<small>
			You can see de colors?!<br />
			Yellow represent the information that can be modificaded, it's ons example.<br />
			Red represent the exact information that you need to copy and paste in your application!!!
		</small>
	</h3>
</section>
<section>
	<h3>
		2<sup>nd</sup><br />
		<small>
			you only need to click in the button bellow and copy important information and complete the steps to create the base for plugin.
		</small>
	</h3>
	<a class="btn btn-primary" href="https://www.instagram.com/accounts/login/?next=%2Fdeveloper%2Fclients%2Fregister%2F" target="_blank">Go to Instagram Developers</a>
</section>
<hr />
<section>
	<h3>
		3<sup>rd</sup><br />
		<small>
			text
		</small>
	</h3>
</section>
<hr />