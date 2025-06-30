<?php
/**
 * Plugin Name:         Image Copyright Manager
 * Plugin URI:          https://mahelwebdesign.com/image-copyright-manager/
 * Description:         Adds a custom field for copyright information to WordPress media.
 * Version:             1.0.5
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

define( 'ICM_PLUGIN_FILE', __FILE__ );
define( 'ICM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ICM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once ICM_PLUGIN_DIR . 'includes/class-icm-core.php';

function icm_init() {
    global $icm_core;
    $icm_core = new ICM_Core();
}

add_action( 'plugins_loaded', 'icm_init' );

function icm_activate() {
    $default_settings = array(
        'display_text' => __( 'Copyright: {copyright}', 'image-copyright-manager' ),
        'css_class' => 'icm-copyright-text'
    );
    
    add_option( 'icm_settings', $default_settings );
}
register_activation_hook( __FILE__, 'icm_activate' );

function icm_deactivate() {
}
register_deactivation_hook( __FILE__, 'icm_deactivate' ); 