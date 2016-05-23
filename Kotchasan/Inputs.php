<?php
/*
 * @filesource Kotchasan/Inputs.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\InputItem;

/**
 * รายการ input รูปแบบ Array
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Inputs implements \Iterator
{
	/**
	 * ตัวแปรเก็บ properties ของคลาส
	 *
	 * @var array
	 */
	private $datas = array();

	/**
	 * Class Constructer
	 *
	 * @param array $items รายการ input
	 */
	public function __construct(array $items = array())
	{
		foreach ($items as $key => $value) {
			if (is_array($value)) {
				$this->datas[$key] = new static($value);
			} else {
				$this->datas[$key] = InputItem::create($value);
			}
		}
	}

	/**
	 * magic method คืนค่าข้อมูลสำหรับ input ชนิด array
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return array
	 * @throws \InvalidArgumentException ถ้าไม่มี method ที่ต้องการ
	 */
	public function __call($name, $arguments)
	{
		if (method_exists('\Kotchasan\InputItem', $name)) {
			$result = array();
			foreach ($this->datas as $key => $item) {
				$result[$key] = $item->$name($arguments);
			}
			return $result;
		} else {
			throw new \InvalidArgumentException('Method '.$name.' not found');
		}
	}

	/**
	 * อ่าน Input ที่ต้องการ
	 *
	 * @param string|int $key รายการที่ต้องการ
	 * @return InputItem
	 */
	public function get($key)
	{
		return $this->datas[$key];
	}

	/**
	 * inherited from Iterator
	 */
	public function rewind()
	{
		reset($this->datas);
	}

	/**
	 * @return InputItem
	 */
	public function current()
	{
		$var = current($this->datas);
		return $var;
	}

	/**
	 * @return string
	 */
	public function key()
	{
		$var = key($this->datas);
		return $var;
	}

	/**
	 * @return InputItem
	 */
	public function next()
	{
		$var = next($this->datas);
		return $var;
	}

	/**
	 * @return bool
	 */
	public function valid()
	{
		$key = key($this->datas);
		return ($key !== NULL && $key !== FALSE);
	}
}