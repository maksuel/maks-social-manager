<?php
/**
 * Created by IntelliJ IDEA.
 * User: mak
 * Date: 06/10/16
 * Time: 20:49
 */

/**
 * Requiring Header
 */

/** direct access protection */
defined( 'ABSPATH' ) or die( 'Direct access denied!' );

require_once 'header.php'; /*****************************************************************************************/?>
	<header>
		<h1>Settings</h1>
	</header>
	<section>
        <h3>
            <small>
                Choose what features you want to activating
            </small>
        </h3>
        <div class="checkbox">
            <label>
                <input type="checkbox" value="">
                Instagram
            </label>
        </div>
	</section>
<?php require_once 'footer.php'; /*************************************************************************************/