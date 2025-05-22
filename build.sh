#!/bin/bash

# WP Media Delivery Build Script
# This script builds the plugin and outputs to a releases folder

# Ensure we're in the plugin directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Create releases directory if it doesn't exist
mkdir -p releases

# Get version from wp-media-delivery.php
VERSION=$(grep "Version:" wp-media-delivery.php | awk -F: '{print $2}' | sed 's/[^0-9.a-z-]//g')
echo "Building WP Media Delivery version $VERSION"

# Create a clean build directory
echo "Creating temporary build directory..."
BUILD_DIR="build-tmp"
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR/wp-media-delivery"

# Copy all necessary files to the build directory
echo "Copying files to build directory..."
rsync -av --progress ./ "$BUILD_DIR/wp-media-delivery/" \
  --exclude="$BUILD_DIR" \
  --exclude=".git" \
  --exclude=".github" \
  --exclude=".gitignore" \
  --exclude=".DS_Store" \
  --exclude="node_modules" \
  --exclude="releases" \
  --exclude="composer.lock" \
  --exclude="package-lock.json" \
  --exclude="*.zip" \
  --exclude=".phpcs.xml" \
  --exclude=".editorconfig" \
  --exclude="scoper.inc.php"

# Run composer to get dependencies and optimize
echo "Installing dependencies..."
cd "$BUILD_DIR/wp-media-delivery"
composer install --no-dev --optimize-autoloader --prefer-dist

# Create zip file
echo "Creating zip file..."
cd ..
zip -r "../releases/wp-media-delivery-$VERSION.zip" wp-media-delivery

# Clean up
echo "Cleaning up..."
cd ..
rm -rf "$BUILD_DIR"

echo "Build complete! File created: releases/wp-media-delivery-$VERSION.zip" 