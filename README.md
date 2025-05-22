# WP Media Delivery

A WordPress plugin that offloads media to cloud storage providers like Amazon S3, Cloudflare R2, DigitalOcean Spaces, Min.io, or Wasabi, with support for WebP conversion and optimizers like ShortPixel.

## Description

WP Media Delivery helps you optimize your WordPress site by offloading media files to cloud storage providers. This reduces the load on your server, improves performance, and can reduce costs. The plugin supports:

- Automatic uploading of new media to cloud storage
- WebP conversion for images
- Offloading existing media via bulk operations
- Integration with ShortPixel and Imagify optimizers
- Option to delete local files after offloading
- Custom domain support for URLs

## Features

- **Seamless Cloud Integration**: Automatically uploads media files to your preferred cloud provider and serves them from their global network
- **Multiple Provider Support**: Works with Amazon S3, Cloudflare R2, DigitalOcean Spaces, Min.io, and Wasabi
- **WebP Conversion**: Creates and serves WebP versions of uploaded images for modern browsers, improving load times
- **Page Builder Compatible**: Works with popular page builders like Elementor and Bricks through standard WordPress hooks
- **Local Cleanup**: Optionally removes media files from your local server after successful offloading
- **Original Image Removal**: Option to remove original images when using WebP to further reduce storage requirements
- **All Media Types Support**: Handles all WordPress media types, not just images
- **Cloud Cleanup**: Automatically deletes files from cloud storage when they're removed from WordPress
- **Push/Pull Media**: Tools to push and pull media between WordPress and cloud storage
- **ShortPixel Integration**: Supports ShortPixel optimization workflow, offloading images after they've been compressed
- **Imagify Integration**: Supports Imagify optimization workflow, offloading images after they've been compressed
- **Batch Processing**: Handles large media libraries (30,000+ images) through an efficient background batch processing system
- **Easy Administration**: Simple settings page with intuitive configuration options
- **Progress Tracking**: Visual progress indicator for bulk offloading operations

## Requirements

* WordPress 5.6 or higher
* PHP 8.1 or higher
* Cloud storage account with API access
* Composer (for installation)
* GD library for PHP (for WebP conversion)

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-media-delivery` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings->WP Media Delivery screen to configure the plugin

## Configuration Constants

You can define the following constants in your wp-config.php file to override the plugin settings:

```php
// General Settings
define('WPMD_PROVIDER', 's3');  // Options: 's3', 'r2', 'spaces', 'minio', 'wasabi'
define('WPMD_REGION', 'us-east-1');  // Region for your cloud provider
define('WPMD_ACCESS_KEY', 'your-access-key');
define('WPMD_SECRET_KEY', 'your-secret-key');
define('WPMD_BUCKET', 'your-bucket-name');
define('WPMD_ENDPOINT', 'https://custom-endpoint.com');  // For R2, Spaces, Min.io, Wasabi

// Feature Toggles
define('WPMD_DELETE_LOCAL', true);  // Delete local files after offloading
define('WPMD_ENABLE_WEBP', true);  // Enable WebP conversion
define('WPMD_DELETE_ORIGINAL', false);  // Delete original images when using WebP
define('WPMD_CUSTOM_DOMAIN', 'https://cdn.yourdomain.com');  // Custom domain for URLs
```

## Usage

1. After activation, go to the plugin settings page
2. Select your preferred cloud storage provider
3. Enter your credentials for the selected provider
4. Configure additional settings as needed
5. Start offloading your media to the cloud!

## Plugin Structure

The plugin follows a modular architecture for maintainability and extensibility:

```
wp-media-delivery/
├── assets/                  # Frontend assets (CSS, JS, images)
├── includes/                # Core plugin files
│   ├── Abstracts/           # Abstract classes
│   ├── Admin/               # Admin-related functionality
│   │   └── Observers/       # Admin-specific observers
│   ├── Core/                # Core plugin functionality
│   ├── Factories/           # Factory classes for provider creation
│   ├── Integrations/        # Cloud provider integrations
│   ├── Interfaces/          # Interface definitions
│   ├── Observers/           # Observer pattern implementations
│   ├── Services/            # Service classes
│   └── Traits/              # Shared traits
├── templates/               # Templates for admin UI
├── vendor/                  # Composer dependencies
├── CHANGELOG.md             # Detailed change history
├── README.md                # Plugin documentation
├── build.sh                 # Build script for releases
├── composer.json            # Composer dependencies
├── utility-functions.php    # Utility functions
└── wp-media-delivery.php    # Main plugin file
```

**Key Files:**
- `wp-media-delivery.php`: Main plugin entry point
- `includes/Offloader.php`: Core offloading functionality
- `includes/Abstracts/S3_Provider.php`: Abstract provider class for S3-compatible storage
- `includes/Admin/GeneralSettings.php`: Settings page functionality
- `includes/Admin/MediaOverview.php`: Media overview page
- `includes/Observers/`: Observer pattern implementation for WordPress hooks
- `includes/Services/CloudAttachmentUploader.php`: Service for uploading attachments

> **Note**: When making changes to the plugin structure, please ensure this section is updated accordingly.

## Changelog

We maintain a detailed changelog to track all changes to the plugin. See the [CHANGELOG.md](CHANGELOG.md) file for a complete history of changes.

Recent changes include:
- Version 1.0.1-beta: Added a media library status column to show storage location
- Version 1.0.0-rc: Initial release candidate

## Credits

This plugin is a fork of [Advanced Media Offloader](https://wordpress.org/support/plugin/advanced-media-offloader/) by WP Fitter. We extend our thanks to the original developers for their work.

## License

This plugin is licensed under the GPL v2 or later.
