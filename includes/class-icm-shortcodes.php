<?php
/**
 * Shortcodes functionality
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handle shortcodes for displaying copyright information
 */
class ICM_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public function __construct() {
        add_shortcode( 'icm', array( $this, 'render_copyright_list' ) );
    }
    
    /**
     * Render the copyright list shortcode
     */
    public function render_copyright_list( $atts ) {
        $atts = shortcode_atts( array(
            'orderby' => 'date',
            'order' => 'DESC',
            'heading' => __( 'Image Sources', ICM_Core::TEXT_DOMAIN ),
            'heading_tag' => 'h3',
            'no_sources_text' => __( 'No image sources with copyright information found.', ICM_Core::TEXT_DOMAIN ),
            'copyright_label' => __( 'Copyright:', ICM_Core::TEXT_DOMAIN ),
            'view_media_text' => __( 'View Media', ICM_Core::TEXT_DOMAIN )
        ), $atts );
        
        $args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
            'meta_query' => array(
                array(
                    'key' => '_icm_copyright',
                    'value' => '',
                    'compare' => '!=',
                    'type' => 'CHAR'
                )
            ),
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false
        );
        
        $query = new WP_Query( $args );
        
        if ( ! $query->have_posts() ) {
            return '<p>' . esc_html( $atts['no_sources_text'] ) . '</p>';
        }
        
        $output = '<div class="icm-media-list">';
        $output .= '<' . esc_attr( $atts['heading_tag'] ) . '>' . esc_html( $atts['heading'] ) . '</' . esc_attr( $atts['heading_tag'] ) . '>';
        $output .= '<ul>';
        
        while ( $query->have_posts() ) {
            $query->the_post();
            $attachment_id = get_the_ID();
            $copyright = get_post_meta( $attachment_id, '_icm_copyright', true );
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
        
        wp_reset_postdata();
        
        return $output;
    }
} 