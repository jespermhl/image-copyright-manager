<?php
/**
 * Metadata Extractor functionality
 * Automatically extracts copyright information from EXIF/IPTC metadata on upload
 *
 * @package Image_Copyright_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles automatic extraction of copyright metadata from uploaded images.
 */
class IMAGCOMA_Metadata_Extractor {
    
    /**
     * Constructor.
     * Initializes hooks for metadata extraction.
     */
    public function __construct() {
        add_action( 'add_attachment', array( $this, 'extract_metadata_on_upload' ) );
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'extract_metadata_after_generation' ), 10, 2 );
    }
    
    /**
     * Extracts copyright metadata when an attachment is first added.
     *
     * @param int $attachment_id The attachment ID.
     */
    public function extract_metadata_on_upload( $attachment_id ) {
        // Only process images
        if ( ! wp_attachment_is_image( $attachment_id ) ) {
            return;
        }
        
        $this->extract_and_save_metadata( $attachment_id );
    }
    
    /**
     * Extracts copyright metadata after WordPress generates attachment metadata.
     *
     * @param array $metadata      Attachment metadata.
     * @param int   $attachment_id Attachment ID.
     * @return array Unmodified metadata.
     */
    public function extract_metadata_after_generation( $metadata, $attachment_id ) {
        // Only process images
        if ( ! wp_attachment_is_image( $attachment_id ) ) {
            return $metadata;
        }
        
        $this->extract_and_save_metadata( $attachment_id );
        return $metadata;
    }
    
    /**
     * Extracts and saves copyright metadata from an image file.
     *
     * @param int $attachment_id The attachment ID.
     */
    private function extract_and_save_metadata( $attachment_id ) {
        // Check if auto-extraction is enabled
        $settings = IMAGCOMA_Core::get_settings();
        if ( empty( $settings['enable_auto_extract'] ) ) {
            return; // Auto-extraction is disabled
        }
        
        // Request-scoped guard to prevent double processing
        static $processed_ids = array();
        
        if ( isset( $processed_ids[ $attachment_id ] ) ) {
            return; // Already processed in this request
        }
        
        // Mark as being processed
        $processed_ids[ $attachment_id ] = true;
        
        // Get the file path
        $file = get_attached_file( $attachment_id );
        
        if ( ! $file || ! file_exists( $file ) ) {
            return;
        }
        
        // Check if copyright info already exists (don't overwrite manual entries)
        $existing_data = IMAGCOMA_Utils::get_copyright_info( $attachment_id );
        
        // Don't overwrite if ANY field has been manually entered
        if ( ! empty( $existing_data['copyright'] ) 
            || ! empty( $existing_data['creator'] ) 
            || ! empty( $existing_data['copyright_notice'] )
            || ! empty( $existing_data['credit_text'] )
            || ! empty( $existing_data['license_url'] )
            || ! empty( $existing_data['acquire_license_url'] ) ) {
            return; // Don't overwrite existing data
        }
        
        // Read image metadata using WordPress core function
        $image_meta = wp_read_image_metadata( $file );
        
        if ( ! $image_meta ) {
            return;
        }
        
        // Extract copyright information from various metadata fields
        $copyright_text = '';
        $creator = '';
        $copyright_notice = '';
        $credit_text = '';
        
        // Try to get copyright from EXIF/IPTC
        if ( ! empty( $image_meta['copyright'] ) ) {
            $copyright_text = $image_meta['copyright'];
        }
        
        // Try to get creator/artist
        if ( ! empty( $image_meta['credit'] ) ) {
            $credit_text = $image_meta['credit'];
        }
        
        // Try to get artist/creator
        if ( ! empty( $image_meta['artist'] ) ) {
            $creator = $image_meta['artist'];
        }
        
        // Additional IPTC extraction for more detailed copyright info
        $iptc_data = $this->read_iptc_data( $file );
        
        if ( $iptc_data ) {
            // IPTC Copyright Notice (2#116) - Standard copyright field
            if ( ! empty( $iptc_data['2#116'][0] ) ) {
                if ( empty( $copyright_text ) ) {
                    $copyright_text = $iptc_data['2#116'][0];
                }
                if ( empty( $copyright_notice ) ) {
                    $copyright_notice = $iptc_data['2#116'][0];
                }
            }
            
            // IPTC Creator (2#080)
            if ( ! empty( $iptc_data['2#080'][0] ) && empty( $creator ) ) {
                $creator = $iptc_data['2#080'][0];
            }
            
            // IPTC Credit (2#110)
            if ( ! empty( $iptc_data['2#110'][0] ) && empty( $credit_text ) ) {
                $credit_text = $iptc_data['2#110'][0];
            }
        }
        
        // Try to extract from XMP data as well (Lightroom often uses XMP)
        $xmp_data = $this->read_xmp_data( $file );
        if ( $xmp_data ) {
            // XMP Rights
            if ( ! empty( $xmp_data['rights'] ) && empty( $copyright_text ) ) {
                $copyright_text = $xmp_data['rights'];
            }
            
            // XMP Creator
            if ( ! empty( $xmp_data['creator'] ) && empty( $creator ) ) {
                $creator = $xmp_data['creator'];
            }
            
            // XMP Credit
            if ( ! empty( $xmp_data['credit'] ) && empty( $credit_text ) ) {
                $credit_text = $xmp_data['credit'];
            }
        }
        
        // Sanitize all extracted metadata before saving
        $copyright_text = IMAGCOMA_Utils::sanitize_copyright_html( $copyright_text );
        $creator = sanitize_text_field( $creator );
        $copyright_notice = sanitize_text_field( $copyright_notice );
        $credit_text = sanitize_text_field( $credit_text );
        
        // Only save if we found at least some copyright information
        if ( ! empty( $copyright_text ) || ! empty( $creator ) || ! empty( $credit_text ) || ! empty( $copyright_notice ) ) {
            IMAGCOMA_Utils::save_copyright_info(
                $attachment_id,
                $copyright_text,
                $creator,
                $copyright_notice,
                $credit_text,
                '', // license_url
                ''  // acquire_license_url
            );
        }
    }
    
    /**
     * Reads IPTC data from an image file.
     *
     * @param string $file Path to the image file.
     * @return array|false IPTC data or false on failure.
     */
    private function read_iptc_data( $file ) {
        // We only need $info, return value is intentionally ignored
        getimagesize( $file, $info );
        
        if ( ! isset( $info['APP13'] ) ) {
            return false;
        }
        
        $iptc = iptcparse( $info['APP13'] );
        
        return $iptc ? $iptc : false;
    }
    
    /**
     * Reads XMP data from an image file.
     * Lightroom often stores copyright information in XMP metadata.
     * Uses bounded reads to avoid loading entire file into memory.
     *
     * @param string $file Path to the image file.
     * @return array|false XMP data or false on failure.
     */
    private function read_xmp_data( $file ) {
        $handle = @fopen( $file, 'rb' );
        
        if ( ! $handle ) {
            return false;
        }
        
        // Read in chunks to find XMP data without loading entire file
        $chunk_size = 262144; // 256KB chunks
        $max_read = 2097152; // Max 2MB to search for XMP
        $buffer = '';
        $total_read = 0;
        
        while ( ! feof( $handle ) && $total_read < $max_read ) {
            $chunk = fread( $handle, $chunk_size );
            if ( false === $chunk ) {
                fclose( $handle );
                return false;
            }
            
            $buffer .= $chunk;
            $total_read += strlen( $chunk );
            
            // Check if we have the complete XMP packet
            if ( strpos( $buffer, '</x:xmpmeta>' ) !== false ) {
                break;
            }
        }
        
        fclose( $handle );
        
        $xmp_data = array();
        
        // Extract XMP data between <x:xmpmeta> tags
        if ( preg_match( '/<x:xmpmeta.*?<\/x:xmpmeta>/s', $buffer, $matches ) ) {
            $xmp = $matches[0];
            
            // Extract dc:rights (Dublin Core Rights)
            if ( preg_match( '/<dc:rights>.*?<rdf:Alt>.*?<rdf:li[^>]*>(.*?)<\/rdf:li>/s', $xmp, $rights_match ) ) {
                $xmp_data['rights'] = trim( strip_tags( $rights_match[1] ) );
            }
            
            // Extract dc:creator
            if ( preg_match( '/<dc:creator>.*?<rdf:Seq>.*?<rdf:li>(.*?)<\/rdf:li>/s', $xmp, $creator_match ) ) {
                $xmp_data['creator'] = trim( strip_tags( $creator_match[1] ) );
            }
            
            // Extract photoshop:Credit
            if ( preg_match( '/<photoshop:Credit>(.*?)<\/photoshop:Credit>/s', $xmp, $credit_match ) ) {
                $xmp_data['credit'] = trim( strip_tags( $credit_match[1] ) );
            }
            
            // Extract xmpRights:UsageTerms (Lightroom uses this)
            if ( preg_match( '/<xmpRights:UsageTerms>.*?<rdf:Alt>.*?<rdf:li[^>]*>(.*?)<\/rdf:li>/s', $xmp, $usage_match ) ) {
                if ( empty( $xmp_data['rights'] ) ) {
                    $xmp_data['rights'] = trim( strip_tags( $usage_match[1] ) );
                }
            }
        }
        
        return ! empty( $xmp_data ) ? $xmp_data : false;
    }
}
