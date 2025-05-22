<?php
/*
** General settings content for WP Media Delivery.
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>
<h2 class="advmo-print-notices-after"></h2>
<form method="post" action="options.php">
    <?php settings_fields('advmo'); ?>
    <?php do_settings_sections('advmo'); ?>
    <?php submit_button(); ?>
</form> 