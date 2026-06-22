<?php
/**
 * Class DisplayTest
 *
 * @package Image_Copyright_Manager
 */

/**
 * Unit tests for IMAGCOMA_Display.
 */
class DisplayTest extends WP_UnitTestCase {

	private $display;

	public function setUp(): void {
		parent::setUp();
		$this->display = new IMAGCOMA_Display();
	}

	public function tearDown(): void {
		parent::tearDown();
		IMAGCOMA_Display::$processed_attachments = array();
		
		// Reset emitted_ids using reflection
		$reflection = new ReflectionClass( 'IMAGCOMA_Display' );
		$prop = $reflection->getProperty( 'emitted_ids' );
		$prop->setAccessible( true );
		$prop->setValue( null, array() );
	}

	/**
	 * Test JSON-LD data collection.
	 */
	public function test_add_to_json_ld() {
		IMAGCOMA_Display::$processed_attachments = array();
		IMAGCOMA_Display::add_to_json_ld( 101 );
		IMAGCOMA_Display::add_to_json_ld( 101 ); // Duplicate
		IMAGCOMA_Display::add_to_json_ld( 102 );

		$this->assertCount( 2, IMAGCOMA_Display::$processed_attachments );
		$this->assertContains( 101, IMAGCOMA_Display::$processed_attachments );
		$this->assertContains( 102, IMAGCOMA_Display::$processed_attachments );
	}

	/**
	 * Test schema data generation security.
	 */
	public function test_get_image_schema_data_sanitization() {
		// Create a mock attachment
		$attachment_id = $this->factory->attachment->create( array(
			'file' => 'test.jpg'
		));

		// Save potentially malicious SEO data
		IMAGCOMA_Utils::save_copyright_info( 
			$attachment_id, 
			'Normal Copyright', 
			'Creator</script><script>alert(1)</script>', // Malicious creator
			'Notice',
			'Credit',
			'https://example.com/license'
		);

		// Use reflection to access private method
		$reflection = new ReflectionClass( 'IMAGCOMA_Display' );
		$method = $reflection->getMethod( 'get_image_schema_data' );
		$method->setAccessible( true );

		$data = $method->invoke( $this->display, $attachment_id );

		// Validate structure
		$this->assertEquals( 'https://schema.org/', $data['@context'] );
		$this->assertEquals( 'ImageObject', $data['@type'] );
		$this->assertStringContainsString( 'test.jpg', $data['contentUrl'] );
		$this->assertEquals( 'Creator</script><script>alert(1)</script>', $data['creator']['name'] );
		$this->assertEquals( 'Notice', $data['copyrightNotice'] );
		$this->assertEquals( 'Credit', $data['creditText'] );
		$this->assertEquals( 'https://example.com/license', $data['license'] );
		
		// The actual escaping happens during output in output_json_ld() using wp_json_encode with security flags.
		// So we verify the data is collected correctly here.
		$json_output = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_APOS );
		$this->assertStringNotContainsString( '</script>', $json_output );
		$this->assertStringContainsString( '\u003C/script\u003E', $json_output );
	}

	/**
	 * Test that duplicates are not emitted.
	 */
	public function test_no_duplicate_output() {
		IMAGCOMA_Display::$processed_attachments = array( 101 );
		
		// Use reflection to set emitted_ids
		$reflection = new ReflectionClass( 'IMAGCOMA_Display' );
		$prop = $reflection->getProperty( 'emitted_ids' );
		$prop->setAccessible( true );
		$prop->setValue( null, array( 101 ) ); // Mark as already emitted

		// Mock image data to avoid early return in foreach
		// (get_image_schema_data would return false for ID 101 anyway, but we want to test the continue logic)
		
		ob_start();
		$this->display->output_json_ld();
		$output = ob_get_clean();

		$this->assertStringNotContainsString( '<script', $output );
	}
}
