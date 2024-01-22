<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://cronkite.asu.edu
 * @since      1.0.0
 *
 * @package    Rssnewsticker
 * @subpackage Rssnewsticker/partials
 */

define('DONOTCACHEPAGE', true);
header('Content-Type: '.feed_content_type('rss2').';charset='.get_option('blog_charset'), true);
echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.">";
do_action('rss_tag_pre', 'rss2');
?>

<rss version="2.0" <?php do_action('rss2_ns'); ?>>
<channel>
	<title><?php bloginfo_rss('name'); ?> - Ticker Feed</title>
	<link><?php bloginfo_rss('url') ?></link>
	<description>For Local use for news ticker</description>
	<?php do_action('rss2_head'); ?>

	<?php
	foreach ($lines as $line) {
		$line = sanitize_text_field($line);
	?>
	<item>
		<description><![CDATA[<?php echo wp_filter_nohtml_kses($line) ?>]]></description>
	<?php do_action('rss2_item'); ?>
	</item>
	<?php
	};
	?>
</channel>
</rss>
