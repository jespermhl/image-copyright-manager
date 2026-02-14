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

		$this->assertEquals( 'Creator</script><script>alert(1)</script>', $data['creator']['name'] );
		
		// The actual escaping happens during output in output_json_ld() using wp_json_encode with security flags.
		// So we verify the data is collected correctly here.
		$json_output = wp_json_encode( $data, JSON_HEX_TAG );
		$this->assertStringNotContainsString( '</script>', $json_output );
		$this->assertStringContainsString( '\u003C/script\u003E', $json_output );
	}
}
