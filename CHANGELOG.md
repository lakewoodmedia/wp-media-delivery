# Changelog

All notable changes to the "WP Media Delivery" plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.13-beta] - 2023-09-18

### Added
- "Fetch Back" functionality to retrieve media from cloud storage to local server
- Automatic cleanup of cloud files after successful fetch operations
- Improved documentation in README.md with details on new media management features

## [1.0.12-beta] - 2023-09-15

### Added
- New offload actions in the media library:
  - Individual action link on each media item to offload to cloud storage
  - Bulk action option to offload multiple selected media files at once
- "Fetch Back" option to retrieve media from cloud storage and remove it from the cloud
- Success/error notifications for offload and fetch operations

## [1.0.1-beta] - 2023-09-07

### Added
- New status column in the Media Library that shows where media is being served from (local server or cloud storage)
- Each cloud provider (S3, R2, etc.) is properly identified in the status column
- Visual indicator with provider-specific status labels
- Added plugin structure section to README.md for better developer onboarding

### Changed
- Plugin renamed from "Advanced Media Offloader" to "WP Media Delivery"
- Updated file structure to maintain consistent naming

### Fixed
- Fixed an autoloading issue with MediaLibraryColumns class that was causing fatal errors on the media library page

## [1.0.0-rc] - 2023-09-01

### Added
- Initial release candidate
- Support for multiple cloud storage providers:
  - Amazon S3
  - Cloudflare R2
  - DigitalOcean Spaces
  - Min.io
  - Wasabi
- Automatic media offloading for new uploads
- Bulk offloading for existing media
- Media status overview page
- Settings page for configuring cloud provider credentials 