<?php
/*
 * @filesource Kotchasan/Database/QueryBuilder.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Database;

use \Kotchasan\Database\Query;
use \Kotchasan\Database\Driver;

/**
 * SQL Query builder
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 *
 * @setupParam new Query
 */
class QueryBuilder extends Query
{
	/**
	 * ส่งออกผลลัพท์เป็น Array
	 *
	 * @var bool
	 */
	private $toArray = false;

	/**
	 * Class constructor
	 *
	 * @param object $db database driver
	 */
	public function __construct(Driver $db)
	{
		$this->db = $db;
		$this->values = array();
	}

	/**
	 * เปิดการใช้งานแคช
	 * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล
	 * @param bool $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
	 * @return \static
	 */
	public function cacheOn($auto_save = true)
	{
		$this->cache->cacheOn($auto_save);
		return $this;
	}

	/**
	 * ประมวลผลคำสั่ง SQL และคืนค่าจำนวนแถวของผลลัพท์
	 *
	 * @return int จำนวนแถว
	 */
	public function count()
	{
		if (!isset($this->sqls['select'])) {
			$this->selectCount('* count');
		}
		$result = $this->toArray()->execute();
		return sizeof($result) == 1 ? (int)$result[0]['count'] : 0;
	}

	/**
	 *
	 * @param type $table
	 */
	public function delete($table)
	{

	}

	/**
	 * ประมวลผลคำสั่ง SQL
	 *
	 * @return array ของผลลัพท์ ไม่พบข้อมูล คืนค่าแอเรย์ว่าง
	 */
	public function execute()
	{
		$result = $this->db->execQuery($this->sqls, $this->values);
		if ($this->toArray) {
			$this->toArray = false;
		} elseif (is_array($result)) {
			foreach ($result as $i => $items) {
				$result[$i] = (object)$items;
			}
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL ข้อมูลต้องการผลลัพท์เพียงรานการเดียว
	 *
	 * @param string $fields (option) รายชื่อฟิลด์ field1, field2, field3, ....
	 * @return object|bool คืนค่าผลลัพท์ที่พบเพียงรายการเดียว ไม่พบข้อมูลคืนค่า false
	 */
	public function first($fields = '*')
	{
		$fields = $fields == '*' || func_num_args() == 1 ? $fields : func_get_args();
		call_user_func(array($this, 'select'), $fields);
		$this->sqls['limit'] = 1;
		$result = $this->execute();
		if (sizeof($result) == 1) {
			if ($this->toArray) {
				$this->toArray = false;
				$result = $result[0];
			} else {
				$result = (object)$result[0];
			}
		} else {
			$result = false;
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง FROM
	 *
	 * @param string $tables ชื่อตาราง table1, table2, table3, ....
	 * @assert select()->from('user')->text() [==] "SELECT * FROM `user`"
	 * @assert select()->from('user a', 'user b')->text() [==] "SELECT * FROM `user` AS `a`, `user` AS `b`"
	 * @return \static
	 */
	public function from($tables)
	{
		$qs = array();
		foreach (func_get_args() as $table) {
			$qs[] = $this->quoteTableName($table);
		}
		if (sizeof($qs) > 0) {
			$this->sqls['from'] = implode(', ', $qs);
		}
		return $this;
	}

	/**
	 *
	 * @param array $fields
	 * @return \static
	 */
	public function groupBy($fields)
	{
		$args = is_array($fields) ? $fields : func_get_args();
		$sqls = array();
		foreach ($args as $item) {
			if (preg_match('/^(([a-z0-9]+)\.)?([a-z0-9_]+)?$/i', $item, $match)) {
				$sqls[] = "$match[1]`$match[3]`";
			}
		}
		if (sizeof($sqls) > 0) {
			$this->sqls['group'] = implode(', ', $sqls);
		}
		return $this;
	}

	public function having()
	{

	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง insert into
	 *
	 * @param string $table ชื่อตาราง
	 * @param array $datas รูปแบบ array(key1=>value1, key2=>value2)
	 * @assert insert('user', array('id' => 1, 'name' => 'test'))->text() [==] "INSERT INTO `user` (`id`, `name`) VALUES (:id, :name)"
	 * @return \static
	 */
	public function insert($table, $datas)
	{
		$this->sqls['function'] = 'query';
		$this->sqls['insert'] = $this->tableWithPrefix($table);
		$keys = array();
		foreach ($datas as $key => $value) {
			$this->sqls['values'][$key] = $value;
		}
		return $this;
	}

	/**
	 * สร้างคำสั่ง JOIN
	 *
	 * @param string $table ชื่อตารางที่ต้องการ join เช่น table alias
	 * @param string $type เข่น INNER OUTER LEFT RIGHT
	 * @param mixed $on query string หรือ array
	 * @assert join('user U', 'INNER', 1)->text() [==] " INNER JOIN `user` AS U ON `id`=1"
	 * @assert join('user U', 'INNER', array('U.id', 'A.id'))->text() [==] " INNER JOIN `user` AS U ON U.`id`=A.`id`"
	 * @assert join('user U', 'INNER', array('U.id', '=', 'A.id'))->text() [==] " INNER JOIN `user` AS U ON U.`id`=A.`id`"
	 * @assert join('user U', 'INNER', array('id', '=', 1))->text() [==] " INNER JOIN `user` AS U ON `id`=1"
	 * @assert join('user U', 'INNER', array(array('U.id', 'A.id'), array('U.id', 'A.id')))->text() [==] " INNER JOIN `user` AS U ON U.`id`=A.`id` AND U.`id`=A.`id`"
	 * @return \static
	 */
	public function join($table, $type, $on)
	{
		$ret = $this->buildJoin($table, $type, $on);
		if (is_array($ret)) {
			$this->sqls['join'][] = $ret[0];
			$this->values = ArrayTool::replace($this->values, $ret[1]);
		} else {
			$this->sqls['join'][] = $ret;
		}
		return $this;
	}

	/**
	 * จำกัดผลลัพท์ และกำหนดรายการเริ่มต้น
	 *
	 * @param int $count จำนวนผลลัท์ที่ต้องการ
	 * @param int $start รายการเริ่มต้น
	 * @assert limit(10)->text() [==] " LIMIT 10"
	 * @assert limit(10, 1)->text() [==] " LIMIT 1,10"
	 * @return \static
	 */
	public function limit($count, $start = 0)
	{
		if (!empty($start)) {
			$this->sqls['start'] = (int)$start;
		}
		$this->sqls['limit'] = (int)$count;
		return $this;
	}

	/**
	 * สร้าง query เรียงลำดับ
	 *
	 * @param mixed $sort array('field ASC','field DESC') หรือ 'field ASC', 'field DESC', ....
	 * @assert order('id', 'id ASC')->text() [==] " ORDER BY `id`, `id` ASC"
	 * @assert order('id ASC')->text() [==] " ORDER BY `id` ASC"
	 * @assert order('user.id DESC')->text() [==] " ORDER BY `user`.`id` DESC"
	 * @assert order('id ASCD')->text() [==] ""
	 * @return \static
	 */
	public function order($sorts)
	{
		$sorts = is_array($sorts) ? $sorts : func_get_args();
		$ret = $this->buildOrder($sorts);
		if (!empty($ret)) {
			$this->sqls['order'] = $ret;
		}
		return $this;
	}

	/**
	 * SELECT `field1`, `field2`, `field3`, ....
	 *
	 * @param string $fields (option) รายชื่อฟิลด์ field1, field2, field3, ....
	 * @assert select('id', 'email name')->from('user')->where('`id`=1')->text() [==] "SELECT `id`,`email` AS `name` FROM `user` WHERE `id`=1"
	 * @assert select()->text()  [==] "SELECT *"
	 * @return \static
	 */
	public function select($fields = '*')
	{
		$qs = array();
		if ($fields == '*') {
			$qs[] = '*';
		} else {
			foreach (func_get_args() AS $item) {
				$qs[] = $this->buildSelect($item);
			}
		}
		if (sizeof($qs) > 0) {
			$this->sqls['function'] = 'customQuery';
			$this->sqls['select'] = implode(',', $qs);
		}
		return $this;
	}

	/**
	 * สร้าง query สำหรับการนับจำนวน record
	 *
	 * @param mixed $fileds (option) 'field alias'
	 * @assert selectCount()->from('user')->text() [==] "SELECT COUNT(*) AS `count` FROM `user`"
	 * @assert selectCount('id ids')->from('user')->text() [==] "SELECT COUNT(`id`) AS `ids` FROM `user`"
	 * @assert selectCount('id ids', 'field alias')->from('user')->text() [==] "SELECT COUNT(`id`) AS `ids`, COUNT(`field`) AS `alias` FROM `user`"
	 * @return \static
	 */
	public function selectCount($fileds = '* count')
	{
		$args = func_num_args() == 0 ? array($fileds) : func_get_args();
		$sqls = array();
		foreach ($args AS $item) {
			if (preg_match('/^([a-z0-9_\*]+)([\s]+([a-z0-9_]+))?$/', trim($item), $match)) {
				$sqls[] = 'COUNT('.($match[1] == '*' ? '*' : '`'.$match[1].'`').')'.(isset($match[3]) ? ' AS `'.$match[3].'`' : '');
			}
		}
		if (sizeof($sqls) > 0) {
			$this->sqls['function'] = 'customQuery';
			$this->sqls['select'] = implode(', ', $sqls);
		}
		return $this;
	}

	/**
	 * SELECT DISTINCT `field1`, `field2`, `field3`, ....
	 *
	 * @param string $fields (option) รายชื่อฟิลด์ field1, field2, field3, ....
	 * @assert selectDistinct('id')->from('user')->text() [==] "SELECT DISTINCT `id` FROM `user`"
	 * @return \static
	 */
	public function selectDistinct($fields = '*')
	{
		call_user_func(array($this, 'select'), func_get_args());
		$this->sqls['select'] = 'DISTINCT '.$this->sqls['select'];
		return $this;
	}

	/**
	 * UPDATE ..... SET
	 *
	 * @param array $datas รูปแบบ array(key1=>value1, key2=>value2)
	 * @assert update('user')->set(array('key1'=>'value1', 'key2'=>2))->where(1)->text() [==] "UPDATE `user` SET `key1`=:key1, `key2`=:key2 WHERE `id`=1"
	 * @return \static
	 */
	public function set($datas)
	{
		$keys = array();
		foreach ($datas as $key => $value) {
			$this->sqls['set'][$key] = "`$key`=:$key";
			$this->sqls['values'][":$key"] = $value;
		}
		return $this;
	}

	/**
	 * คืนค่าข้อมูลเป็น Array
	 * ฟังก์ชั่นนี้ใช้เรียกก่อนการสอบถามข้อมูล
	 *
	 * @return \static
	 */
	public function toArray()
	{
		$this->toArray = true;
		return $this;
	}

	public function union()
	{

	}

	/**
	 * UPDATE
	 *
	 * @param string $table ชื่อตาราง
	 * @assert update('user')->set(array('key1'=>'value1', 'key2'=>2))->where(array(array('id', 1), array('id', 1)))->text() [==] "UPDATE `user` SET `key1`=:key1, `key2`=:key2 WHERE `id`=1 AND `id`=1"
	 * @return \static
	 */
	public function update($table)
	{
		$this->sqls['function'] = 'query';
		$this->sqls['update'] = $this->quoteTableName($table);
		return $this;
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง WHERE
	 *
	 * @param mixed $condition query string หรือ array
	 * @assert where(1)->text() [==] " WHERE `id`=1"
	 * @assert where(array('id', 1))->text() [==] " WHERE `id`=1"
	 * @assert where(array('id', '1'))->text() [==] " WHERE `id`='1'"
	 * @assert where(array('date', '2016-1-1 30:30'))->text() [==] " WHERE `date`='2016-1-1 30:30'"
	 * @assert where(array('id', '=', 1))->text() [==] " WHERE `id`=1"
	 * @assert where('`id`=1 OR (SELECT ....)')->text() [==] " WHERE `id`=1 OR (SELECT ....)"
	 * @assert where(array('id', '=', 1))->text() [==] " WHERE `id`=1"
	 * @assert where(array('id', 'IN', array(1, 2, '3')))->text() [==] " WHERE `id` IN (:id0, :id1, :id2)"
	 * @return \static
	 */
	public function where($condition, $oprator = 'AND', $id = 'id')
	{
		$ret = $this->buildWhere($condition, $oprator, $id);
		if (is_array($ret)) {
			$this->sqls['where'] = $ret[0];
			$this->values = ArrayTool::replace($this->values, $ret[1]);
		} else {
			$this->sqls['where'] = $ret;
		}
		return $this;
	}
}