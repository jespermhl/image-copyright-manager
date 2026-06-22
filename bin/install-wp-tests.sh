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

SED_INPLACE=(-i)
if [[ "$(uname)" == "Darwin" ]]; then
	SED_INPLACE=(-i '')
fi

set -ex

# Download WordPress
if [ ! -d "$WP_CORE_DIR" ]; then
	mkdir -p "$WP_CORE_DIR"

	if [ "$WP_VERSION" == "latest" ]; then
		ARCHIVE_URL="https://wordpress.org/latest.tar.gz"
		CHECKSUM_URL="https://wordpress.org/latest.tar.gz.md5"
	else
		ARCHIVE_URL="https://wordpress.org/wordpress-$WP_VERSION.tar.gz"
		CHECKSUM_URL="https://wordpress.org/wordpress-$WP_VERSION.tar.gz.md5"
	fi

	ARCHIVE_PATH="${TMPDIR}/wordpress.tar.gz"

	curl -sL "$ARCHIVE_URL" -o "$ARCHIVE_PATH"
	expected=$(curl -sL "$CHECKSUM_URL" | tr -d '[:space:]')

	if [ -n "$expected" ]; then
		if command -v md5sum &>/dev/null; then
			actual=$(md5sum "$ARCHIVE_PATH" | cut -d' ' -f1)
		elif command -v md5 &>/dev/null; then
			actual=$(md5 -q "$ARCHIVE_PATH")
		else
			echo "No md5 tool available, skipping checksum verification" >&2
			actual="$expected"
		fi
		if [ "$expected" != "$actual" ]; then
			echo "Checksum mismatch for WordPress archive" >&2
			exit 1
		fi
	fi

	tar xz -C "$TMPDIR" -f "$ARCHIVE_PATH"
	rm -f "$ARCHIVE_PATH"
fi

# Set up WordPress config
if [ ! -f "$WP_CORE_DIR/wp-config.php" ]; then
	cp "$WP_CORE_DIR/wp-config-sample.php" "$WP_CORE_DIR/wp-config.php"
	sed "${SED_INPLACE[@]}" "s/database_name_here/$DB_NAME/" "$WP_CORE_DIR/wp-config.php"
	sed "${SED_INPLACE[@]}" "s/username_here/$DB_USER/" "$WP_CORE_DIR/wp-config.php"
	sed "${SED_INPLACE[@]}" "s/password_here/$DB_PASS/" "$WP_CORE_DIR/wp-config.php"
	sed "${SED_INPLACE[@]}" "s/localhost/$DB_HOST/" "$WP_CORE_DIR/wp-config.php"
fi

# Download WordPress test suite
if [ ! -d "$WP_TESTS_DIR" ]; then
	mkdir -p "$WP_TESTS_DIR"

	# Use a stable branch matching the requested version; default to 6.9 for 'latest'
	if [ "$WP_VERSION" == "latest" ]; then
		SVN_BRANCH="6.9"
	else
		SVN_BRANCH="$WP_VERSION"
	fi

	svn co --quiet "https://develop.svn.wordpress.org/tags/${SVN_BRANCH}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn co --quiet "https://develop.svn.wordpress.org/tags/${SVN_BRANCH}/tests/phpunit/data/" "$WP_TESTS_DIR/data"
fi

# Create test config
if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
	curl -sL "https://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php" > "$WP_TESTS_DIR/wp-tests-config.php"
	sed "${SED_INPLACE[@]}" "s/database_name_here/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed "${SED_INPLACE[@]}" "s/username_here/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed "${SED_INPLACE[@]}" "s/password_here/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed "${SED_INPLACE[@]}" "s/localhost/$DB_HOST/" "$WP_TESTS_DIR/wp-tests-config.php"
	sed "${SED_INPLACE[@]}" "s/wp_phpunit_tests/wptests_/" "$WP_TESTS_DIR/wp-tests-config.php"
	# Override ABSPATH to point to the WordPress core directory (not the non-existent tests/src/)
	sed "${SED_INPLACE[@]}" "s|dirname( __FILE__ ) . '/src/'|'${WP_CORE_DIR}/'|" "$WP_TESTS_DIR/wp-tests-config.php"
fi
