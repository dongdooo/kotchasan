<?php
/**
 * @filesource document/models/categories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Categories;

/**
 * อ่านข้อมูลโมดูล
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class Model extends \Model
{

	public static function get($index)
	{
		$obj = new static;
		$sql = "SELECT * FROM `".$obj->tableWithPrefix('category')."`";
		$sql .= " WHERE `module_id`=:module_id AND `published`='1' ORDER BY `category_id` DESC";
		$where = array(
			':module_id' => (int)$index->module_id,
		);
		$obj->cache->cacheOn();
		$result = array();
		foreach ($obj->db->customQuery($sql, true, $where, $obj->cache) as $item) {
			$item['topic'] = \Gcms::ser2Str($item, 'topic');
			$item['detail'] = \Gcms::ser2Str($item, 'detail');
			$item['icon'] = \Gcms::ser2Str($item, 'icon');
			$config = @unserialize($item['config']);
			if (!is_array($config)) {
				$save = array();
				foreach (explode("\n", $item['config']) as $value) {
					if (preg_match('/^(.*)=(.*)$/', $value, $match)) {
						$save[$match[1]] = trim($match[2]);
					}
				}
				$item['config'] = serialize($save);
				$obj->db->update($obj->tableWithPrefix('category'), array('id', $item['id']), array('config' => $item['config']));
			}
			foreach (unserialize($item['config']) as $key => $value) {
				$item[$key] = $value;
			}
			$result[] = (object)$item;
		}
		return $result;
	}
}