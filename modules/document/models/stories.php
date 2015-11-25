<?php
/**
 * @filesource document/models/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Stories;

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
		$qs[] = "D.`module_id`=:module_id";
		if (isset($_GET['cat']) && preg_match('/^[0-9,]+$/', $_GET['cat'], $match)) {
			$qs[] = "I.`category_id` IN ($match[0])";
		}
		$qs[] = "D.`language` IN (:language,'')";
		// query
		$sql1 = "FROM `".$obj->tableWithPrefix('index_detail')."` AS `D`";
		$sql1 .= " INNER JOIN `".$obj->tableWithPrefix('index')."` AS `I` ON I.`id`=D.`id` AND I.`module_id`=D.`module_id` AND I.`index`=0 AND I.`published`=1 AND I.`published_date`<=:published_date";
		$sql1 .= " LEFT JOIN `".$obj->tableWithPrefix('user')."` AS `U` ON U.`id`=I.`member_id`";
		$sql1 .= ' WHERE '.implode(' AND ', $qs);
		$datas = array(
			':module_id' => (int)$index->module_id,
			':language' => \Language::name(),
			':published_date' => \Datetool::mktimeToSqlDate(\Kotchasan::$mktime)
		);
		// รายการทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` $sql1";
		$obj->cache->cacheOn();
		$result = $obj->db->customQuery($sql, true, $datas, $obj->cache);
		$obj->count = empty($result) ? 0 : $result[0]['count'];
		$start = 0;
		// query รายการบทความ
		$sql = "SELECT I.`id`,I.`alias`,I.`last_update`,I.`create_date`,I.`comment_date`,I.`visited`,I.`comments`,I.`picture`,I.`member_id`";
		$sql .= ",D.`topic`,D.`description`,U.`status`,U.`displayname`,U.`email`";
		$sql .= " $sql1 ORDER BY I.`last_update` DESC, I.`id` DESC LIMIT $start,".$index->list_per_page;
		$obj->cache->cacheOn();
		return $obj->db->customQuery($sql, false, $datas, $obj->cache);
	}
}