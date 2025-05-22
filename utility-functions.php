<?php
if (!defined('ABSPATH')) {
	die('No direct script access allowed');
}

/**
 * Include a view file from the plugin directory.
 *
 * @param string $file The file path relative to the plugin directory.
 * @return void
 */
if (!function_exists('wpmd_get_view')) {
	function wpmd_get_view(string $template)
	{
		if (file_exists(WPMD_PATH . 'templates/' . $template . '.php')) {
			include WPMD_PATH . 'templates/' . $template . '.php';
		}
	}
}

/**
 * Debug function to var_dump a variable.
 *
 * @param mixed $var The variable to dump.
 * @param bool $die Whether to die after dumping.
 * @return void
 */
if (!function_exists('wpmd_vd')) {
	function wpmd_vd($var, bool $die = false): void
	{
		echo '<pre style="direction: ltr">';
		var_dump($var);
		echo '</pre>';
		if ($die) {
			die();
		}
	}
}


if (!function_exists('wpmd_is_settings_page')) {
	function wpmd_is_settings_page($page_name = ''): bool
	{
		$current_screen = get_current_screen();

		if (!$current_screen) {
			return false;
		}

		// Get the current page from the query string
		$current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

		// Define our plugin pages
		$plugin_pages = [
			'general' => 'wpmd',
			'media-overview' => 'wpmd_media_overview'
		];

		// If a specific page is requested
		if (!empty($page_name)) {
			if (!isset($plugin_pages[$page_name])) {
				return false; // Invalid page name provided
			}

			// Check if the current page matches the requested page
			return $current_page === $plugin_pages[$page_name];
		}

		// Check if we're on any plugin page
		return in_array($current_page, array_values($plugin_pages));
	}
}


/**
 * Generate copyright text
 *
 * @return string
 */
/**
 * Generate copyright and support text
 *
 * @return string
 */
if (!function_exists('wpmd_get_copyright_text')) {
	function wpmd_get_copyright_text(): string
	{
		$year = date('Y');
		$site_url = 'https://wpfitter.com/?utm_source=wp-plugin&utm_medium=plugin&utm_campaign=wp-media-delivery';

		return sprintf(
			'WP Media Delivery plugin developed by <a href="%s" target="_blank">WPFitter</a>. ',
			esc_url($site_url)
		);
	}
}

/**
 * Get bulk offload data.
 *
 * @return array The bulk offload data.
 */
if (!function_exists('wpmd_get_bulk_offload_data')) {
	function wpmd_get_bulk_offload_data(): array
	{
		$defaults = array(
			'total' => 0,
			'status' => '',
			'processed' => 0,
			'errors' => 0,
			'last_update' => null,
			'oversized_skipped' => 0
		);

		$stored_data = get_option('wpmd_bulk_offload_data', array());

		return array_merge($defaults, $stored_data);
	}
}

/**
 * Update bulk offload data.
 *
 * @param array $new_data The new data to update.
 * @return array The updated bulk offload data.
 */
if (!function_exists('wpmd_update_bulk_offload_data')) {
	function wpmd_update_bulk_offload_data(array $new_data): array
	{
		// Define the allowed keys
		$allowed_keys = array('total', 'status', 'processed', 'errors', 'oversized_skipped');

		// Filter the new data to only include allowed keys
		$filtered_new_data = array_intersect_key($new_data, array_flip($allowed_keys));

		// Get the existing data
		$existing_data = wpmd_get_bulk_offload_data();

		// Merge the filtered new data with the existing data
		$updated_data = array_merge($existing_data, $filtered_new_data);

		// Ensure only allowed keys are in the final data set
		$final_data = array_intersect_key($updated_data, array_flip($allowed_keys));

		// Add a timestamp for the last update
		$final_data['last_update'] = time();

		// Update the option in the database
		update_option('wpmd_bulk_offload_data', $final_data);

		return $final_data;
	}
}

/**
 * Check if media is organized by year and month.
 *
 * @return bool True if media is organized by year and month, false otherwise.
 */
if (!function_exists('wpmd_is_media_organized_by_year_month')) {
	function wpmd_is_media_organized_by_year_month(): bool
	{
		return get_option('uploads_use_yearmonth_folders') ? true : false;
	}
}

/**
 * Sanitize a URL path.
 *
 * @param string $path The path to sanitize.
 * @return string The sanitized path.
 */
if (!function_exists('wpmd_sanitize_path')) {
	function wpmd_sanitize_path(string $path): string
	{
		// Remove leading and trailing whitespace
		$path = trim($path);

		// Remove or encode potentially harmful characters
		$path = wp_sanitize_redirect($path);

		// Convert to lowercase for consistency (optional, depending on your needs)
		$path = strtolower($path);

		// Remove any directory traversal attempts
		$path = str_replace(['../', './'], '', $path);

		// Normalize slashes and remove duplicate slashes
		$path = preg_replace('#/+#', '/', $path);

		// Remove leading and trailing slashes
		$path = trim($path, '/');

		// Optionally, you can use wp_normalize_path() if you want to ensure consistent directory separators
		// $path = wp_normalize_path($path);

		return $path;
	}
}

/**
 * Clear bulk offload data.
 *
 * @return void
 */
if (!function_exists('wpmd_clear_bulk_offload_data')) {
	function wpmd_clear_bulk_offload_data(): void
	{
		delete_option('wpmd_bulk_offload_data');
	}
}

/**
 * Get the cloud provider key.
 *
 * @return string The cloud provider key.
 */
if (!function_exists('wpmd_get_cloud_provider_key')) {
	function wpmd_get_cloud_provider_key(): string
	{
		$options = get_option('wpmd_settings', []);
		return $options['cloud_provider'] ?? '';
	}
}

/**
 * Get the count of unoffloaded media items.
 *
 * @return int The count of unoffloaded media items.
 */
if (!function_exists('wpmd_get_unoffloaded_media_items_count')) {
	function wpmd_get_unoffloaded_media_items_count(): int
	{
		$args = [
			'fields' => 'ids',
			'numberposts' => -1,
			'post_type' => 'attachment',
			'post_status' => 'any',
			'meta_query' => [
				'relation' => 'OR',
				[
					'key' => 'wpmd_offloaded',
					'compare' => 'NOT EXISTS'
				],
				[
					'key' => 'wpmd_offloaded',
					'compare' => '=',
					'value' => ''
				]
			]
		];
		$attachments = get_posts($args);
		return count($attachments);
	}
}

if (!function_exists('wpmd_get_offloaded_media_items_count')) {
	function wpmd_get_offloaded_media_items_count()
	{
		$args = [
			'fields' => 'ids',
			'numberposts' => -1,
			'post_type' => 'attachment',
			'post_status' => 'any',
			'meta_query' => [
				[
					'key' => 'wpmd_offloaded',
					'compare' => '!=',
					'value' => ''
				]
			]
		];
		$attachments = get_posts($args);
		return count($attachments);
	}
}
