<?php
/**
 * Plugin Name:         Image Copyright Manager
 * Plugin URI:          https://mahelwebdesign.com/image-copyright-manager/
 * Description:         Adds a custom field for copyright information to WordPress media.
 * Version:             1.1.3
 * Requires at least:   6.4
 * Requires PHP:        7.4
 * Author:              Mahel Webdesign
 * Author URI:          https://mahelwebdesign.com/
 * Contributors:        jespermhl
 * License:             GPL2
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:         image-copyright-manager
 * Domain Path:         /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'IMAGCOMA_PLUGIN_FILE', __FILE__ );
define( 'IMAGCOMA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMAGCOMA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-core.php';

function imagcoma_init() {
    global $imagcoma_core;
    $imagcoma_core = new IMAGCOMA_Core();
}

add_action( 'plugins_loaded', 'imagcoma_init' );

function imagcoma_activate() {
    $default_settings = array(
        'display_text' => __( 'Copyright: {copyright}', 'image-copyright-manager' )
    );
    
    add_option( 'imagcoma_settings', $default_settings );
}
register_activation_hook( __FILE__, 'imagcoma_activate' );

register_activation_hook( __FILE__, 'imagcoma_create_copyright_table' );
function imagcoma_create_copyright_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'imagcoma_copyright';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        attachment_id BIGINT(20) UNSIGNED NOT NULL,
        copyright_text TEXT NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY attachment_id (attachment_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function imagcoma_deactivate() {
}

register_deactivation_hook( __FILE__, 'imagcoma_deactivate' ); 