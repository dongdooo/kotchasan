<?php
/*
 * @filesource http/request.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Http;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\UriInterface;
use \Core\Http\AbstractMessage;
use \Core\Http\Stream;
use \Core\Http\Uri;

/**
 * Class สำหรับจัดการ URL
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Request extends AbstractMessage implements RequestInterface
{
	/**
	 * @var UriInterface
	 */
	protected $uri;
	/**
	 * @var string
	 */
	protected $method;
	/**
	 * @var string
	 */
	protected $requestTarget;

	/**
	 * create Request
	 *
	 * @param null|string $uri URI for the request, if any.
	 * @param null|string $method HTTP method for the request, if any.
	 * @param string|resource|StreamInterface $body Message body, if any.
	 * @param array $headers Headers for the message, if any.
	 * @throws \InvalidArgumentException for any invalid value.
	 */
	public function __construct($uri = null, $method = null, $body = 'php://temp', array $headers = array())
	{
		$this->method = $method;
		$this->uri = $uri;
	}

	/**
	 * อ่านค่า request target.
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		return $this->requestTarget;
	}

	/**
	 * กำหนดค่า request target.
	 *
	 * @param mixed $requestTarget
	 * @return self
	 */
	public function withRequestTarget($requestTarget)
	{
		$clone = clone $this;
		$clone->requestTarget = $requestTarget;
		return $clone;
	}

	/**
	 * อ่านค่า HTTP method
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * กำหนดค่า HTTP method
	 *
	 * @param string $method
	 * @return self
	 */
	public function withMethod($method)
	{
		$clone = clone $this;
		$clone->method = $method;
		return $clone;
	}

	/**
	 * อ่าน Uri
	 *
	 * @return UriInterface
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * กำหนดค่า Uri
	 *
	 * @param UriInterface $uri
	 * @param bool $preserveHost
	 * @return self
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		$clone = clone $this;
		$clone->uri = $uri;
		return $clone;
	}
}