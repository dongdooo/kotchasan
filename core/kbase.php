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
	 * Config Class
	 *
	 * @var \Config
	 */
	static protected $cfg;

	/**
	 * ฟังก์ชั่นอ่านค่าตัวแปรที่เป็น private หรือ protected
	 *
	 * @param string $key
	 * @return mixed
	 */
	protected function getAttribute($key)
	{
		if (isset($this->$key)) {
			return $this->$key;
		}
	}
}