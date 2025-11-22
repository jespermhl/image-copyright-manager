#!/bin/bash

# Exit if any command fails
set -e

# Define variables
PLUGIN_SLUG="image-copyright-manager"
SVN_URL="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}"
SVN_DIR="/tmp/${PLUGIN_SLUG}-svn"
BUILD_DIR="/tmp/${PLUGIN_SLUG}-build"
CURRENT_DIR=$(pwd)

# Get version from main plugin file
VERSION=$(grep "Version:" image-copyright-manager.php | awk '{print $3}')

echo "🚀 Starting deployment for version ${VERSION}..."

# 1. Build the plugin
echo "📦 Building plugin..."
pnpm install
pnpm run makepot
pnpm run build

# 2. Prepare SVN directory
echo "🔄 Checking out SVN repo..."
rm -rf "$SVN_DIR"
svn co "$SVN_URL" "$SVN_DIR" --depth immediates
svn update "$SVN_DIR/trunk" --set-depth infinity
svn update "$SVN_DIR/assets" --set-depth infinity

# 3. Sync files to trunk
echo "📂 Syncing files to trunk..."
# Use rsync to sync files, excluding dev files
# We use the .distignore file to know what to exclude, but rsync needs a slightly different format or we can just be explicit here for safety.
# Actually, the best way is to unzip the built zip file into trunk, as that is guaranteed to be clean.

# Create a temp build dir
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"
unzip -q "${PLUGIN_SLUG}.zip" -d "$BUILD_DIR"

# Sync from the unzipped folder to SVN trunk
# --delete ensures files removed from the plugin are removed from SVN
rsync -rc --delete "$BUILD_DIR/${PLUGIN_SLUG}/" "$SVN_DIR/trunk/"

# 4. Commit to Trunk
echo "committing to trunk..."
cd "$SVN_DIR/trunk"
# Add new files
svn stat | grep "^?" | awk '{print $2}' | xargs -I {} svn add {}
# Remove deleted files
svn stat | grep "^!" | awk '{print $2}' | xargs -I {} svn rm {}

svn ci -m "Deploying version ${VERSION} to trunk"

# 5. Tag the version
echo "🏷️ Tagging version ${VERSION}..."
cd "$SVN_DIR"
svn cp trunk "tags/${VERSION}"
svn ci -m "Tagging version ${VERSION}" "tags/${VERSION}"

echo "✅ Deployment complete! Version ${VERSION} is live."
