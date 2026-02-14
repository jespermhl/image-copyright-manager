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
    
    /**
     * Track which attachment IDs have already been emitted in JSON-LD.
     *
     * @var array
     */
    private static $emitted_ids = array();

    /**
     * Adds an attachment ID to the list of processed images for JSON-LD.
     *
     * @param int $attachment_id The attachment ID.
     */
    public static function add_to_json_ld( $attachment_id ) {
        if ( ! in_array( $attachment_id, self::$processed_attachments ) ) {
            self::$processed_attachments[] = $attachment_id;
        }
    }
    
    /**
     * Constructor.
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initializes hooks for display functionality.
     */
    private function init_hooks() {
        add_filter( 'the_content', array( $this, 'auto_display_copyright' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_filter( 'wp_get_attachment_image_attributes', array( $this, 'collect_rendered_image_id' ), 10, 2 );
        add_action( 'wp_footer', array( $this, 'output_json_ld' ), 20 );
        add_action( 'wp_head', array( $this, 'output_single_attachment_json_ld' ) );
    }
    
    /**
     * Collects attachment ID from rendered images.
     *
     * @param array $attr Image attributes.
     * @param WP_Post|int $attachment Attachment post object or ID.
     * @return array Modified image attributes.
     */
    public function collect_rendered_image_id( $attr, $attachment ) {
        if ( is_object( $attachment ) && isset( $attachment->ID ) ) {
            self::add_to_json_ld( $attachment->ID );
        } elseif ( is_numeric( $attachment ) ) {
            self::add_to_json_ld( $attachment );
        }
        return $attr;
    }
    
    /**
     * Automatically appends copyright information to images in the content.
     *
     * @param string $content The post content.
     * @return string Modified content with copyright information.
     */
    public function auto_display_copyright( $content ) {
        if ( empty( $content ) ) {
            return $content;
        }

        // Suppress warnings for invalid HTML
        $internal_errors = libxml_use_internal_errors( true );
        
        $dom = new DOMDocument();
        // Hack to load HTML with UTF-8 encoding without using deprecated mb_convert_encoding
        $dom->loadHTML( '<?xml encoding="UTF-8">' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
        
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
            @$temp_dom->loadHTML( '<?xml encoding="UTF-8"><div>' . $safe_html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
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
    
    /**
     * Enqueues the necessary CSS styles for displaying copyright information.
     */
    public function enqueue_styles() {
        $settings = IMAGCOMA_Core::get_settings();
        
        if ( $settings['enable_css'] ) {
            wp_enqueue_style( 'imagcoma-copyright-styles', IMAGCOMA_PLUGIN_URL . 'includes/css/copyright-styles.css', array(), IMAGCOMA_Core::VERSION );
        }
    }

    /**
     * Outputs JSON-LD structured data for collected images in the footer.
     */
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
            if ( in_array( $attachment_id, self::$emitted_ids ) ) {
                continue;
            }
            $data = $this->get_image_schema_data( $attachment_id );
            if ( $data ) {
                $json_data[] = $data;
                self::$emitted_ids[] = $attachment_id;
            }
        }

        if ( ! empty( $json_data ) ) {
            echo "\n<!-- Image Copyright Manager: JSON-LD start -->\n";
            echo '<script type="application/ld+json">' . "\n";
            echo wp_json_encode( $json_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_PRETTY_PRINT );
            echo "\n" . '</script>' . "\n";
            echo "<!-- Image Copyright Manager: JSON-LD end -->\n";
        }
    }

    /**
     * Outputs JSON-LD structured data for the main image on single attachment pages.
     */
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
                echo wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_PRETTY_PRINT );
                echo "\n" . '</script>' . "\n";
                self::$emitted_ids[] = $attachment_id;
            }
        }
    }

    /**
     * Generates Schema.org ImageObject data for an attachment.
     *
     * @param int $attachment_id The attachment ID.
     * @return array|bool Schema data array or false if criteria not met.
     */
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