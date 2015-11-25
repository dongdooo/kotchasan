<?php
/**
 * index/models/module.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @link http://www.kotchasan.com/
 *
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Module;

/**
 * คลาสสำหรับโหลดรายการเมนูจากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Model
{

	/**
	 * อ่านรายชื่อโมดูลทั้งหมดที่ติดตั้งแล้ว
	 *
	 * @return array
	 */
	public static function getModules()
	{
		$result = array();
		$obj = new static;
		$sql = "SELECT `id` AS `module_id`, `module`, `owner`, `config` FROM `".$obj->tableWithPrefix('modules')."`";
		$obj->cache->cacheOn();
		foreach ($obj->db->customQuery($sql, true, array(), $obj->cache) as $item) {
			if (!empty($item['config'])) {
				$config = @unserialize($item['config']);
				if (!is_array($config)) {
					$save = array();
					foreach (explode("\n", $item['config']) As $value) {
						if (preg_match('/^(.*)=(.*)$/', $value, $match)) {
							$save[$match[1]] = trim($match[2]);
						}
					}
					$item['config'] = serialize($save);
					$obj->db->update($obj->tableWithPrefix('modules'), array('id', $item['module_id']), array('config' => $item['config']));
				}
				foreach (unserialize($item['config']) as $key => $value) {
					$item[$key] = $value;
				}
			}
			unset($item['config']);
			$result[] = (object)$item;
		}
		return $result;
	}
}