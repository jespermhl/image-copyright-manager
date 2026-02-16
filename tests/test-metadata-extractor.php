<?php
/**
 * Tests for metadata extraction
 *
 * @package Image_Copyright_Manager
 */

class Test_Metadata_Extractor extends WP_UnitTestCase {

    /**
     * Test that the metadata extractor class exists
     */
    public function test_metadata_extractor_class_exists() {
        $this->assertTrue( class_exists( 'IMAGCOMA_Metadata_Extractor' ) );
    }

    /**
     * Test IPTC data reading
     */
    public function test_read_iptc_data() {
        $extractor = new IMAGCOMA_Metadata_Extractor();
        $reflection = new ReflectionClass( $extractor );
        $method = $reflection->getMethod( 'read_iptc_data' );
        $method->setAccessible( true );

        // Test with a non-existent file
        $result = $method->invoke( $extractor, '/path/to/nonexistent.jpg' );
        $this->assertFalse( $result );
    }

    /**
     * Test XMP data reading
     */
    public function test_read_xmp_data() {
        $extractor = new IMAGCOMA_Metadata_Extractor();
        $reflection = new ReflectionClass( $extractor );
        $method = $reflection->getMethod( 'read_xmp_data' );
        $method->setAccessible( true );

        // Test with a non-existent file
        $result = $method->invoke( $extractor, '/path/to/nonexistent.jpg' );
        $this->assertFalse( $result );
    }

    /**
     * Test that metadata extraction doesn't overwrite existing data
     */
    public function test_metadata_extraction_respects_existing_data() {
        // Create a test attachment
        $attachment_id = $this->factory->attachment->create_upload_object( 
            IMAGCOMA_PLUGIN_DIR . 'tests/fixtures/test-image.jpg' 
        );

        // Set existing copyright data
        IMAGCOMA_Utils::save_copyright_info( 
            $attachment_id, 
            'Existing Copyright', 
            'Existing Creator' 
        );

        // Try to extract metadata (should not overwrite)
        $extractor = new IMAGCOMA_Metadata_Extractor();
        $reflection = new ReflectionClass( $extractor );
        $method = $reflection->getMethod( 'extract_and_save_metadata' );
        $method->setAccessible( true );
        $method->invoke( $extractor, $attachment_id );

        // Verify existing data is preserved
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $attachment_id );
        $this->assertEquals( 'Existing Copyright', $copyright_data['copyright'] );
        $this->assertEquals( 'Existing Creator', $copyright_data['creator'] );
    }

    /**
     * Test metadata extraction on upload hook
     */
    public function test_extract_metadata_on_upload_hook() {
        $extractor = new IMAGCOMA_Metadata_Extractor();
        
        // Verify the hook is registered
        $this->assertEquals( 10, has_action( 'add_attachment', array( $extractor, 'extract_metadata_on_upload' ) ) );
    }

    /**
     * Test metadata extraction after generation hook
     */
    public function test_extract_metadata_after_generation_hook() {
        $extractor = new IMAGCOMA_Metadata_Extractor();
        
        // Verify the filter is registered
        $this->assertEquals( 10, has_filter( 'wp_generate_attachment_metadata', array( $extractor, 'extract_metadata_after_generation' ) ) );
    }

    /**
     * Test that non-image attachments are skipped
     */
    public function test_non_image_attachments_skipped() {
        // Create a non-image attachment (PDF)
        $attachment_id = $this->factory->attachment->create_object(
            'test.pdf',
            0,
            array(
                'post_mime_type' => 'application/pdf',
                'post_type' => 'attachment'
            )
        );

        $extractor = new IMAGCOMA_Metadata_Extractor();
        $extractor->extract_metadata_on_upload( $attachment_id );

        // Verify no copyright data was created
        $copyright_data = IMAGCOMA_Utils::get_copyright_info( $attachment_id );
        $this->assertEmpty( $copyright_data['copyright'] );
    }
}
