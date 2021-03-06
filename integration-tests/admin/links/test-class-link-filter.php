<?php
/**
 * WPSEO plugin test file.
 *
 * @package WPSEO\Tests\Admin\Links
 */

/**
 * Unit Test Class.
 */
class WPSEO_Link_Filter_Test extends WPSEO_UnitTestCase {

	/**
	 * Tests whether the internal link fragment filter filters as expected.
	 *
	 * @dataProvider link_provider
	 *
	 * @param string     $current_page Current page.
	 * @param WPSEO_Link $link         Link object.
	 * @param bool       $expected     Expected output.
	 *
	 * @covers WPSEO_Link_Filter::internal_link_with_fragment_filter
	 */
	public function test_internal_link_with_fragment_filter( $current_page, WPSEO_Link $link, $expected ) {
		$filter = new WPSEO_Link_Filter( $current_page );

		$this->assertEquals( $expected, $filter->internal_link_with_fragment_filter( $link ) );
	}

	/**
	 * Provides a couple of internal links.
	 *
	 * @return array
	 */
	public function link_provider() {

		return [
			[
				'page',
				new WPSEO_Link( 'testpage', 0, 'internal' ),
				true,
			],
			[
				'testpage',
				new WPSEO_Link( 'testpage', 0, 'internal' ),
				false,
			],
			[
				'page',
				new WPSEO_Link( 'page#fragment', 0, 'internal' ),
				false,
			],
			[
				'page',
				new WPSEO_Link( 'testpage#fragment', 0, 'internal' ),
				true,
			],
			[
				'page',
				new WPSEO_Link( 'page?param=foo', 0, 'internal' ),
				false,
			],
			[
				'page',
				new WPSEO_Link( 'testpage?param=foo', 0, 'internal' ),
				true,
			],
			[
				'page',
				new WPSEO_Link( 'http://extern.al/page?param=foo', 0, 'external' ),
				true,
			],
			[
				'page',
				new WPSEO_Link( '/', 0, 'internal' ),
				true,
			],
			[
				'/',
				new WPSEO_Link( '/', 0, 'internal' ),
				true,
			],
			[
				'page',
				new WPSEO_Link( '?param=foo', 0, 'internal' ),
				false,
			],
			[
				'page',
				new WPSEO_Link( '#fragment', 0, 'internal' ),
				false,
			],
		];
	}
}
