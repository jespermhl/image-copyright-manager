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
    
    public static $processed_attachments = array();

    public static function add_to_json_ld( $attachment_id ) {
        if ( ! in_array( $attachment_id, self::$processed_attachments ) ) {
            self::$processed_attachments[] = $attachment_id;
        }
    }
    
    public function __construct() {
        add_filter( 'the_content', array( $this, 'auto_display_copyright' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_footer', array( $this, 'output_json_ld' ) );
        add_action( 'wp_head', array( $this, 'output_single_attachment_json_ld' ) );
        add_filter( 'wp_get_attachment_image_attributes', array( $this, 'collect_rendered_image_id' ), 10, 2 );
    }

    public function collect_rendered_image_id( $attr, $attachment ) {
        if ( isset( $attachment->ID ) ) {
            self::add_to_json_ld( $attachment->ID );
        }
        return $attr;
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
            
            // Collect for JSON-LD
            self::add_to_json_ld( $attachment_id );
            
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
            $safe_html = wp_kses_post( $copyright_text );
            $temp_dom = new DOMDocument();
            @$temp_dom->loadHTML( mb_convert_encoding( '<div>' . $safe_html . '</div>', 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
            $container = $temp_dom->getElementsByTagName( 'div' )->item( 0 );
            if ( $container ) {
                foreach ( $container->childNodes as $node ) {
                    $imported_node = $dom->importNode( $node, true );
                    $copyright_div->appendChild( $imported_node );
                }
            }
            
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

    public function output_json_ld() {
        $settings = IMAGCOMA_Core::get_settings();
        if ( empty( $settings['enable_json_ld'] ) ) {
            return;
        }

        if ( empty( self::$processed_attachments ) ) {
            return;
        }

        $attachments = array_unique( self::$processed_attachments );
        $json_data = array();

        foreach ( $attachments as $attachment_id ) {
            $data = $this->get_image_schema_data( $attachment_id );
            if ( $data ) {
                $json_data[] = $data;
            }
        }

        if ( ! empty( $json_data ) ) {
            echo "\n<!-- Image Copyright Manager: JSON-LD start -->\n";
            echo '<script type="application/ld+json">' . "\n";
            echo json_encode( $json_data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
            echo "\n" . '</script>' . "\n";
            echo "<!-- Image Copyright Manager: JSON-LD end -->\n";
        }
    }

    public function output_single_attachment_json_ld() {
        $settings = IMAGCOMA_Core::get_settings();
        if ( empty( $settings['enable_json_ld'] ) ) {
            return;
        }

        if ( is_attachment() ) {
            $attachment_id = get_the_ID();
            $data = $this->get_image_schema_data( $attachment_id );
            
            if ( $data ) {
                echo "\n" . '<script type="application/ld+json">' . "\n";
                echo json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
                echo "\n" . '</script>' . "\n";
            }
        }
    }

    private function get_image_schema_data( $attachment_id ) {
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $attachment_id );
        
        $creator = $copyright_data['creator'] ?? '';
        $notice = ! empty( $copyright_data['copyright_notice'] ) ? $copyright_data['copyright_notice'] : wp_strip_all_tags( $copyright_data['copyright'] );
        $credit = $copyright_data['credit_text'] ?? '';
        $license = $copyright_data['license_url'] ?? '';
        $acquire = $copyright_data['acquire_license_url'] ?? '';

        // Basic requirement for JSON-LD output
        if ( empty( $creator ) && empty( $notice ) && empty( $credit ) && empty( $license ) ) {
            return false;
        }

        $img_url = wp_get_attachment_url( $attachment_id );
        
        $image_schema = array(
            '@context'   => 'https://schema.org',
            '@type'      => 'ImageObject',
            'contentUrl' => $img_url,
        );

        if ( ! empty( $creator ) ) {
            $image_schema['creator'] = array(
                '@type' => 'Person',
                'name'  => $creator
            );
        }

        if ( ! empty( $notice ) ) {
            $image_schema['copyrightNotice'] = $notice;
        }

        if ( ! empty( $credit ) ) {
            $image_schema['creditText'] = $credit;
        }

        if ( ! empty( $license ) ) {
            $image_schema['license'] = $license;
        }

        if ( ! empty( $acquire ) ) {
            $image_schema['acquireLicensePage'] = $acquire;
        }

        return $image_schema;
    }
} 