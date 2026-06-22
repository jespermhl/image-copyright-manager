#!/usr/bin/env bash

# WordPress test suite installer
# Usage: bin/install-wp-tests.sh <db-name> <db-user> <db-pass> <db-host> <wp-version>

DB_NAME=${1:-wordpress_test}
DB_USER=${2:-root}
DB_PASS=${3:-}
DB_HOST=${4:-127.0.0.1}
WP_VERSION=${5:-latest}

TMPDIR=${TMPDIR:-/tmp}
WP_TESTS_DIR="${TMPDIR}/wordpress-tests-lib"
WP_CORE_DIR="${TMPDIR}/wordpress"

set -ex

# Download WordPress
if [ ! -d "$WP_CORE_DIR" ]; then
	mkdir -p "$WP_CORE_DIR"
	if [ "$WP_VERSION" == "latest" ]; then
		ARCHIVE_URL="https://wordpress.org/latest.tar.gz"
	else
		ARCHIVE_URL="https://wordpress.org/wordpress-$WP_VERSION.tar.gz"
	fi
	curl -sL "$ARCHIVE_URL" | tar xz -C "$TMPDIR"
fi

# Set up WordPress config
if [ ! -f "$WP_CORE_DIR/wp-config.php" ]; then
	cp "$WP_CORE_DIR/wp-config-sample.php" "$WP_CORE_DIR/wp-config.php"
	sed -i "s/database_name_here/$DB_NAME/" "$WP_CORE_DIR/wp-config.php"
	sed -i "s/username_here/$DB_USER/" "$WP_CORE_DIR/wp-config.php"
	sed -i "s/password_here/$DB_PASS/" "$WP_CORE_DIR/wp-config.php"
	sed -i "s/localhost/$DB_HOST/" "$WP_CORE_DIR/wp-config.php"
fi

# Download WordPress test suite
if [ ! -d "$WP_TESTS_DIR" ]; then
	mkdir -p "$WP_TESTS_DIR"
	svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn co --quiet "https://develop.svn.wordpress.org/trunk/tests/phpunit/data/" "$WP_TESTS_DIR/data"
fi

# Create test config
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
	curl -sL "https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php" > "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/database_name_here/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/username_here/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/password_here/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/localhost/$DB_HOST/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed -i "s/wp_phpunit_tests/wptests_/" "$WP_TESTS_DIR/wp-tests-config.php"
fi
