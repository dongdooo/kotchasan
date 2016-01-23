<?php
/*
 * @filesource Kotchasan/Http/AbstractRequest.php
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
class AbstractRequest extends AbstractMessage implements RequestInterface
{
	/**
	 * @var UriInterface
	 */
	protected $uri;
	/**
	 * @var string
	 */
	protected $method = 'GET';
	/**
	 * @var string
	 */
	protected $requestTarget;

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
		if ($this->uri === null) {
			$this->uri = Uri::createFromGlobals();
		}
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