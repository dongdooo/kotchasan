<?php
/**
 * @filesource core/kbase.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Base Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class KBase
{

	/**
	 * ใช้สำหรับสร้าง Class
	 *
	 * @param string $className ชื่อ class (carmelCase)
	 * @param mixed $param (option)
	 * @return stdClass
	 */
	public function createClass($className, $param = null)
	{
		return new $className($this, $param);
	}

	/**
	 * ฟังก์ชั่นอ่านค่าตัวแปรที่เป็น private หรือ protected
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		if (isset($this->$key)) {
			return $this->$key;
		}
	}

	/**
	 * ฟังก์ชั่นกำหนดค่าตัวแปรที่เป็น private หรือ protected
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function setAttributes($key, $value)
	{
		if (isset($this->$key)) {
			return $this->$key = $value;
		}
	}
}