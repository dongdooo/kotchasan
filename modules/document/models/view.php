<?php
/**
 * @filesource document/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

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
		// query
		$sql = "SELECT I.`id`,I.`module_id`,I.`category_id`,I.`picture`,I.`create_date`,I.`last_update`,I.`visited`,I.`visited_today`,I.`comments`,I.`alias`,I.`can_reply`,I.`published`,I.`member_id`";
		$sql .= ",D.`topic`,D.`description`,D.`detail`,D.`keywords`,D.`relate`,U.`displayname`,U.`email`";
		$sql .= " FROM `".$obj->tableWithPrefix('index')."` AS I";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('index_detail')."` AS D ON D.`id`=I.`id` AND D.`module_id`=I.`module_id` AND D.`language` IN (:language,'')";
		$sql .= " INNER JOIN `".$obj->tableWithPrefix('user')."` AS U ON U.`id`=I.`member_id`";
		$sql .= " WHERE ";
		$where = array(':language' => \Language::name());
		if (empty($_GET['document'])) {
			$where[':id'] = (int)$_GET['id'];
			$sql .= "I.`id`=:id";
		} else {
			$where[':alias'] = $_GET['document'];
			$sql .= "I.`alias`=:alias";
		}
		$sql .= " AND I.`index`=0 LIMIT 1";
		$obj->cache->cacheOn(false);
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