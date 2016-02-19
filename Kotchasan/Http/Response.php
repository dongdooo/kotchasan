<?php
/*
 * @filesource Kotchasan/Http/Response.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

use \Psr\Http\Message\ResponseInterface;

/**
 * Response Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Response extends Message implements ResponseInterface
{
	/**
	 * @var int
	 */
	private $statusCode = 200;
	/**
	 * @var string
	 */
	private $reasonPhrase;

	/**
	 * create Response
	 *
	 * @param int $code
	 * @param string $reasonPhrase
	 */
	public function __construct($code, $reasonPhrase = '')
	{
		$this->status = $code;
		$this->reasonPhrase = $reasonPhrase;
	}

	/**
	 * คืนค่า Response Status
	 *
	 * @return int.
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * กำหนดค่า status code
	 *
	 * @param int $code
	 * @param string $reasonPhrase
	 * @return self
	 */
	public function withStatus($code, $reasonPhrase = '')
	{
		$clone = clone $this;
		$clone->status = $code;
		$clone->reasonPhrase = $reasonPhrase;
		return $clone;
	}

	/**
	 * Gets the response reason phrase associated with the status code.
	 *
	 * @link http://tools.ietf.org/html/rfc7231#section-6
	 * @link http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 * @return string
	 */
	public function getReasonPhrase()
	{
		return $this->reasonPhrase;
	}
}