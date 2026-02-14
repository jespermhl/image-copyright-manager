<?php
/**
 * Class CoreTest
 *
 * @package Image_Copyright_Manager
 */

/**
 * Unit tests for IMAGCOMA_Core.
 */
class CoreTest extends WP_UnitTestCase {

	private $core;

	public function setUp(): void {
		parent::setUp();
		$this->core = new IMAGCOMA_Core();
	}

	/**
	 * Test plugin constants.
	 */
	public function test_constants() {
		$this->assertNotEmpty( IMAGCOMA_Core::VERSION );
		$this->assertEquals( 'image-copyright-manager', IMAGCOMA_Core::TEXT_DOMAIN );
	}

	/**
	 * Test settings retrieval.
	 */
	public function test_get_settings() {
		$settings = IMAGCOMA_Core::get_settings();
		
		$this->assertArrayHasKey( 'display_text', $settings );
		$this->assertArrayHasKey( 'enable_css', $settings );
		$this->assertArrayHasKey( 'enable_json_ld', $settings );
	}
}
