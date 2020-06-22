<?php namespace Yoast\WP\SEO\Exceptions\OAuth;

/**
 * Class OAuth_Authentication_Failed_Exception
 * @package Yoast\WP\SEO\Exceptions\OAuth
 */
class OAuth_Authentication_Failed_Exception extends \Exception {

	/**
	 * OAuth_Authentication_Failed_Exception constructor.
	 *
	 * @param \Exception $original_exception The original exception.
	 */
	public function __construct( \Exception $original_exception ) {
		parent::__construct( 'Authentication failed', 401, $original_exception );
	}

	/**
	 * Returns a formatted response object.
	 *
	 * @return object The response object.
	 */
	public function get_response() {
		return (object) [
			'tokens' => [],
			'error'  => $this->getMessage() . ': ' . $this->getPrevious()->getMessage(),
			'code'   => $this->getCode(),
		];
	}
}