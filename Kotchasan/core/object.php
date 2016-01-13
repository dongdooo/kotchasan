<?php
/**
 * @filesource object.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Object tools
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Object
{

	/**
	 * ฟังก์ชั่นรวม object แทนที่คีย์เดิม
	 *
	 * @param object $a
	 * @param array|object $b
	 * @return object
	 */
	public static function replace($a, $b)
	{
		foreach ($b as $key => $value) {
			$a->$key = $value;
		}
		return $a;
	}
}