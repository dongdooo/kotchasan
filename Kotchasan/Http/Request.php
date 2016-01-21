<?php
/*
 * @filesource Kotchasan/Http/Request.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\UriInterface;
use \Kotchasan\Http\AbstractMessage;
use \Kotchasan\Http\Stream;
use \Kotchasan\Http\Uri;

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
	 * @param null|string $uri URI for the request
	 * @param null|string $method HTTP method for the request
	 * @param string|resource|StreamInterface $body Message body
	 * @param array $headers Headers for the message
	 * @throws \InvalidArgumentException ถ้ามีข้อผิดพลาด
	 */
	public function __construct($uri = null, $method = 'GET', $body = null, array $headers = array())
	{
		$this->uri = $uri;
		$this->method = $method;
		$this->stream = $body ? new Stream($body) : null;
		foreach ($headers as $name => $value) {
			$this->filterHeader($name);
			$this->headers[strtolower($name)] = array(
				$name,
				is_array($value) ? $value : (array)$value
			);
		}
	}

	/**
	 * สร้าง Request จากตัวแปร $_SERVER
	 *
	 * @return \static
	 * @throws \InvalidArgumentException ถ้า Request ไม่ถูกต้อง
	 */
	public static function createFromGlobals()
	{
		return new static(
		Uri::createFromGlobals()
		);
	}

	/**
	 * อ่านค่า request target.
	 *
	 * @return string
	 */
	public function getRequestTarget()
	{
		if ($this->requestTarget === null) {
			$this->requestTarget = $this->uri;
		}
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
		if (!$preserveHost) {
			if ($uri->getHost() !== '') {
				$clone->headers['Host'] = $uri->getHost();
			}
		} else {
			if ($this->uri->getHost() !== '' && (!$this->hasHeader('Host') || $this->getHeader('Host') === null)) {
				$clone->headers['Host'] = $uri->getHost();
			}
		}
		return $clone;
	}
}