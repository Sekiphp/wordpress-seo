<?php

namespace Yoast\WP\SEO\Tests\Actions\SEMrush;

use League\OAuth2\Client\Token\AccessTokenInterface;
use Mockery;
use Yoast\WP\SEO\Actions\SEMrush\SEMrush_Login_Action;
use Yoast\WP\SEO\Config\SEMrush_Client;
use Yoast\WP\SEO\Exceptions\OAuth\OAuth_Authentication_Failed_Exception;
use Yoast\WP\SEO\Tests\TestCase;
use Yoast\WP\SEO\Values\SEMrush\SEMrush_Token;

/**
 * Class SEMrush_Login_Action_Test
 *
 * @group semrush
 *
 * @coversDefaultClass \Yoast\WP\SEO\Actions\SEMrush\SEMrush_Login_Action
 */
class SEMrush_Login_Action_Test extends TestCase {

	/**
	 * @var SEMrush_Login_Action
	 */
	protected $instance;

	/**
	 * @var Mockery\MockInterface|SEMrush_Client
	 */
	protected $client_instance;

	/**
	 * @inheritDoc
	 */
	public function setUp() {
		parent::setUp();

		$this->client_instance = Mockery::mock( SEMrush_Client::class );
		$this->instance        = new SEMrush_Login_Action( $this->client_instance );
	}

	/**
	 * Tests if the needed attributes are set correctly.
	 *
	 * @covers ::__construct
	 */
	public function test_constructor() {
		$this->assertAttributeInstanceOf( SEMrush_Client::class, 'client', $this->instance );
	}

	/**
	 * Tests a valid authentication with SEMrush.
	 *
	 * @covers ::authenticate
	 */
	public function test_valid_authentication() {
		// Expected returned class by client
		$response = Mockery::mock( AccessTokenInterface::class );
		$response->allows( [
			'getToken'        => '000000',
			'getRefreshToken' => '000001',
			'getExpires'      => 604800,
			'hasExpired'      => false,
		] );

		$tokens_class = Mockery::mock( SEMrush_Token::class );

		$this->client_instance
			->expects( 'request_tokens' )
			->with( '123456' )
			->andReturn( $tokens_class );

		$authenticated_instance = $this
			->instance
			->authenticate( '123456' );

		$this->assertInstanceOf( SEMrush_Token::class, $authenticated_instance->tokens );
	}

	/**
	 * Tests an invalid authentication with SEMrush.
	 *
	 * @covers ::authenticate
	 */
	public function test_invalid_authentication() {
		// Expected returned class by client
		$failed_tokens_request = Mockery::mock( OAuth_Authentication_Failed_Exception::class );
		$failed_tokens_request
			->expects( 'get_response' )
			->once()
			->andReturn(
				(object) [
					'tokens' => [],
					'error'  => 'Invalid token',
					'status' => 500,
				] );

		$this->client_instance
			->expects( 'request_tokens' )
			->with( '123456' )
			->andThrow( $failed_tokens_request );

		$this->assertEquals(
			(object) [
				'tokens' => [],
				'error'  => 'Invalid token',
				'status' => 500,
			],
			$this->instance->authenticate( '123456' )
		);
	}
}
