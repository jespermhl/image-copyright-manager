<?php
/**
 * Settings functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Settings functionality.
 */
class IMAGCOMA_Settings {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
    }
    
    /**
     * Adds the settings page to the Media menu.
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Image Copyright Manager', 'image-copyright-manager' ),
            __( 'Image Copyright', 'image-copyright-manager' ),
            'manage_options',
            'image-copyright-manager',
            array( $this, 'render_settings_page' )
        );
    }
    
    /**
     * Registers plugin settings and fields.
     */
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
        
        add_settings_field(
            'enable_css',
            __( 'Enable CSS Styling', 'image-copyright-manager' ),
            array( $this, 'render_enable_css_field' ),
            'image-copyright-manager',
            'imagcoma_general_section'
        );

        add_settings_field(
            'enable_json_ld',
            __( 'Enable JSON-LD SEO', 'image-copyright-manager' ),
            array( $this, 'render_enable_json_ld_field' ),
            'image-copyright-manager',
            'imagcoma_general_section'
        );

        add_settings_field(
            'enable_auto_extract',
            __( 'Auto-Extract Metadata', 'image-copyright-manager' ),
            array( $this, 'render_enable_auto_extract_field' ),
            'image-copyright-manager',
            'imagcoma_general_section'
        );
    }
    
    /**
     * Renders the settings page HTML.
     */
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
    
    /**
     * Renders the description for the general settings section.
     */
    public function render_section_description() {
        echo '<p>' . esc_html__( 'Configure how copyright information is displayed on your website.', 'image-copyright-manager' ) . '</p>';
    }
    
    /**
     * Renders the display text format input field.
     */
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
    
    /**
     * Renders the enable CSS styling checkbox field.
     */
    public function render_enable_css_field() {
        $settings = IMAGCOMA_Core::get_settings();
        ?>
        <label for="imagcoma_settings[enable_css]">
            <input 
                type="checkbox" 
                name="imagcoma_settings[enable_css]" 
                id="imagcoma_settings[enable_css]"
                value="1" 
                <?php checked( $settings['enable_css'], 1 ); ?>
            />
            <?php esc_html_e( 'Enable CSS styling for copyright information.', 'image-copyright-manager' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When disabled, copyright information will be displayed without custom styling.', 'image-copyright-manager' ); ?>
        </p>
        <?php
    }

    /**
     * Renders the enable JSON-LD SEO checkbox field.
     */
    public function render_enable_json_ld_field() {
        $settings = IMAGCOMA_Core::get_settings();
        ?>
        <label for="imagcoma_settings[enable_json_ld]">
            <input 
                type="checkbox" 
                name="imagcoma_settings[enable_json_ld]" 
                id="imagcoma_settings[enable_json_ld]"
                value="1" 
                <?php checked( $settings['enable_json_ld'], 1 ); ?>
            />
            <?php esc_html_e( 'Enable JSON-LD Structured Data for Image SEO.', 'image-copyright-manager' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When enabled, the plugin will output Schema.org ImageObject JSON-LD to help Google identify images and show licensing badges.', 'image-copyright-manager' ); ?>
        </p>
        <?php
    }

    /**
     * Renders the checkbox field for the "enable_auto_extract" setting.
     *
     * Displays a checkbox that toggles automatic extraction of copyright metadata
     * from EXIF, IPTC, and XMP on image upload. When enabled, the plugin will read
     * copyright data produced by tools like Lightroom; existing manual metadata
     * entries will not be overwritten. */
    public function render_enable_auto_extract_field() {
        $settings = IMAGCOMA_Core::get_settings();
        ?>
        <label for="imagcoma_settings[enable_auto_extract]">
            <input 
                type="checkbox" 
                name="imagcoma_settings[enable_auto_extract]" 
                id="imagcoma_settings[enable_auto_extract]"
                value="1" 
                <?php checked( $settings['enable_auto_extract'], 1 ); ?>
            />
            <?php esc_html_e( 'Automatically extract copyright information from EXIF/IPTC/XMP metadata on upload.', 'image-copyright-manager' ); ?>
        </label>
        <p class="description">
            <?php esc_html_e( 'When enabled, the plugin will automatically read copyright data from Lightroom and other photo editing software. Existing manual entries will not be overwritten.', 'image-copyright-manager' ); ?>
        </p>
        <?php
    }
    
    /**
     * Sanitizes and normalizes plugin settings prior to persistence.
     *
     * Sanitizes the display text and converts checkbox-like options to explicit integers.
     *
     * @param array $input Raw settings array from the settings form.
     * @return array Sanitized settings with keys:
     *               - 'display_text' (string) if provided,
     *               - 'enable_css' (int) 1 or 0,
     *               - 'enable_json_ld' (int) 1 or 0,
     *               - 'enable_auto_extract' (int) 1 or 0.
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();
        
        if ( isset( $input['display_text'] ) ) {
            $sanitized['display_text'] = sanitize_text_field( $input['display_text'] );
        }

        if ( isset( $input['enable_css'] ) ) {
            $sanitized['enable_css'] = 1;
        } else {
            $sanitized['enable_css'] = 0;
        }

        if ( isset( $input['enable_json_ld'] ) ) {
            $sanitized['enable_json_ld'] = 1;
        } else {
            $sanitized['enable_json_ld'] = 0;
        }

        if ( isset( $input['enable_auto_extract'] ) ) {
            $sanitized['enable_auto_extract'] = 1;
        } else {
            $sanitized['enable_auto_extract'] = 0;
        }

        return $sanitized;
    }
} 