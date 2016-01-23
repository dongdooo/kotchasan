<?php
/**
 * @filesource Kotchasan/Object.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

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
	 * @assert ((object)array('one' => 1), array('two' => 2)) [==] (object)array('one' => 1, 'two' => 2)
	 * @assert ((object)array('one' => 1), (object)array('two' => 2)) [==] (object)array('one' => 1, 'two' => 2)
	 */
	public static function replace($a, $b)
	{
		foreach ($b as $key => $value) {
			$a->$key = $value;
		}
		return $a;
	}
}