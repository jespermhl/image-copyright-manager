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

    const VERSION = '1.3.1';
    
    const TEXT_DOMAIN = 'image-copyright-manager';
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initializes core hooks.
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'check_version' ) );
    }

    /**
     * Checks the plugin version and runs migration logic if necessary.
     */
    public function check_version() {
        $installed_version = get_option( 'imagcoma_version' );
        if ( self::VERSION !== $installed_version ) {
            imagcoma_create_copyright_table();
            update_option( 'imagcoma_version', self::VERSION );
        }
    }
    
    /**
     * Initializes internal components.
     */
    public function init() {
        new IMAGCOMA_Meta_Boxes();
        new IMAGCOMA_Shortcodes();
        new IMAGCOMA_Settings();
        new IMAGCOMA_Display();
        new IMAGCOMA_Admin_Columns();
    }
    
    /**
     * Load dependencies (legacy).
     */
    private function load_dependencies() {
    }
    
    public static function get_settings() {
        $settings = get_option( 'imagcoma_settings', array() );
        
        $defaults = array(
            'display_text'   => __( 'Copyright: {copyright}', 'image-copyright-manager' ),
            'enable_css'     => 1,
            'enable_json_ld' => 1
        );
        
        return wp_parse_args( $settings, $defaults );
    }
}