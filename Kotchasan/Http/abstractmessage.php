<?php
/*
 * @filesource http/abstractmessage.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Http;

use \Psr\Http\Message\MessageInterface;
use \Psr\Http\Message\StreamInterface;

/**
 * HTTP messages base class (PSR-7)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class AbstractMessage implements MessageInterface
{
	/**
	 * @var string
	 */
	private $protocol = '1.1';
	/**
	 * @var StreamInterface
	 */
	protected $stream;
	/**
	 * @var array
	 */
	protected $headers = array();

	/**
	 * อ่านเวอร์ชั่นของโปรโตคอล
	 *
	 * @return string เช่น 1.1, 1.0
	 */
	public function getProtocolVersion()
	{
		return $this->protocol;
	}

	/**
	 * กำหนดเวอร์ชั่นของโปรโตคอล
	 *
	 * @param string $version เช่น 1.1, 1.0
	 * @return self
	 */
	public function withProtocolVersion($version)
	{
		$clone = clone $this;
		$clone->protocol = $version;
		return $clone;
	}

	/**
	 * คืนค่า header ทั้งหมด ผลลัพท์เป็น array
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		$result = array();
		foreach ($this->headers as $values) {
			$result[$values[0]] = $values[1];
		}
		return $result;
	}

	/**
	 * ตรวจสอบว่ามี header หรือไม่
	 *
	 * @param string $name
	 * @return bool คืนค่า true ถ้ามี
	 */
	public function hasHeader($name)
	{
		return isset($this->headers[strtolower($name)]);
	}

	/**
	 * อ่าน header ที่ต้องการ ผลลัพท์เป็น array
	 *
	 * @param string $name
	 * @return string[] คืนค่าแอเรย์ของ header ถ้าไม่พบคืนค่าแอเรย์ว่าง
	 */
	public function getHeader($name)
	{
		$result = array();
		$name = strtolower($name);
		if (isset($this->headers[$name])) {
			foreach ($this->headers[$name] as $values) {
				$result[] = $values[1];
			}
		}
		return $result;
	}

	/**
	 * อ่าน header ที่ต้องการ ผลลัพท์เป็น string
	 *
	 * @param string $name
	 * @return string คืนค่ารายการ header ทั้งหมดที่พบเชื่อมต่อด้วย ลูกน้ำ (,) หรือคืนค่าข้อความว่าง หากไม่พบ
	 */
	public function getHeaderLine($name)
	{
		$values = $this->getHeader($name);
		return empty($values) ? '' : implode('', $values);
	}

	/**
	 * กำหนด header แทนที่รายการเดิม
	 *
	 * @param string $name
	 * @param string|string[] $value
	 * @return self
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withHeader($name, $value)
	{
		$this->filterHeader($name);
		$clone = clone $this;
		$clone->headers[strtolower($name)] = array(
			$name,
			is_array($value) ? $value : (array)$value
		);
		return $clone;
	}

	/**
	 * เพิ่ม header ใหม่
	 *
	 * @param string $name
	 * @param string|string[] $value Header value(s).
	 * @return self
	 * @throws \InvalidArgumentException ถ้าชื่อ header ไม่ถูกต้อง
	 */
	public function withAddedHeader($name, $value)
	{
		$this->filterHeader($name);
		$clone = clone $this;
		$key = strtolower($name);
		if (is_array($value)) {
			foreach ($value as $item) {
				$clone->headers[$key][] = array($name, $item);
			}
		} else {
			$clone->headers[$key][] = array($name, $value);
		}
		return $clone;
	}

	/**
	 * ลบ header
	 *
	 * @param string $name ชื่อ header ที่ต้องการลบ
	 * @return self
	 */
	public function withoutHeader($name)
	{
		$clone = clone $this;
		unset($clone->headers[$name]);
		return $clone;
	}

	/**
	 * อ่าน stream
	 *
	 * @return StreamInterface
	 */
	public function getBody()
	{
		return $this->stream;
	}

	/**
	 * กำหนด stream
	 *
	 * @param StreamInterface $body.
	 * @return self
	 */
	public function withBody(StreamInterface $body)
	{
		$clone = clone $this;
		$clone->stream = $body;
		return $clone;
	}

	/**
	 * ตรวจสอบความถูกต้องของ header
	 *
	 * @param string $name
	 * @throws \InvalidArgumentException ถ้า header ไม่ถูกต้อง
	 */
	protected function filterHeader($name)
	{
		if (!preg_match('/^[a-zA-Z0-9\-]+$/', $name)) {
			throw new \InvalidArgumentException('Invalid header name');
		}
	}
}