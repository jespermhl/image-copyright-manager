<?php
/**
 * Settings functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IMAGCOMA_Settings {
    
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
    }
    
    public function add_settings_page() {
        add_options_page(
            __( 'Image Copyright Manager', 'image-copyright-manager' ),
            __( 'Image Copyright', 'image-copyright-manager' ),
            'manage_options',
            'image-copyright-manager',
            array( $this, 'render_settings_page' )
        );
    }
    
    public function init_settings() {
        register_setting( 'imagcoma_settings', 'imagcoma_settings', array( $this, 'sanitize_settings' ) );
        
        add_settings_section(
            'imagcoma_general_section',
            __( 'General Settings', 'image-copyright-manager' ),
            array( $this, 'render_section_description' ),
            'image-copyright-manager'
        );
        
        add_settings_field(
            'display_text',
            __( 'Display Text Format', 'image-copyright-manager' ),
            array( $this, 'render_display_text_field' ),
            'image-copyright-manager',
            'imagcoma_general_section'
        );
    }
    
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Image Copyright Manager Settings', 'image-copyright-manager' ); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'imagcoma_settings' );
                do_settings_sections( 'image-copyright-manager' );
                submit_button();
                ?>
            </form>
            
            <div class="imagcoma-settings-help">
                <h3><?php esc_html_e( 'Usage Instructions', 'image-copyright-manager' ); ?></h3>
                <p><?php esc_html_e( '1. Go to Media Library and edit any image to add copyright information.', 'image-copyright-manager' ); ?></p>
                <p><?php esc_html_e( '2. Use the [imagcoma] shortcode to display all images with copyright information.', 'image-copyright-manager' ); ?></p>
                <p><?php esc_html_e( '3. Copyright information will automatically display under images when enabled.', 'image-copyright-manager' ); ?></p>
            </div>
        </div>
        <?php
    }
    
    public function render_section_description() {
        echo '<p>' . esc_html__( 'Configure how copyright information is displayed on your website.', 'image-copyright-manager' ) . '</p>';
    }
    
    public function render_display_text_field() {
        $settings = IMAGCOMA_Core::get_settings();
        ?>
        <input 
            type="text" 
            name="imagcoma_settings[display_text]" 
            value="<?php echo esc_attr( $settings['display_text'] ); ?>" 
            class="regular-text" 
        />
        <p class="description">
            <?php esc_html_e( 'Use {copyright} as placeholder for the actual copyright text', 'image-copyright-manager' ); ?>
        </p>
        <?php
    }
    
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        if ( isset( $input['display_text'] ) ) {
            $sanitized['display_text'] = sanitize_text_field( $input['display_text'] );
        }

        return $sanitized;
    }
} 