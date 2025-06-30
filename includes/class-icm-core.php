<?php
/**
 * Core plugin class
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ICM_Core {

    const VERSION = '1.0.5';
    
    const TEXT_DOMAIN = 'image-copyright-manager';
    
    public function __construct() {
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
    }
    
    public function init() {
        $this->load_dependencies();
        
        new ICM_Meta_Boxes();
        new ICM_Shortcodes();
        new ICM_Settings();
        new ICM_Display();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname( plugin_basename( ICM_PLUGIN_FILE ) ) . '/languages'
        );
    }
    
    private function load_dependencies() {
        require_once ICM_PLUGIN_DIR . 'includes/class-icm-meta-boxes.php';
        require_once ICM_PLUGIN_DIR . 'includes/class-icm-shortcodes.php';
        require_once ICM_PLUGIN_DIR . 'includes/class-icm-settings.php';
        require_once ICM_PLUGIN_DIR . 'includes/class-icm-display.php';
        require_once ICM_PLUGIN_DIR . 'includes/class-icm-utils.php';
    }
    
    public static function get_settings() {
        $settings = get_option( 'icm_settings', array() );
        
        $defaults = array(
            'display_text' => __( 'Copyright: {copyright}', self::TEXT_DOMAIN ),
            'css_class' => 'icm-copyright-text'
        );
        
        return wp_parse_args( $settings, $defaults );
    }
} 