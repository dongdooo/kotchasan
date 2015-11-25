<?php
/**
 * index/models/index.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @link http://www.kotchasan.com/
 *
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

/**
 * คลาสสำหรับโหลดรายการเมนูจากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Model
{

	public static function getIndex($module_id)
	{
		$obj = new static;
		$obj->cache->cacheOn(false);
		$sql = "SELECT I.`id`,M.`module`,D.`topic`,D.`description`,D.`keywords`,D.`detail`,I.`visited`";
		$sql .= " FROM `".$obj->tableWithPrefix('modules')."` AS M";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('index')."` AS I ON I.`module_id`=M.`id`";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('index_detail')."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`";
		$sql .= " WHERE I.`index`=1 AND I.`module_id`=:module_id AND I.`published`=1 AND I.`published_date`<=:published_date LIMIT 1";
		$where = array(
			':module_id' => (int)$module_id,
			':published_date' => \Datetool::mktimeToSqlDate(\Kotchasan::$mktime)
		);
		$result = $obj->db->customQuery($sql, true, $where, $obj->cache);
		if (sizeof($result) == 1) {
			$result[0]['visited'] ++;
			$obj->db->update($obj->tableWithPrefix('index'), array('id', $result[0]['id']), array('visited' => $result[0]['visited']));
			$obj->cache->save($result);
			return (object)$result[0];
		} else {
			return null;
		}
	}

	public static function getIndexById($id)
	{
		$obj = new static;
		$obj->cache->cacheOn(false);
		$sql = "SELECT I.`id`,I.`module_id`,M.`module`,D.`topic`,D.`description`,D.`keywords`,D.`detail`,I.`visited`";
		$sql .= " FROM `".$obj->tableWithPrefix('index')."` AS I";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('modules')."` AS M ON M.`id`=I.`module_id`";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('index_detail')."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language`=I.`language`";
		$sql .= " WHERE I.`id`=:id AND I.`index`='1' AND I.`published`='1' AND I.`published_date`<=:published_date LIMIT 1";
		$where = array(
			':id' => (int)$id,
			':published_date' => \Datetool::mktimeToSqlDate(\Kotchasan::$mktime)
		);
		$result = $obj->db->customQuery($sql, true, $where, $obj->cache);
		if (sizeof($result) == 1) {
			$result[0]['visited'] ++;
			$obj->db->update($obj->tableWithPrefix('index'), array('id', $result[0]['id']), array('visited' => $result[0]['visited']));
			$obj->cache->save($result);
			return (object)$result[0];
		} else {
			return null;
		}
	}
}