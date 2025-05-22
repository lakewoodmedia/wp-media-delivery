<?php
/*
 * Plugin Name:       WP Media Delivery
 * Plugin URI:        https://wpfitter.com/plugins/wp-media-delivery/
 * Description:       Offload WordPress media to Amazon S3, Cloudflare R2, DigitalOcean Spaces, Min.io or Wasabi.
 * Version:           1.0.0-beta
 * Requires at least: 5.6
 * Requires PHP:      8.1
 * Author:            Fuunction
 * Author URI:        https://fuunction.agency/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-media-delivery
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('WPMD')) {
	/**
	 * The main WPMD class
	 */
	class WPMD
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
		 * @var WP_Media_Delivery\Offloader
		 */
		public $offloader;

		/**
		 * The plugin data array.
		 *
		 * @var array
		 */
		public $data = array();

		/**
		 * A dummy constructor to ensure WP Media Delivery is only setup once.
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
		 * Sets up the WP Media Delivery
		 *
		 * @since   1.0.0
		 *
		 * @return  void
		 */
		public function initialize()
		{

			// Define constants.
			$this->define('WPMD', true);
			$this->define('WPMD_PATH', plugin_dir_path(__FILE__));
			$this->define('WPMD_URL', plugin_dir_url(__FILE__));
			$this->define('WPMD_BASENAME', plugin_basename(__FILE__));
			$this->define('WPMD_VERSION', $this->version);
			$this->define('WPMD_API_VERSION', 1);

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
			if (file_exists(WPMD_PATH . 'vendor/scoper-autoload.php')) {
				require_once WPMD_PATH . 'vendor/scoper-autoload.php';
			} elseif (file_exists(WPMD_PATH . 'vendor/autoload.php')) {
				require_once WPMD_PATH . 'vendor/autoload.php';
			}

			$this->container = new \WP_Media_Delivery\Core\Container();

			$this->container->register('cloud_provider_factory', function ($c) {
				return new \WP_Media_Delivery\Factories\CloudProviderFactory();
			});

			// Register core services
			$this->container->register('cloud_provider', function ($c) {
				$cloud_provider_key = wpmd_get_cloud_provider_key();
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
					return \WP_Media_Delivery\Offloader::get_instance($c->get('cloud_provider'));
				}
				return null;
			});

			$this->container->register('settings_page', function ($c) {
				return \WP_Media_Delivery\Admin\GeneralSettings::create($c->get('cloud_provider_factory'));
			});

			$this->container->register('media_overview_page', function ($c) {
				return \WP_Media_Delivery\Admin\MediaOverview::getInstance();
			});

			$this->container->register('bulk_offload_handler', function ($c) {
				return \WP_Media_Delivery\BulkOffloadHandler::get_instance();
			});
		}

		private function setup_hooks()
		{
			# check if AWS SDK is loaded
			if (!class_exists(WPFitter\Aws\S3\S3Client::class)) {
				// Show admin notice if AWS SDK is missing.
				add_action('admin_notices', function () {
					$this->notice(__('AWS SDK for PHP is required to use WP Media Delivery. Please install it via Composer.', 'wp-media-delivery'), 'error');
				});
				return;
			}

			# Include admin if needed
			if (is_admin()) {
				$this->container->get('settings_page'); // Initialize settings
				$this->container->get('media_overview_page'); // Initialize media overview
				new \WP_Media_Delivery\Admin\Observers\CurrentScreen();

				# Add link to the settings page in the plugins list
				add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_action_links']);
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
			include_once WPMD_PATH . 'utility-functions.php';
		}

		public function plugin_action_links($links)
		{
			$settings_page_link = '<a href="' . esc_url(admin_url('admin.php?page=wpmd')) . '">' . __('Settings', 'wp-media-delivery') . '</a>';
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
			$cloud_provider_key = wpmd_get_cloud_provider_key();

			if ($cloud_provider_key) {
				try {
					// Use the Factory to create the cloud provider instance.
					$cloud_provider = WP_Media_Delivery\Factories\CloudProviderFactory::create($cloud_provider_key);

					// Instantiate the Offloader with the cloud provider.
					$this->offloader = WP_Media_Delivery\Offloader::get_instance($cloud_provider);
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
			// Set the first activated version of WP Media Delivery.
			if (null === get_option('wpmd_first_activated_version', null)) {
				update_option('wpmd_first_activated_version', WPMD_VERSION, true);
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
	}

	function wpmd()
	{
		global $wpmd;

		// Instantiate only once.
		if (!isset($wpmd)) {
			$wpmd = new WPMD();
			$wpmd->initialize();
		}
		return $wpmd;
	}

	// Instantiate.
	wpmd();
} // class_exists check 