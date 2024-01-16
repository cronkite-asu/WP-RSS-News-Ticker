<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://cronkite.asu.edu
 * @since      1.0.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/admin/partials
 */
?>

	<div class="wrap">
		<h2><?php $page_title ?></h2>
		<form method="post" action="options.php">
<?php
settings_fields($this->plugin_name);
do_settings_sections($this->plugin_name);
submit_button();
?>
		</form>
	</div>
