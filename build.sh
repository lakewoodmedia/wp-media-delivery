#!/bin/bash

# WP Media Delivery Build Script

# Exit if any command fails
set -e

# Configuration
PLUGIN_SLUG="wp-media-delivery"
VERSION=$(grep "Version:" wp-media-delivery.php | awk -F' ' '{print $3}' | tr -d '\r')
RELEASE_DIR="./releases"
BUILD_DIR="$RELEASE_DIR/build"
PACKAGE_NAME="$PLUGIN_SLUG-$VERSION"
ZIP_FILE="$RELEASE_DIR/$PACKAGE_NAME.zip"

# Ensure the release directory exists
mkdir -p "$RELEASE_DIR"
mkdir -p "$BUILD_DIR"

echo "Building $PLUGIN_SLUG version $VERSION..."

# Clean up any previous build
rm -rf "$BUILD_DIR"/*

# Create necessary directories
mkdir -p "$BUILD_DIR/$PLUGIN_SLUG"

# Copy files to the build directory
echo "Copying files..."
cp -R assets includes templates vendor utility-functions.php wp-media-delivery.php README.md "$BUILD_DIR/$PLUGIN_SLUG/"

# Remove any development or unnecessary files
echo "Removing development files..."
find "$BUILD_DIR/$PLUGIN_SLUG" -name ".git*" -print0 | xargs -0 rm -rf
find "$BUILD_DIR/$PLUGIN_SLUG" -name "node_modules" -print0 | xargs -0 rm -rf
find "$BUILD_DIR/$PLUGIN_SLUG" -name ".DS_Store" -print0 | xargs -0 rm -rf
find "$BUILD_DIR/$PLUGIN_SLUG" -name "*.log" -print0 | xargs -0 rm -rf

# Create the zip file
echo "Creating zip file..."
cd "$BUILD_DIR"
zip -r "../$PACKAGE_NAME.zip" "$PLUGIN_SLUG"
cd ../..

# Clean up
echo "Cleaning up..."
rm -rf "$BUILD_DIR"

echo "Build complete!"
echo "Package created: $ZIP_FILE"

# Make the file executable
chmod +x build.sh 