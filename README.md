# WP Media Delivery

Offload WordPress media to Amazon S3, Cloudflare R2, DigitalOcean Spaces, Min.io or Wasabi.

**WP Media Delivery** helps you optimize your WordPress media handling by automatically uploading your media files to S3-compatible cloud storage services.

Struggling with server space limitations? Want to improve your site's performance by serving media through a CDN? This plugin handles the technical work of migrating your media to the cloud, rewriting URLs, and maintaining compatibility with your existing content.

## Key Benefits

* Reduce server storage requirements and costs
* Decrease server load when serving media files
* Improve global site loading speeds when combined with CDN services
* Maintain full compatibility with WordPress media functions
* No need to modify existing content - URLs are automatically rewritten

## Supported Cloud Providers

* **Amazon S3** - The industry standard object storage service
* **Cloudflare R2** - S3-compatible storage with zero egress fees
* **DigitalOcean Spaces** - Simple object storage from DigitalOcean
* **MinIO** - Self-hosted, S3-compatible object storage
* **Wasabi** - Hot cloud storage with predictable pricing

## Features

### Current Features
* **Automatic Offloading** - New media uploads are automatically sent to your cloud storage
* **Bulk Migration** - Easily move existing media to the cloud (50 files per batch)
* **Smart URL Rewriting** - All media URLs are automatically rewritten to serve from cloud storage
* **File Versioning** - Add unique timestamps to media paths to prevent caching issues
* **Flexible Retention** - Choose to keep local copies or remove them after successful offloading
* **Mirror Deletion** - Optionally remove files from cloud storage when deleted from WordPress
* **Custom Paths** - Configure custom path prefixes in your cloud storage
* **Developer-Friendly** - Action hooks for extending functionality
* **CloudFront integration** - Deliver media through Amazon's CDN
* **Support for custom domains** - Use your own domain for media delivery
* **All Media Types Support** - Handles all WordPress media types, not just images
* **Page Builder Compatible** - Works with popular page builders like Elementor and Bricks through standard WordPress hooks
* **Easy Administration** - Simple settings page with intuitive configuration options

### Planned Features
* **WebP Conversion** - Automatically create and serve WebP versions of uploaded images for modern browsers _(coming soon)_
* **ShortPixel Integration** - Support ShortPixel optimization workflow, offloading images after compression _(coming soon)_
* **Imagify Integration** - Support Imagify optimization workflow, offloading images after compression _(coming soon)_
* **Original Image Removal** - Option to remove original images when using WebP _(coming soon)_
* **Push/Pull Media Tools** - Tools to push and pull media between WordPress and cloud storage _(coming soon)_
* **Advanced Progress Tracking** - Visual progress indicator for bulk offloading operations _(coming soon)_

## Requirements

- WordPress 5.6 or higher
- PHP 8.1 or higher
- Composer (for development)

## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-media-delivery` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Add your cloud provider credentials to `wp-config.php` (see configuration examples below)
4. Configure the plugin by going to the WP Media Delivery settings page.
5. Test your connection and start offloading media

## Configuration

For security, cloud provider credentials are stored in your `wp-config.php` file rather than the database.

**[Cloudflare R2](https://developers.cloudflare.com/r2/) Configuration**
```php
define('WPMD_CLOUDFLARE_R2_KEY', 'your-access-key');
define('WPMD_CLOUDFLARE_R2_SECRET', 'your-secret-key');
define('WPMD_CLOUDFLARE_R2_BUCKET', 'your-bucket-name');
define('WPMD_CLOUDFLARE_R2_DOMAIN', 'your-domain-url');
define('WPMD_CLOUDFLARE_R2_ENDPOINT', 'your-endpoint-url');
```

**[DigitalOcean Spaces](https://www.digitalocean.com/products/spaces) Configuration**
```php
define('WPMD_DOS_KEY', 'your-access-key');
define('WPMD_DOS_SECRET', 'your-secret-key');
define('WPMD_DOS_BUCKET', 'your-bucket-name');
define('WPMD_DOS_DOMAIN', 'your-domain-url');
define('WPMD_DOS_ENDPOINT', 'your-endpoint-url');
```

**[MinIO](https://min.io/docs/minio/linux/administration/identity-access-management/minio-user-management.html) Configuration**
```php
define('WPMD_MINIO_KEY', 'your-access-key');
define('WPMD_MINIO_SECRET', 'your-secret-key');
define('WPMD_MINIO_BUCKET', 'your-bucket-name');
define('WPMD_MINIO_DOMAIN', 'your-domain-url');
define('WPMD_MINIO_ENDPOINT', 'your-endpoint-url');
```

**[Amazon S3](https://aws.amazon.com/s3/) Configuration**
```php
define('WPMD_AWS_KEY', 'your-access-key');
define('WPMD_AWS_SECRET', 'your-secret-key');
define('WPMD_AWS_BUCKET', 'your-bucket-name');
define('WPMD_AWS_REGION', 'your-bucket-region');
define('WPMD_AWS_DOMAIN', 'your-domain-url');
```

**[Wasabi](https://docs.wasabi.com/docs/creating-a-new-access-key) Configuration**
```php
define('WPMD_WASABI_KEY', 'your-access-key');
define('WPMD_WASABI_SECRET', 'your-secret-key');
define('WPMD_WASABI_BUCKET', 'your-bucket-name');
define('WPMD_WASABI_REGION', 'your-bucket-region');
define('WPMD_WASABI_DOMAIN', 'your-domain-url');
```

## Development

### Setup

1. Clone this repository
2. Run `composer install` to install dependencies

### Building

To build the plugin for release:

```
./build.sh
```

This will create a zip file in the `/releases` directory.

## License

This plugin is licensed under GPL v2 or later.

## Credits

Developed by [Fuunction](https://fuunction.agency) 