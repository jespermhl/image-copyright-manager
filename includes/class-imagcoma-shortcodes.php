<?php
/**
 * Shortcodes functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class IMAGCOMA_Shortcodes {
    
    /**
     * Constructor.
     */
    public function __construct() {
        add_shortcode( 'imagcoma', array( $this, 'render_shortcode' ) );
    }
    
    /**
     * Renders the [imagcoma] shortcode showing all images with copyright information.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'orderby' => 'date',
            'order' => 'DESC',
            'heading' => __( 'Image Sources', 'image-copyright-manager' ),
            'heading_tag' => 'h3',
            'no_sources_text' => __( 'No images with copyright information found.', 'image-copyright-manager' ),
            'copyright_label' => __( 'Copyright:', 'image-copyright-manager' ),
            'view_media_text' => __( 'View Media', 'image-copyright-manager' )
        ), $atts );
        
        $attachments = IMAGCOMA_Utils::get_attachments_with_copyright();
        if ( empty( $attachments ) ) {
            return '<p>' . esc_html( $atts['no_sources_text'] ) . '</p>';
        }
        
        // Validate heading_tag
        $allowed_headings = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
        if ( ! in_array( strtolower( $atts['heading_tag'] ), $allowed_headings ) ) {
            $atts['heading_tag'] = 'h3'; // Fallback to default
        }
        
        $output = '<div class="imagcoma-media-list">';
        $output .= '<' . esc_attr( $atts['heading_tag'] ) . '>' . esc_html( $atts['heading'] ) . '</' . esc_attr( $atts['heading_tag'] ) . '>';
        $output .= '<ul>';
        
        foreach ( $attachments as $row ) {
            $attachment_id = $row->attachment_id;
            IMAGCOMA_Display::add_to_json_ld( $attachment_id );
            $copyright = $row->copyright_text;
            $attachment_url = wp_get_attachment_url( $attachment_id );
            $attachment_title = get_the_title( $attachment_id );
            
            $output .= '<li>';
            $output .= '<strong>' . esc_html( $attachment_title ) . '</strong><br>';
            $output .= '<em>' . esc_html( $atts['copyright_label'] ) . ' ' . wp_kses_post( $copyright ) . '</em><br>';
            
            if ( $attachment_url ) {
                $output .= '<a href="' . esc_url( $attachment_url ) . '" target="_blank">' . esc_html( $atts['view_media_text'] ) . '</a>';
            }
            
            $output .= '</li>';
        }
        
        $output .= '</ul>';
        $output .= '</div>';
        
        return $output;
    }
} 