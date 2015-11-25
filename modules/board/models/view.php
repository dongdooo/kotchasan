<?php
/**
 * @filesource board/models/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\View;

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
		$sql = "SELECT I.*,U.`status`,U.`id` AS `member_id`";
		$sql .= " ,C.`topic` AS `category`,C.`detail` AS `cat_tooltip`,C.`config`";
		$sql .= " ,(CASE WHEN ISNULL(U.`id`) THEN I.`email` WHEN U.`displayname`='' THEN U.`email` ELSE U.`displayname` END) AS `displayname` ";
		$sql .= " FROM `".$obj->tableWithPrefix('board_q')."` AS I ";
		$sql .= " LEFT JOIN `".$obj->tableWithPrefix('user')."` AS U ON U.`id`=I.`member_id` ";
		$sql .= " LEFT JOIN `".$obj->tableWithPrefix('category')."` AS C ON C.`category_id`=I.`category_id` AND C.`module_id`=I.`module_id` ";
		$sql .= " WHERE I.`id`=:id LIMIT 1";
		$datas = array(
			':id' => isset($_GET['wbid']) ? $_GET['wbid'] : (isset($_GET['id']) ? $_GET['id'] : 0)
		);
		$obj->cache->cacheOn(false);
		$result = $obj->db->customQuery($sql, true, $datas, $obj->cache);
		if (sizeof($result) == 1) {
			$result[0]['visited'] ++;
			$obj->db->update($obj->tableWithPrefix('board_q'), array('id', $result[0]['id']), array('visited' => $result[0]['visited']));
			$obj->cache->save($result);
			$config = @unserialize($result[0]['config']);
			if (is_array($config)) {
				$result[0] = \Arraytool::replace($result[0], $config);
			}
			unset($result[0]['config']);
			return (object)$result[0];
		} else {
			return null;
		}
	}
}