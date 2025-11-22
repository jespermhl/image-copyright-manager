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
        if ( empty( $content ) ) {
            return $content;
        }

        // Suppress warnings for invalid HTML
        $internal_errors = libxml_use_internal_errors( true );
        
        $dom = new DOMDocument();
        // Hack to load HTML with UTF-8 encoding
        $dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        
        $images = $dom->getElementsByTagName( 'img' );
        
        if ( $images->length === 0 ) {
            libxml_use_internal_errors( $internal_errors );
            return $content;
        }
        
        $settings = IMAGCOMA_Core::get_settings();
        $modified = false;
        
        // Loop backwards to avoid issues with DOM modification
        for ( $i = $images->length - 1; $i >= 0; $i-- ) {
            $img = $images->item( $i );
            
            // Get the outer HTML of the image to extract attachment ID
            $img_html = $dom->saveHTML( $img );
            
            $attachment_id = IMAGCOMA_Utils::get_attachment_id_from_img_tag( $img_html );
            
            if ( ! $attachment_id ) {
                continue;
            }
            
            $copyright_data = IMAGCOMA_Utils::get_copyright_info( $attachment_id );
            $display_copyright = $copyright_data['display_copyright'] ?? false;
            
            if ( ! $display_copyright ) {
                continue;
            }
            
            $copyright = $copyright_data['copyright'] ?? '';
            
            if ( empty( $copyright ) ) {
                continue;
            }
            
            $copyright_text = str_replace( '{copyright}', $copyright, $settings['display_text'] );
            
            // Create copyright element
            $copyright_div = $dom->createElement( 'div' );
            $copyright_div->setAttribute( 'class', 'imagcoma-copyright-text' );
            
            // We need to handle HTML in copyright text safely
            $fragment = $dom->createDocumentFragment();
            $fragment->appendXML( wp_kses_post( $copyright_text ) );
            $copyright_div->appendChild( $fragment );
            
            // Insert after image
            if ( $img->nextSibling ) {
                $img->parentNode->insertBefore( $copyright_div, $img->nextSibling );
            } else {
                $img->parentNode->appendChild( $copyright_div );
            }
            
            $modified = true;
        }
        
        if ( $modified ) {
            $content = $dom->saveHTML();
        }
        
        libxml_use_internal_errors( $internal_errors );
        
        return $content;
    }
    
    public function enqueue_styles() {
        $settings = IMAGCOMA_Core::get_settings();
        
        if ( $settings['enable_css'] ) {
            wp_enqueue_style( 'imagcoma-copyright-styles', IMAGCOMA_PLUGIN_URL . 'includes/css/copyright-styles.css', array(), IMAGCOMA_Core::VERSION );
        }
    }
} 