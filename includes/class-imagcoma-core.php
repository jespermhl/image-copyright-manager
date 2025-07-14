<?php
/**
 * Core plugin class
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IMAGCOMA_Core {

    const VERSION = '1.1.3';
    
    const TEXT_DOMAIN = 'image-copyright-manager';
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );
    }
    
    public function init() {
        $this->load_dependencies();
        
        new IMAGCOMA_Meta_Boxes();
        new IMAGCOMA_Shortcodes();
        new IMAGCOMA_Settings();
        new IMAGCOMA_Display();
    }
    
    private function load_dependencies() {
        require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-meta-boxes.php';
        require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-shortcodes.php';
        require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-settings.php';
        require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-display.php';
        require_once IMAGCOMA_PLUGIN_DIR . 'includes/class-imagcoma-utils.php';
    }
    
    public static function get_settings() {
        $settings = get_option( 'imagcoma_settings', array() );
        
        $defaults = array(
            'display_text' => __( 'Copyright: {copyright}', 'image-copyright-manager' ),
            'enable_css' => 1
        );
        
        return wp_parse_args( $settings, $defaults );
    }
}