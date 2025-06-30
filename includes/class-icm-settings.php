<?php
/**
 * Settings functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ICM_Settings {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
    }
    
    public function add_settings_page() {
        add_options_page(
            __( 'Image Copyright Manager', ICM_Core::TEXT_DOMAIN ),
            __( 'Image Copyright', ICM_Core::TEXT_DOMAIN ),
            'manage_options',
            'image-copyright-manager',
            array( $this, 'render_settings_page' )
        );
    }
    
    public function init_settings() {
        register_setting( 'icm_settings', 'icm_settings', array( $this, 'sanitize_settings' ) );
        
        add_settings_section(
            'icm_general_section',
            __( 'General Settings', ICM_Core::TEXT_DOMAIN ),
            array( $this, 'render_section_description' ),
            'image-copyright-manager'
        );
        
        add_settings_field(
            'display_text',
            __( 'Display Text Format', ICM_Core::TEXT_DOMAIN ),
            array( $this, 'render_display_text_field' ),
            'image-copyright-manager',
            'icm_general_section'
        );
        
        add_settings_field(
            'css_class',
            __( 'CSS Class', ICM_Core::TEXT_DOMAIN ),
            array( $this, 'render_css_class_field' ),
            'image-copyright-manager',
            'icm_general_section'
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Image Copyright Manager Settings', ICM_Core::TEXT_DOMAIN ); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'icm_settings' );
                do_settings_sections( 'image-copyright-manager' );
                submit_button();
                ?>
            </form>
            
            <div class="icm-settings-help">
                <h3><?php esc_html_e( 'Usage Instructions', ICM_Core::TEXT_DOMAIN ); ?></h3>
                <p><?php esc_html_e( '1. Go to Media Library and edit any image to add copyright information.', ICM_Core::TEXT_DOMAIN ); ?></p>
                <p><?php esc_html_e( '2. Use the [icm] shortcode to display all copyrighted images.', ICM_Core::TEXT_DOMAIN ); ?></p>
                <p><?php esc_html_e( '3. Copyright information will automatically display under images when enabled.', ICM_Core::TEXT_DOMAIN ); ?></p>
            </div>
        </div>
        <?php
    }
    
    public function render_section_description() {
        echo '<p>' . esc_html__( 'Configure how copyright information is displayed on your website.', ICM_Core::TEXT_DOMAIN ) . '</p>';
    }
    
    public function render_display_text_field() {
        $settings = ICM_Core::get_settings();
        ?>
        <input 
            type="text" 
            name="icm_settings[display_text]" 
            value="<?php echo esc_attr( $settings['display_text'] ); ?>" 
            class="regular-text" 
        />
        <p class="description">
            <?php esc_html_e( 'Use {copyright} as placeholder for the actual copyright text', ICM_Core::TEXT_DOMAIN ); ?>
        </p>
        <?php
    }
    
    public function render_css_class_field() {
        $settings = ICM_Core::get_settings();
        ?>
        <input 
            type="text" 
            name="icm_settings[css_class]" 
            value="<?php echo esc_attr( $settings['css_class'] ); ?>" 
            class="regular-text" 
        />
        <p class="description">
            <?php esc_html_e( 'CSS class for styling the copyright text', ICM_Core::TEXT_DOMAIN ); ?>
        </p>
        <?php
    }
    
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        if ( isset( $input['display_text'] ) ) {
            $sanitized['display_text'] = sanitize_text_field( $input['display_text'] );
        }
        
        if ( isset( $input['css_class'] ) ) {
            $sanitized['css_class'] = sanitize_html_class( $input['css_class'] );
        }
        
        return $sanitized;
    }
} 