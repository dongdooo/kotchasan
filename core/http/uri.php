<?php
/*
 * @filesource core/http/uri.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Http;

use \Psr\Http\Message\UriInterface;

/**
 * Class สำหรับจัดการ URL
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Uri implements UriInterface
{
	/**
	 * Uri scheme
	 *
	 * @var string
	 */
	private $scheme = '';
	/**
	 * Uri user info
	 *
	 * @var string
	 */
	private $userInfo = '';
	/**
	 * Uri host
	 *
	 * @var string
	 */
	private $host = '';
	/**
	 * Uri port
	 *
	 * @var int
	 */
	private $port;
	/**
	 * Uri path
	 *
	 * @var string
	 */
	private $path = '';
	/**
	 * Uri query string
	 *
	 * @var string
	 */
	private $query = '';
	/**
	 * Uri fragment
	 *
	 * @var string
	 */
	private $fragment = '';

	/**
	 * create Uri
	 *
	 * @param string $uri
	 * @throws \InvalidArgumentException ถ้า Uri ไม่ถูกต้อง
	 */
	public function __construct($uri = '')
	{
		if (!empty($uri)) {
			$parts = parse_url($uri);
			if (false === $parts) {
				throw new \InvalidArgumentException('Invalid Uri');
			} else {
				$this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
				$this->userInfo = isset($parts['user']) ? $parts['user'] : '';
				$this->host = isset($parts['host']) ? $parts['host'] : '';
				$this->port = !empty($parts['port']) ? $parts['port'] : null;
				$this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
				$this->query = isset($parts['query']) ? $this->filterQueryFragment($parts['query']) : '';
				$this->fragment = isset($parts['fragment']) ? $this->filterQueryFragment($parts['fragment']) : '';
				if (isset($parts['pass'])) {
					$this->userInfo .= ':'.$parts['pass'];
				}
			}
		}
	}

	/**
	 * magic function ส่งออกคลาสเป็น String
	 *
	 * @return string
	 */
	public function __toString()
	{
		return self::createUriString(
		$this->scheme, $this->getAuthority(), $this->path, $this->query, $this->fragment
		);
	}

	/**
	 * คืนค่า scheme ของ Uri ไม่รวม :// เช่น http, https
	 *
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * ตืนค่า authority ของ Uri [user-info@]host[:port]
	 *
	 * @return string
	 */
	public function getAuthority()
	{
		$port = $this->getPort();
		return ($this->userInfo ? $this->userInfo.'@' : '').$this->host.($port !== null ? ':'.$port : '');
	}

	/**
	 * คืนค่าข้อมูล user ของ Uri user[:password]
	 *
	 * @return string
	 */
	public function getUserInfo()
	{
		return $this->userInfo;
	}

	/**
	 * คืนค่า Hostname ของ Uri เช่น domain.tld
	 *
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * คืนค่าหมายเลข Port ของ Uri
	 * ไม่ระบุหรือเป็น default port (80,433) คืนค่า null
	 *
	 * @return null|int
	 */
	public function getPort()
	{
		return $this->filterPort($this->scheme, $this->host, $this->port) ? $this->port : null;
	}

	/**
	 * คืนค่า path ของ Uri เช่น /kotchasan
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * คืนค่า query string (ข้อมูลหลัง ? ใน Uri) ของ Uri
	 *
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * คืนค่า fragment (ข้อมูลหลัง # ใน Uri) ของ Uri
	 *
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * กำหนดค่า scheme ของ Uri
	 *
	 * @param string $scheme http หรือ https หรือค่าว่าง
	 * @return \static คืนค่า Object ใหม่
	 * @throws \InvalidArgumentException ถ้าไม่ใช่ ค่าว่าง http หรือ https
	 */
	public function withScheme($scheme)
	{
		$clone = clone $this;
		$clone->scheme = $this->filterScheme($scheme);
		return $clone;
	}

	/**
	 * กำหนดข้อมูล user ของ Uri
	 *
	 * @param string $user
	 * @param string $password
	 * @return \static คืนค่า Object ใหม่
	 */
	public function withUserInfo($user, $password = null)
	{
		$clone = clone $this;
		$clone->userInfo = $user.($password ? ':'.$password : '');
		return $clone;
	}

	/**
	 * กำหนดชื่อ host
	 *
	 * @param string $host ชื่อ host
	 * @return \static คืนค่า Object ใหม่
	 */
	public function withHost($host)
	{
		$clone = clone $this;
		$clone->host = $host;
		return $clone;
	}

	/**
	 * กำหนดค่า port
	 *
	 * @param null|int $port หมายเลข port 1- 65535 หรือ null
	 * @return \static คืนค่า Object ใหม่
	 * @throws \InvalidArgumentException ถ้า port ไม่ถูกต้อง
	 */
	public function withPort($port)
	{
		$clone = clone $this;
		$clone->port = $this->filterPort($this->scheme, $this->host, $port);
		return $clone;
	}

	/**
	 * กำหนดชื่อ path
	 * path ต้องเริ่มต้นด้วย / เช่น /kotchasan
	 * หรือเป็นค่าว่าง ถ้าเป็นรากของโดเมน
	 *
	 * @param string $path ชื่อ path
	 * @return \static คืนค่า Object ใหม่
	 * @throws \InvalidArgumentException ถ้า path ไม่ถูกต้อง
	 */
	public function withPath($path)
	{
		if (!is_string($path)) {
			throw new \InvalidArgumentException('Uri path must be a string');
		}
		$clone = clone $this;
		$clone->path = $this->filterPath($path);
		return $clone;
	}

	/**
	 * กำหนดค่า query string
	 *
	 * @param string $query
	 * @return \static คืนค่า Object ใหม่
	 * @throws \InvalidArgumentException ถ้า query string ไม่ถูกต้อง
	 */
	public function withQuery($query)
	{
		if (!is_string($query) && !method_exists($query, '__toString')) {
			throw new \InvalidArgumentException('Uri query must be a string');
		}
		$query = ltrim((string)$query, '?');
		$clone = clone $this;
		$clone->query = $this->filterQueryFragment($query);
		return $clone;
	}

	/**
	 * กำหนดค่า fragment
	 *
	 * @param string $fragment
	 * @return \static คืนค่า Object ใหม่
	 * @throws \InvalidArgumentException ถ้า fragment ไม่ถูกต้อง
	 */
	public function withFragment($fragment)
	{
		if (!is_string($fragment) && !method_exists($fragment, '__toString')) {
			throw new InvalidArgumentException('Uri fragment must be a string');
		}
		$fragment = ltrim((string)$fragment, '#');
		$clone = clone $this;
		$clone->fragment = $this->filterQueryFragment($fragment);
		return $clone;
	}

	/**
	 * ตรวจสอบ port
	 *
	 * @param string $scheme
	 * @param string $host
	 * @param int $port
	 * @return int|null
	 * @throws \InvalidArgumentException ถ้า port ไม่ถูกต้อง
	 */
	private function filterPort($scheme, $host, $port)
	{
		if (null !== $port) {
			$port = (int)$port;
			if (1 > $port || 0xffff < $port) {
				throw new \InvalidArgumentException('Port number must be between 1 and 65535');
			}
		}
		return $this->isNonStandardPort($scheme, $host, $port) ? $port : null;
	}

	/**
	 * สร้าง Uri จาก properties ต่างๆของคลาส
	 *
	 * @param string $scheme
	 * @param string $authority
	 * @param string $path
	 * @param string $query
	 * @param string $fragment
	 * @return string
	 */
	private static function createUriString($scheme, $authority, $path, $query, $fragment)
	{
		$uri = '';
		if (!empty($scheme)) {
			$uri .= $scheme.'://';
		}
		if (!empty($authority)) {
			$uri .= $authority;
		}
		if ($path != null) {
			if ($uri && substr($path, 0, 1) !== '/') {
				$uri .= '/';
			}
			$uri .= $path;
		}
		if ($query != null) {
			$uri .= '?'.$query;
		}
		if ($fragment != null) {
			$uri .= '#'.$fragment;
		}
		return $uri;
	}

	/**
	 * ตรวจสอบว่าเป็น port มาตรฐานหรือไม่
	 * เช่น http เป็น 80 หรือ https เป็น 433
	 *
	 * @param string $scheme
	 * @param string $host
	 * @param int $port
	 * @return bool
	 */
	private function isNonStandardPort($scheme, $host, $port)
	{
		$schemes = array(
			'http' => 80,
			'https' => 443,
		);
		if (!$scheme && $port) {
			return true;
		}
		if (!$host || !$port) {
			return false;
		}
		return !isset($schemes[$scheme]) || $port !== $schemes[$scheme];
	}

	/**
	 * ตรวจสอบ scheme
	 *
	 * @param string $scheme
	 * @return string
	 * @throws \InvalidArgumentException ถ้าไม่ใช่ ค่าว่าง http หรือ https
	 */
	private function filterScheme($scheme)
	{
		$schemes = array('' => '', 'http' => 'http', 'https' => 'https');
		$scheme = rtrim(strtolower($scheme), '://');
		if (isset($schemes[$scheme])) {
			return $scheme;
		} else {
			throw new \InvalidArgumentException('Uri scheme must be http, https or empty string');
		}
	}

	/**
	 * ตรวจสอบ query และ fragment
	 *
	 * @param $str
	 * @return string
	 */
	private function filterQueryFragment($str)
	{
		return preg_replace_callback(
		'/(?:[^a-zA-Z0-9_\-\.~!\$&\'\(\)\*\+,;=%:@\/\?]+|%(?![A-Fa-f0-9]{2}))/', function ($match) {
			return rawurlencode($match[0]);
		}, $str);
	}

	/**
	 * ตรวจสอบ path
	 *
	 * @param $path
	 * @return string
	 */
	private function filterPath($path)
	{
		return preg_replace_callback(
		'/(?:[^a-zA-Z0-9_\-\.~:@&=\+\$,\/;%]+|%(?![A-Fa-f0-9]{2}))/', function ($match) {
			return rawurlencode($match[0]);
		}, $path
		);
	}
}