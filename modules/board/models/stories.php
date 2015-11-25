<?php
/**
 * @filesource board/models/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Stories;

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
		// query หลัก
		$qs = array();
		$qs[] = "Q.`module_id`=:module_id";
		if (isset($_GET['cat']) && preg_match('/^[0-9,]+$/', $_GET['cat'], $match)) {
			$qs[] = "Q.`category_id` IN ($match[0])";
		}
		$where = implode(' AND ', $qs);
		// อ่านจำนวนกระทู้ทั้งหมด
		$sql = "SELECT COUNT(*) AS `count` FROM `".$obj->tableWithPrefix('board_q')."` AS Q WHERE $where";
		$datas = array(
			':module_id' => (int)$index->module_id,
		);
		$obj->cache->cacheOn();
		$result = $obj->db->customQuery($sql, true, $datas, $obj->cache);
		$obj->count = empty($result) ? 0 : $result[0]['count'];
		$pins = $obj->getPin($where, $datas);
		$list = $obj->getBoard($where, $datas, 0, $index->list_per_page - sizeof($pins));
		return array_merge($pins, $list);
	}

	public function getPin($where, $datas)
	{
		$sql = "SELECT Q.*,U1.`status`,U2.`status` AS `replyer_status`";
		$sql .= ",(CASE WHEN Q.`comment_date` > 0 THEN Q.`comment_date` ELSE Q.`last_update` END) AS `d`";
		$sql .= ",(CASE WHEN ISNULL(U1.`id`) THEN Q.`email` WHEN U1.`displayname`='' THEN U1.`email` ELSE U1.`displayname` END) AS `sender`";
		$sql .= ",(CASE WHEN ISNULL(U2.`id`) THEN Q.`commentator` WHEN U2.`displayname`='' THEN U2.`email` ELSE U2.`displayname` END) AS `commentator`";
		$sql .= " FROM `".$this->tableWithPrefix('board_q')."` AS Q";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('user')."` AS U1 ON U1.`id`=Q.`member_id`";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('user')."` AS U2 ON U2.`id`=Q.`commentator_id`";
		$sql .= " WHERE Q.`pin`='1' AND $where ORDER BY Q.`id` DESC";
		$this->cache->cacheOn();
		return $this->db->customQuery($sql, false, $datas, $this->cache);
	}

	public function getBoard($where, $datas, $start, $count)
	{
		$sql = "SELECT Q.*,U1.`status`,U2.`status` AS `replyer_status`";
		$sql .= ",(CASE WHEN Q.`comment_date` > 0 THEN Q.`comment_date` ELSE Q.`last_update` END) AS `d`";
		$sql .= ",(CASE WHEN ISNULL(U1.`id`) THEN Q.`email` WHEN U1.`displayname`='' THEN U1.`email` ELSE U1.`displayname` END) AS `sender`";
		$sql .= ",(CASE WHEN ISNULL(U2.`id`) THEN Q.`commentator` WHEN U2.`displayname`='' THEN U2.`email` ELSE U2.`displayname` END) AS `commentator`";
		$sql .= " FROM `".$this->tableWithPrefix('board_q')."` AS Q";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('user')."` AS U1 ON U1.`id`=Q.`member_id`";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('user')."` AS U2 ON U2.`id`=Q.`commentator_id`";
		$sql .= " WHERE $where AND Q.`pin`='0' ORDER BY `d` DESC LIMIT $start,$count";
		$this->cache->cacheOn();
		return $this->db->customQuery($sql, false, $datas, $this->cache);
	}
}