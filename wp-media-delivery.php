<?php
/*
 * Plugin Name:       WP Media Delivery
 * Description:       Offload WordPress media to Amazon S3, Cloudflare R2, DigitalOcean Spaces, Min.io or Wasabi.
 * Version:           1.0.13-beta
 * Requires at least: 5.6
 * Requires PHP:      8.1
 * Author:            Fuunction
 * Author URI:        https://fuunction.agency
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-media-delivery
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('ADVMO')) {
	/**
	 * The main ADVMO class
	 */
	class ADVMO
	{

		/** @var Container */
		public $container;

		/**
		 * The plugin version number.
		 *
		 * @var string
		 */
		public $version;

		/**
		 * The offloader instance.
		 *
		 * @var Advanced_Media_Offloader\Offloader
		 */
		public $offloader;

		/**
		 * The plugin data array.
		 *
		 * @var array
		 */
		public $data = array();

		/**
		 * A dummy constructor to ensure WP Fitter Media Offloader is only setup once.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function __construct()
		{
			$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
			$this->version = $plugin_data['Version'];
		}

		/**
		 * Sets up the Advanced Media Offloader
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function initialize()
		{

			// Define constants.
			$this->define('ADVMO', true);
			$this->define('ADVMO_PATH', plugin_dir_path(__FILE__));
			$this->define('ADVMO_URL', plugin_dir_url(__FILE__));
			$this->define('ADVMO_BASENAME', plugin_basename(__FILE__));
			$this->define('ADVMO_VERSION', $this->version);
			$this->define('ADVMO_API_VERSION', 1);

			// Register activation hook.
			register_activation_hook(__FILE__, array($this, 'plugin_activated'));

			// Set up container
			$this->setup_container();

			// Include files and setup WordPress hooks
			$this->include_files();
			$this->setup_hooks();
		}

		private function setup_container()
		{
			// Include autoloader
			if (file_exists(ADVMO_PATH . 'vendor/scoper-autoload.php')) {
				require_once ADVMO_PATH . 'vendor/scoper-autoload.php';
			} elseif (file_exists(ADVMO_PATH . 'vendor/autoload.php')) {
				require_once ADVMO_PATH . 'vendor/autoload.php';
			}

			$this->container = new \Advanced_Media_Offloader\Core\Container();

			$this->container->register('cloud_provider_factory', function ($c) {
				return new \Advanced_Media_Offloader\Factories\CloudProviderFactory();
			});

			// Register core services
			$this->container->register('cloud_provider', function ($c) {
				$cloud_provider_key = advmo_get_cloud_provider_key();
				if (empty($cloud_provider_key)) {
					return null;
				}

				if ($c->has('cloud_provider_factory')) {
					$cloud_provider_factory = $c->get('cloud_provider_factory');
					return $cloud_provider_factory::create($cloud_provider_key);
				}

				return null;
			});

			$this->container->register('offloader', function ($c) {
				if ($c->has('cloud_provider') && $c->get('cloud_provider') !== null) {
					return \Advanced_Media_Offloader\Offloader::get_instance($c->get('cloud_provider'));
				}
				return null;
			});

			$this->container->register('settings_page', function ($c) {
				return \Advanced_Media_Offloader\Admin\GeneralSettings::create($c->get('cloud_provider_factory'));
			});

			$this->container->register('media_overview_page', function ($c) {
				return \Advanced_Media_Offloader\Admin\MediaOverview::getInstance();
			});

			$this->container->register('bulk_offload_handler', function ($c) {
				return \Advanced_Media_Offloader\BulkOffloadHandler::get_instance();
			});
		}

		private function setup_hooks()
		{
			# check if AWS SDK is loaded
			if (!class_exists(WPFitter\Aws\S3\S3Client::class)) {
				// Show admin notice if AWS SDK is missing.
				add_action('admin_notices', function () {
					$this->notice(__('AWS SDK for PHP is required to use Advanced Media Offloader. Please install it via Composer.', 'wp-media-delivery'), 'error');
				});
				return;
			}

			# Include admin if needed
			if (is_admin()) {
				$this->container->get('settings_page'); // Initialize settings
				$this->container->get('media_overview_page'); // Initialize media overview
				
				// Ensure the MediaLibraryColumns file is included
				require_once ADVMO_PATH . 'includes/Admin/Observers/MediaLibraryColumns.php';
				
				new \Advanced_Media_Offloader\Admin\Observers\CurrentScreen();

				# Add link to the settings page in the plugins list
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
				
				# Handle individual media offload action
				add_action('admin_init', [$this, 'handle_media_offload_action']);
				
				# Handle fetch back action
				add_action('admin_init', [$this, 'handle_media_fetch_action']);
			}

			# Initialize offloader if cloud provider is configured
			if ($this->container->has('offloader') && $this->container->get('offloader') !== null) {
				$this->container->get('offloader')->initializeHooks();
			}

			# Initialize bulk offload handler
			$this->container->get('bulk_offload_handler');
		}

		private function include_files()
		{
			# include Utility Functions
			include_once ADVMO_PATH . 'utility-functions.php';
		}

		public function plugin_action_links($links)
		{
			$settings_page_link = '<a href="' . esc_url(admin_url('admin.php?page=advmo')) . '">' . __('Settings', 'wp-media-delivery') . '</a>';
			array_unshift($links, $settings_page_link);
			return $links;
		}

		/**
		 * Completes the setup process on "init" of earlier.
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function init()
		{
			// Load textdomain file.
			load_plugin_textdomain('wp-media-delivery', false, dirname(plugin_basename(__FILE__)) . '/languages/');

			// Get selected cloud provider in plugin settings page.
			$cloud_provider_key = advmo_get_cloud_provider_key();

			if ($cloud_provider_key) {
				try {
					// Use the Factory to create the cloud provider instance.
					$cloud_provider = Advanced_Media_Offloader\Factories\CloudProviderFactory::create($cloud_provider_key);

					// Instantiate the Offloader with the cloud provider.
					$this->offloader = Advanced_Media_Offloader\Offloader::get_instance($cloud_provider);
					$this->offloader->initializeHooks();
				} catch (Exception $e) {
					// Handle exception or display admin notice.
					add_action('admin_notices', function () use ($e) {
						$this->notice($e->getMessage(), 'error');
					});
				}
			}
		}

		/**
		 * Plugin Activation Hook
		 *
		 * @since 1.0.0
		 */
		public function plugin_activated()
		{
			// Set the first activated version of Advanced Media Offloader.
			if (null === get_option('advmo_first_activated_version', null)) {
				update_option('advmo_first_activated_version', ADVMO_VERSION, true);
			}
		}

		public function define($name, $value = true)
		{
			if (!defined($name)) {
				define($name, $value);
			}
		}

		public function notice($message, $type = 'info')
		{
			$class = 'notice notice-' . $type;
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_attr($message));
		}

		/**
		 * Handle the single media offload action.
		 *
		 * @return void
		 */
		public function handle_media_offload_action()
		{
			if (!isset($_GET['action']) || $_GET['action'] !== 'advmo_offload_media' || !isset($_GET['attachment_id'])) {
				return;
			}
			
			$attachment_id = intval($_GET['attachment_id']);
			
			// Verify nonce
			if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'advmo_offload_media_' . $attachment_id)) {
				wp_die(__('Security check failed.', 'wp-media-delivery'));
			}
			
			// Check permissions
			if (!current_user_can('upload_files')) {
				wp_die(__('You do not have permission to offload media files.', 'wp-media-delivery'));
			}
			
			// Check if already offloaded
			if (get_post_meta($attachment_id, 'advmo_offloaded', true)) {
				wp_redirect(add_query_arg('advmo_offload_success', '1', admin_url('upload.php')));
				exit;
			}
			
			$success = false;
			
			// Get cloud provider and attempt to offload
			if ($this->container->has('offloader') && $this->container->get('offloader') !== null) {
				$offloader = $this->container->get('offloader');
				$uploader = new \Advanced_Media_Offloader\Services\CloudAttachmentUploader($offloader->cloudProvider);
				
				$success = $uploader->uploadAttachment($attachment_id);
			}
			
			if ($success) {
				wp_redirect(add_query_arg('advmo_offload_success', '1', admin_url('upload.php')));
				exit;
			} else {
				wp_redirect(add_query_arg('advmo_offload_error', '1', admin_url('upload.php')));
				exit;
			}
		}

		/**
		 * Handle the fetch back action.
		 *
		 * @return void
		 */
		public function handle_media_fetch_action()
		{
			if (!isset($_GET['action']) || $_GET['action'] !== 'advmo_fetch_media' || !isset($_GET['attachment_id'])) {
				return;
			}
			
			$attachment_id = intval($_GET['attachment_id']);
			
			// Verify nonce
			if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'advmo_fetch_media_' . $attachment_id)) {
				wp_die(__('Security check failed.', 'wp-media-delivery'));
			}
			
			// Check permissions
			if (!current_user_can('upload_files')) {
				wp_die(__('You do not have permission to manage media files.', 'wp-media-delivery'));
			}
			
			// Check if media is offloaded
			if (!get_post_meta($attachment_id, 'advmo_offloaded', true)) {
				wp_redirect(add_query_arg('advmo_fetch_error', 'not_offloaded', admin_url('upload.php')));
				exit;
			}
			
			$success = false;
			
			// Get cloud provider
			if ($this->container->has('offloader') && $this->container->get('offloader') !== null) {
				$offloader = $this->container->get('offloader');
				$cloud_provider = $offloader->cloudProvider;
				
				// Get file path information
				$attached_file = get_attached_file($attachment_id);
				$file_dir = dirname($attached_file);
				$file_name = basename($attached_file);
				
				// Create the directory if it doesn't exist
				if (!file_exists($file_dir)) {
					wp_mkdir_p($file_dir);
				}
				
				// Get metadata for thumbnails
				$metadata = wp_get_attachment_metadata($attachment_id);
				
				// Get the URL to the file in cloud storage
				$cloud_url = wp_get_attachment_url($attachment_id);
				
				// Download the original file
				$this->fetch_remote_file_to_local($cloud_url, $attached_file);
				
				// Download thumbnails if they exist
				if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
					foreach ($metadata['sizes'] as $size => $size_data) {
						$thumb_path = $file_dir . '/' . $size_data['file'];
						$thumb_url = dirname($cloud_url) . '/' . $size_data['file'];
						$this->fetch_remote_file_to_local($thumb_url, $thumb_path);
					}
				}
				
				// Delete from cloud storage
				$cloud_provider->deleteAttachment($attachment_id);
				
				// Update metadata to indicate it's no longer in cloud storage
				delete_post_meta($attachment_id, 'advmo_offloaded');
				delete_post_meta($attachment_id, 'advmo_provider');
				delete_post_meta($attachment_id, 'advmo_provider_type');
				delete_post_meta($attachment_id, 'advmo_bucket');
				delete_post_meta($attachment_id, 'advmo_path');
				delete_post_meta($attachment_id, 'advmo_offloaded_at');
				
				$success = true;
			}
			
			if ($success) {
				wp_redirect(add_query_arg('advmo_fetch_success', '1', admin_url('upload.php')));
				exit;
			} else {
				wp_redirect(add_query_arg('advmo_fetch_error', 'general', admin_url('upload.php')));
				exit;
			}
		}
		
		/**
		 * Download a remote file to the local filesystem.
		 *
		 * @param string $url  The URL of the remote file.
		 * @param string $path The local path to save the file to.
		 * @return bool Whether the download was successful.
		 */
		private function fetch_remote_file_to_local($url, $path)
		{
			$response = wp_remote_get($url, [
				'timeout'     => 60,
				'sslverify'   => false,
				'stream'      => true,
				'filename'    => $path,
			]);
			
			if (is_wp_error($response)) {
				error_log('WP Media Delivery: Error downloading file: ' . $response->get_error_message());
				return false;
			}
			
			if (wp_remote_retrieve_response_code($response) !== 200) {
				error_log('WP Media Delivery: Error downloading file: ' . wp_remote_retrieve_response_message($response));
				return false;
			}
			
			return true;
		}
	}

	function advmo()
	{
		global $advmo;

		// Instantiate only once.
		if (!isset($advmo)) {
			$advmo = new ADVMO();
			$advmo->initialize();
		}
		return $advmo;
	}

	// Instantiate.
	advmo();
} // class_exists check 