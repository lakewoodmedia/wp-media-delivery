<?php

/**
 * WP Media Delivery - Admin Navigation Menu
 */

declare(strict_types=1);

/**
 * Generate admin page URL for WP Media Delivery.
 *
 * @param string $tab The tab slug.
 * @return string The full admin URL.
 */
function advmo_get_admin_page_url(string $tab = 'general'): string
{
    if ($tab === 'general') {
        return get_admin_url(null, "tools.php?page=advmo");
    }
    return get_admin_url(null, "tools.php?page=advmo&tab={$tab}");
}

/**
 * Menu items configuration.
 */
$menu_items = [
    'general' => [
        'title' => __('General Settings', 'wp-media-delivery'),
        'url' => advmo_get_admin_page_url('general'),
    ],
    'media-overview' => [
        'title' => __('Media Overview', 'wp-media-delivery'),
        'url' => advmo_get_admin_page_url('media-overview'),
    ],
];

/**
 * Generate a menu item HTML.
 *
 * @param array $item Menu item configuration.
 * @param string $page tab slug.
 * @return string HTML for the menu item.
 */
function advmo_generate_menu_item(array $item, string $tab): string
{
    $class = advmo_is_settings_page($tab) ? 'active' : '';
    return sprintf(
        '<a href="%s" class="%s">%s</a>',
        esc_url($item['url']),
        esc_attr($class),
        esc_html($item['title'])
    );
}
?>

<div class="advmo-menu">
    <nav>
        <?php foreach ($menu_items as $slug => $item) : ?>
            <?php echo advmo_generate_menu_item($item, $slug); ?>
        <?php endforeach; ?>
    </nav>
</div>