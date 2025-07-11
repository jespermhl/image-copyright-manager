<?php
/**
 * Display functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


class IMAGCOMA_Display {
    
    public function __construct() {
        add_filter( 'the_content', array( $this, 'auto_display_copyright' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }
    
    public function auto_display_copyright( $content ) {
        $settings = IMAGCOMA_Core::get_settings();
        
        $pattern = '/<img[^>]+>/i';
        $content = preg_replace_callback( $pattern, function( $matches ) use ( $settings ) {
            $img_tag = $matches[0];
            
            $attachment_id = IMAGCOMA_Utils::get_attachment_id_from_img_tag( $img_tag );
            
            if ( ! $attachment_id ) {
                return $img_tag;
            }
            
            $display_copyright = get_post_meta( $attachment_id, '_imagcoma_display_copyright', true );
            if ( $display_copyright !== '1' ) {
                return $img_tag;
            }
            
            $copyright = get_post_meta( $attachment_id, '_imagcoma_copyright', true );
            
            if ( empty( $copyright ) ) {
                return $img_tag;
            }
            
            $copyright_text = str_replace( '{copyright}', $copyright, $settings['display_text'] );
            $copyright_html = '<div class="' . esc_attr( $settings['css_class'] ) . '">' . wp_kses_post( $copyright_text ) . '</div>';
            
            return $img_tag . $copyright_html;
        }, $content );
        
        return $content;
    }
    
    public function enqueue_styles() {
        wp_enqueue_style( 'imagcoma-copyright-styles', IMAGCOMA_PLUGIN_URL . 'includes/css/copyright-styles.css', array(), IMAGCOMA_Core::VERSION );
    }
} 