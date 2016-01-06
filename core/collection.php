<?php
/*
 * @filesource core/collection.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Collection Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Collection implements Countable, IteratorAggregate, ArrayAccess
{
	/**
	 * ตัวแปรเก็บสมาชิกของคลาส
	 *
	 * @var array
	 */
	private $datas = array();

	/**
	 * Create new collection
	 *
	 * @param array $items สมาชิกเริ่มต้นของ Collection
	 */
	public function __construct(array $items = array())
	{
		foreach ($items as $key => $value) {
			$this->set($key, $value);
		}
	}
	/*	 * ****************
	 * Collection interface
	 * ******************* */

	/**
	 * กำหนดค่า $value ของ $key
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function set($key, $value)
	{
		$this->datas[$key] = $value;
	}

	/**
	 * อ่านข้อมูลที่ $key ถ้าไม่พบคืนค่า $default
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed คืนค่าข้อมูลตามชนิดของตัวแปร $default
	 */
	public function get($key, $default = null)
	{
		if ($this->has($key)) {
			$result = $this->datas[$key];
			if (is_float($default)) {
				// จำนวนเงิน เช่น 0.0
				$result = (double)$result;
			} elseif (is_int($default)) {
				// เลขจำนวนเต็ม เช่น 0
				$result = (int)$result;
			} elseif (is_bool($default)) {
				// true, false
				$result = (boolean)$result;
			}
		} else {
			$result = $default;
		}
		return $result;
	}

	/**
	 * เพิ่มรายการใหม่ แทนที่รายการเดิม
	 *
	 * @param array $items array(array($key => $value), array($key => $value), ...)
	 */
	public function replace(array $items)
	{
		foreach ($items as $key => $value) {
			$this->set($key, $value);
		}
	}

	/**
	 * คืนค่าข้อมูลทั้งหมดเป็น
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->datas;
	}

	/**
	 * อ่านรายชื่อ keys
	 *
	 * @return array แอเรย์ของรายการ key ทั้งหมด
	 */
	public function keys()
	{
		return array_keys($this->datas);
	}

	/**
	 * ตรวจสอบว่ามีรายการ $key หรือไม่
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->datas);
	}

	/**
	 * ลบรายการที่ $key
	 *
	 * @param string $key
	 */
	public function remove($key)
	{
		unset($this->datas[$key]);
	}

	/**
	 * ลบข้อมูลทั้งหมด
	 */
	public function clear()
	{
		$this->datas = array();
	}
	/*	 * *****************
	 * ArrayAccess interface
	 * ********************* */

	/**
	 * ตรวจสอบว่ามีรายการ $key หรือไม่
	 *
	 * @param  string $key
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return $this->has($key);
	}

	/**
	 * อ่านข้อมูลที่ $key
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->get($key);
	}

	/**
	 * กำหนดค่า $value ของ $key
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
	}

	/**
	 * ลบรายการที่ $key
	 *
	 * @param string $key
	 */
	public function offsetUnset($key)
	{
		$this->remove($key);
	}

	/**
	 * คืนค่าจำนวนข้อมูลทั้งหมด
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->datas);
	}
	/*	 * **********************
	 * IteratorAggregate interface
	 * ************************* */

	/**
	 * Retrieve an external iterator
	 *
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ArrayIterator($this->datas);
	}

	/**
	 * create Collection จากไฟล์
	 *
	 * @param string $file ชื่อไฟล์รวมภาธ
	 * @return \Collection
	 */
	public static function loadFromFile($file)
	{
		$config = include($file);
		$obj = new static($config);
		return $obj;
	}

	/**
	 * เพิ่มรายการใหม่ที่ตำแหน่งสุดท้ายของข้อมูล
	 *
	 * @param mixed $item
	 */
	public function add($item)
	{
		$this->datas[] = $item;
	}
}