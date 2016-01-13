<?php
/*
 * @filesource Kotchasan/ArrayTool.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

/**
 * Array function class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ArrayTool
{

	/**
	 * ฟังก์ชั่น เรียงลำดับ array ตามชื่อฟิลด์
	 *
	 * @param pointer $array แอเรย์ที่ต้องการเรียงลำดับ
	 * @param string $sort_key (optional) คืย์ของ $array ที่ต้องการในการเรียง (default id)
	 * @param bool $sort_desc true=เรียงจากมากไปหาน้อย, false=เรียงจากน้อยไปหามาก (default false)
	 */
	public static function sort(&$array, $sort_key = 'id', $sort_desc = false)
	{
		if (!empty($array)) {
			$temp_array[key($array)] = array_shift($array);
			foreach ($array AS $key => $val) {
				$offset = 0;
				$found = false;
				foreach ($temp_array AS $tmp_key => $tmp_val) {
					$v1 = isset($val[$sort_key]) ? strtolower(self::toString('', $val[$sort_key])) : '';
					$v2 = isset($tmp_val[$sort_key]) ? strtolower(self::toString('', $tmp_val[$sort_key])) : '';
					if (!$found && $v1 > $v2) {
						$temp_array = array_merge((array)array_slice($temp_array, 0, $offset), array($key => $val), array_slice($temp_array, $offset));
						$found = true;
					}
					$offset++;
				}
				if (!$found) {
					$temp_array = array_merge($temp_array, array($key => $val));
				}
			}
			if ($sort_desc) {
				$array = array_reverse($temp_array);
			} else {
				$array = $temp_array;
			}
		}
	}

	/**
	 * เลือกรายการ array ที่มีข้อมูลที่กำหนด
	 *
	 * @param array $array
	 * @param string $where ข้อมูลที่ต้องการ
	 * @assert (array('one', 'One', 'two'), 'one') [==] array('one', 'One')
	 * @return array
	 */
	public static function filter($array, $where)
	{
		if ($where == '') {
			return $array;
		} else {
			$result = array();
			foreach ($array as $key => $value) {
				if (stripos(self::toString(' ', $value), $where) !== false) {
					$result[$key] = $value;
				}
			}
			return $result;
		}
	}

	/**
	 * แปลงแอเรย์ $array เป็น string คั่นด้วย $glue
	 *
	 * @param string $glue ตัวคั่นข้อมูล
	 * @param array $array แอเรย์ที่ต้องการนำมาเชื่อม
	 * @assert ('|', array('a' => 'A', 'b' => array('b', 'B'), 'c' => array('c' => array('c', 'C')))) [==] "A|b|B|c|C"
	 * @return string
	 */
	public static function toString($glue, $array)
	{
		if (is_array($array)) {
			$result = array();
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$result[] = self::toString($glue, $value);
				} else {
					$result[] = $value;
				}
			}
			return implode($glue, $result);
		} else {
			return $array;
		}
	}

	/**
	 * ลบรายการที่ id สามารถลบได้หลายรายการโดยคั่นแต่ละรายการด้วย ,
	 *
	 * @param array $array
	 * @param string|int $ids รายการที่ต้องการลบ 1 หรือ 1,2,3
	 * @assert (array(0, 1, 2, 3, 4, 5), '0,2') [==] array(1, 3, 4, 5)
	 * @return array คืนค่า array ใหม่หลังจากลบแล้ว
	 */
	public static function delete($array, $ids)
	{
		$temp = array();
		$ids = explode(',', $ids);
		foreach ($array as $id => $items) {
			if (!in_array($id, $ids)) {
				$temp[] = $items;
			}
		}
		return $temp;
	}

	/**
	 * ฟังก์ชั่นแยก $key และ $value ออกจาก array รองรับข้อมูลรูปแบบแอเรย์ย่อยๆ
	 *
	 * @param array $array array('key1' => 'value1', 'key2' => 'value2', array('key3' => 'value3', 'key4' => 'value4'))
	 * @param array $keys คืนค่า $key
	 * @param array $values คืนค่า $value
	 */
	public static function extract($array, &$keys, &$values)
	{
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				self::extract($array[$key], $keys, $values);
			} else {
				$keys[] = $key;
				$values[] = $value;
			}
		}
	}

	/**
	 * ฟังก์ชั่นรวมแอเรย์ แทนที่คีย์ก่อนหน้า
	 *
	 * @param array $a
	 * @param array|object $b
	 * @assert (array(1 => 1, 2 => 2, 3 => 'three'), array(1 => 'one', 2 => 'two')) [==] array(1 => 'one', 2 => 'two', 3 => 'three')
	 * @return array
	 */
	public static function replace($a, $b)
	{
		foreach ($b as $key => $value) {
			$a[$key] = $value;
		}
		return $a;
	}

	/**
	 * แปลงข้อความ serialize เป็นแอเรย์
	 *
	 * @param string $str serialize
	 * @return array
	 */
	public static function unserialize($str)
	{
		if ($str != '') {
			$datas = @unserialize($str);
		}
		return isset($datas) && is_array($datas) ? $datas : array();
	}
}