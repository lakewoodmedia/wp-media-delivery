<?php
/*
** Main admin page for WP Media Delivery.
** This page includes the navigation tabs and loads the appropriate tab content.
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// check user capabilities
if (!current_user_can('manage_options')) {
    return;
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>
<div id="advmo">
    <div class="wrap">
        <h1><?php echo esc_html__('WP Media Delivery', 'wp-media-delivery'); ?></h1>
        
        <?php include_once ADVMO_PATH . 'templates/admin/navmenu.php'; ?>
        
        <div class="advmo-tab-content">
            <?php 
            // Load the appropriate tab content
            if ($current_tab === 'media-overview') {
                // Don't include the h2 tag and wrapper divs since they are already in this template
                advmo_get_view('admin/media_overview_content');
            } else {
                // Default to general settings
                advmo_get_view('admin/general_settings_content');
            }
            ?>
        </div>
    </div>
</div> 