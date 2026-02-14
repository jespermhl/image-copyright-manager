<?php
/**
 * Plugin Name:         Image Copyright Manager
 * Plugin URI:          https://mahelwebdesign.com/image-copyright-manager/
 * Description:         Adds a custom field for copyright information to WordPress media.
 * Version:             1.3.1
 * Requires at least:   6.4
 * Requires PHP:        7.4
 * Author:              Mahel Webdesign
 * Author URI:          https://mahelwebdesign.com/
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         image-copyright-manager
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'IMAGCOMA_PLUGIN_FILE' ) ) {
    define( 'IMAGCOMA_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'IMAGCOMA_PLUGIN_DIR' ) ) {
    define( 'IMAGCOMA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'IMAGCOMA_PLUGIN_URL' ) ) {
    define( 'IMAGCOMA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Load dependencies with safety checks
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-utils.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-core.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-meta-boxes.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-shortcodes.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-settings.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-display.php';
require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-admin-columns.php';

if ( ! function_exists( 'imagcoma_init' ) ) {
    function imagcoma_init() {
        global $imagcoma_core;
        if ( ! isset( $imagcoma_core ) && class_exists( 'IMAGCOMA_Core' ) ) {
            $imagcoma_core = new IMAGCOMA_Core();
        }
    }
    add_action( 'plugins_loaded', 'imagcoma_init' );
}

if ( ! function_exists( 'imagcoma_activate' ) ) {
    register_activation_hook( __FILE__, 'imagcoma_activate' );
    function imagcoma_activate() {
        // Set default settings
        $default_settings = array(
            'display_text'   => __( 'Copyright: {copyright}', 'image-copyright-manager' ),
            'enable_css'     => 1,
            'enable_json_ld' => 1
        );
        add_option( 'imagcoma_settings', $default_settings );

        // Initial table creation
        if ( function_exists( 'imagcoma_create_copyright_table' ) ) {
            imagcoma_create_copyright_table();
        }
        
        // Set version
        update_option( 'imagcoma_version', '1.3.1' );
    }
}

if ( ! function_exists( 'imagcoma_create_copyright_table' ) ) {
    function imagcoma_create_copyright_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'imagcoma_copyright';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
      id bigint(20) unsigned NOT NULL auto_increment,
      attachment_id bigint(20) unsigned NOT NULL,
      copyright_text text DEFAULT '',
      creator text DEFAULT '',
      copyright_notice text DEFAULT '',
      credit_text text DEFAULT '',
      license_url text DEFAULT '',
      acquire_license_url text DEFAULT '',
      PRIMARY KEY  (id),
      UNIQUE KEY attachment_id (attachment_id)
    ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}

if ( ! function_exists( 'imagcoma_deactivate' ) ) {
    function imagcoma_deactivate() {
    }
    register_deactivation_hook( __FILE__, 'imagcoma_deactivate' );
} 