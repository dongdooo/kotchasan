<?php
/**
 * @filesource board/models/module.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Module;

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
		$qs = array();
		$qs[] = 'D.`module_id`=:module_id';
		// หมวดหมู่
		$cat_num = 0;
		if (isset($_GET['cat']) && preg_match('/^[0-9,]+$/', $_GET['cat'], $match)) {
			$cat_num = sizeof(explode(',', $match[0]));
			$qs[] = "C.`category_id` IN ($match[0])";
		}
		$qs[] = "D.`language` IN (:language, '')";
		// query ข้อมูลโมดูล
		$sql = 'SELECT D.`topic` AS `title`,D.`detail`,D.`keywords`';
		$sql .= ',(SELECT COUNT(*) FROM `'.$obj->tableWithPrefix('category').'` WHERE `module_id`=D.`module_id`) AS `categories`';
		if ($cat_num == 1) {
			$sql .= ',C.`category_id`,C.`topic`,C.`detail` AS `description`,C.`icon`,C.`config`';
		} else {
			$sql .= ',D.`topic`,D.`description`';
		}
		$sql .= ' FROM `'.$obj->tableWithPrefix('index_detail').'` AS D';
		if ($cat_num == 1) {
			$sql .= ' INNER JOIN `'.$obj->tableWithPrefix('category').'` AS C ON C.`module_id`=D.`module_id`';
		}
		$sql .= ' INNER JOIN `'.$obj->tableWithPrefix('index')."` AS I ON I.`id`=D.`id` AND I.`index`=1 AND I.`module_id`=D.`module_id` AND I.`language`=D.`language`";
		$sql .= ' WHERE '.implode(' AND ', $qs).' LIMIT 1';
		$where = array(
			':module_id' => (int)$index->module_id,
			':language' => \Language::name()
		);
		$obj->cache->cacheOn();
		$result = $obj->db->customQuery($sql, true, $where, $obj->cache);
		if (empty($result)) {
			$index = null;
		} else {
			foreach ($result[0] as $key => $value) {
				$index->$key = $value;
			}
			if ($cat_num == 1) {
				$index->topic = \String::ser2Str($index->topic);
				$index->description = \String::ser2Str($index->description);
				$index->config = @unserialize($index->config);
				if (is_array($index->config)) {
					foreach ($index->config as $key => $value) {
						$index->$key = $value;
					}
				}
				unset($index->config);
			}
		}
		return $index;
	}
}