<?php
/*
 * @filesource Kotchasan/Database/Query.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Database;

use \Kotchasan\Database;
use \Kotchasan\Database\Driver;
use \Kotchasan\ArrayTool;

/**
 * Database Query (base class)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class Query extends \Kotchasan\KBase
{
	/**
	 * database connection
	 *
	 * @var Driver
	 */
	private $db;
	/**
	 * ตัวแปรเก็บคำสั่ง SQL
	 *
	 * @var array
	 */
	protected $sqls;

	/**
	 * Class constructor
	 */
	public function __construct($conn)
	{
		$this->db = Database::create($conn);
	}

	/**
	 * อ่าน database connection
	 *
	 * @return Driver
	 */
	public function db()
	{
		return $this->db;
	}

	/**
	 * อ่านค่ากำหนดของฐานข้อมูล
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function getSetting($key)
	{
		if (isset($this->db->settings->$key)) {
			return $this->db->settings->$key;
		}
	}

	/**
	 * ฟังก์ชั่นสำหรับจัดกลุ่มคำสั่ง และ เชื่อมแต่ละกลุ่มด้วย AND
	 *
	 * @param array $params คำสั่ง รูปแบบ array('field1', 'condition', 'field2')
	 * @assert (array('id', 1), array('id', '=' , 1), array('id', array(1,2,'3'))) [==] "(`id` = 1 AND `id` = 1 AND `id` IN (1, 2, '3'))"
	 * @return string query ภายใต้ ()
	 */
	protected function groupAnd($params)
	{
		if (func_num_args() > 1) {
			$params = func_get_args();
		}
		$sqls = array();
		foreach ($params as $i => $item) {
			$sqls[] = $this->buildValue($item);
		}
		return '('.implode(' AND ', $sqls).')';
	}

	/**
	 * ฟังก์ชั่นสำหรับจัดกลุ่มคำสั่ง และ เชื่อมแต่ละกลุ่มด้วย OR
	 *
	 * @param array $params คำสั่ง รูปแบบ array('field1', 'condition', 'field2')
	 * @assert (array('id', 1), array('id', '=' , 1), array('id', array(1,2,'3'))) [==] "(`id` = 1 OR `id` = 1 OR `id` IN (1, 2, '3'))"
	 * @return string
	 */
	protected function groupOr($params)
	{
		if (func_num_args() > 1) {
			$params = func_get_args();
		}
		$sqls = array();
		foreach ($params as $i => $item) {
			$sqls[] = $this->buildValue($item);
		}
		return '('.implode(' OR ', $sqls).')';
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง SQL เป้นข้อความ
	 *
	 * @return string
	 */
	public function text()
	{
		if (empty($this->sqls)) {
			return '';
		} else {
			return $this->db->makeQuery($this->sqls);
		}
	}

	/**
	 * ฟังก์ชั่นอ่านชื่อตารางและชื่อรอง และใส่ ` ครอบชื่อตารางด้วย
	 *
	 * @param string $table ชื่อตารางตามที่กำหนดใน settings/datasbase.php
	 * @assert ('user U') [==] "`user` AS U"
	 * @assert ('user as U1') [==] "`user` AS U1"
	 * @assert ('user username') [==] "`user` AS `username`"
	 * @assert ('user AS username') [==] "`user` AS `username`"
	 * @assert ('user') [==] "`user`"
	 * @return string
	 */
	protected function quoteTableName($table)
	{
		if (preg_match('/^([a-zA-Z0-9_]+)(\s+(as|AS))?[\s]+([A-Z0-9]{1,2})$/', $table, $match)) {
			$table = '`'.$this->tableWithPrefix($match[1]).'` AS '.$match[4];
		} elseif (preg_match('/^([a-zA-Z0-9_]+)(\s+(as|AS))?[\s]+([a-zA-Z0-9]+)$/', $table, $match)) {
			$table = '`'.$this->tableWithPrefix($match[1]).'` AS `'.$match[4].'`';
		} else {
			$table = '`'.$this->tableWithPrefix($table).'`';
		}
		return $table;
	}

	/**
	 * ฟังก์ชั่นอ่านชื่อตารางจากการตั้งค่าฐานข้อมุล
	 *
	 * @param string $table ชื่อตารางตามที่กำหนดใน settings/datasbase.php
	 * @assert ('user') [==] "user"
	 * @return string ชื่อตารางรวม prefix ถ้าไม่มีชื่อกำหนดไว้ จะคืนค่า prefix ตามด้วย $table
	 */
	public function tableWithPrefix($table)
	{
		$prefix = empty($this->db->settings->prefix) ? '' : $this->db->settings->prefix.'_';
		return $prefix.(isset($this->db->tables->$table) ? $this->db->tables->$table : $table);
	}

	/**
	 * ฟังก์ชั่นสร้าง query string สำหรับคำสั่ง SELECT
	 *
	 * @param string $fields
	 * @assert ('0 as id') [==] "0 AS `id`"
	 * @assert ("'document' as module") [==] "'document' AS `module`"
	 * @assert ('user.id user_id') [==] "`user`.`id` AS `user_id`"
	 * @assert ('user.id as user_id') [==] "`user`.`id` AS `user_id`"
	 * @assert ('U.id') [==] "U.`id`"
	 * @assert ('V.email invite') [==] "V.`email` AS `invite`"
	 * @assert ("(SELECT `name` FROM `user`) name") [==] "(SELECT `name` FROM `user`) AS `name`"
	 * @assert (array('0 id', 'user', 'user.id user_id', "'document' module", '(...) name')) [==] "0 AS `id`, `user`, `user`.`id` AS `user_id`, 'document' AS `module`, (...) AS `name`"
	 * @return string
	 */
	protected function buildSelect($fields)
	{
		if (is_array($fields)) {
			// multiples
			$rets = array();
			foreach ($fields AS $item) {
				$rets[] = $this->buildSelect($item);
			}
			$ret = implode(', ', $rets);
		} else {
			if (preg_match('/^(.*?)\((.*)\)(([\s]+as)?[\s]+`?([a-z0-9_]+)`?)$/i', $fields, $match)) {
				// (...) alias
				$ret = "$match[1]($match[2]) AS `$match[5]`";
			} elseif (preg_match('/^([0-9]+)([\s]+as)?[\s]+([a-z0-9_]+)$/i', $fields, $match)) {
				// 0 as alias
				$ret = $match[1].' AS `'.$match[3].'`';
			} elseif (preg_match('/^([A-Z0-9]{1,2})\.([\*a-z0-9_]+)(([\s]+as)?[\s]+([a-z0-9_]+))?$/i', $fields, $match)) {
				// U.id alias
				$ret = $match[1].'.'.($match[2] == '*' ? '*' : '`'.$match[2].'`').(isset($match[5]) ? ' AS `'.$match[5].'`' : '');
			} elseif (preg_match('/^([a-z0-9_]+)\.([\*a-z0-9_]+)(([\s]+as)?[\s]+([a-z0-9_]+))?$/i', $fields, $match)) {
				// table.field alias
				$ret = '`'.$match[1].'`.'.($match[2] == '*' ? '*' : '`'.$match[2].'`').(isset($match[5]) ? ' AS `'.$match[5].'`' : '');
			} elseif (preg_match('/^([a-z0-9_]+)([\s]+as)?[\s]+([a-z0-9_]+)$/i', $fields, $match)) {
				// table.field
				$ret = '`'.$match[1].'` AS `'.$match[3].'`';
			} elseif (preg_match('/([a-z0-9_]+)/i', $fields, $match)) {
				// field name เช่น id
				$ret = '`'.$fields.'`';
			}
		}
		return $ret;
	}

	/**
	 * สร้างคำสั่ง JOIN
	 *
	 * @param string $table ชื่อตารางต้องมี alias ด้วย
	 * @param string $type เข่น INNER OUTER LEFT RIGHT
	 * @param mixed $on query string หรือ array
	 * @assert ('user U', 'INNER', 1) [==] " INNER JOIN `user` AS U ON `id`=1"
	 * @assert ('user U', 'INNER', array('U.id', 'A.id')) [==] " INNER JOIN `user` AS U ON U.`id`=A.`id`"
	 * @assert ('user U', 'INNER', array('U.id', '=', 'A.id')) [==] " INNER JOIN `user` AS U ON U.`id`=A.`id`"
	 * @assert ('user U', 'INNER', array('id', '=', 1)) [==] " INNER JOIN `user` AS U ON `id`=1"
	 * @assert ('user U', 'INNER', array(array('U.id', 'A.id'), array('U.id', 'A.id'))) [==] " INNER JOIN `user` AS U ON U.`id`=A.`id` AND U.`id`=A.`id`"
	 * @return string ถ้าไม่มี alias คืนค่าว่าง
	 */
	protected function buildJoin($table, $type, $on)
	{
		$ret = $this->buildWhere($on);
		$sql = is_array($ret) ? $ret[0] : $ret;
		if (preg_match('/^([a-zA-Z0-9_]+)([\s]+(as|AS))?[\s]+([A-Z0-9]{1,2})$/', $table, $match)) {
			$sql = ' '.$type.' JOIN `'.$this->tableWithPrefix($match[1]).'` AS '.$match[4].' ON '.$sql;
		} elseif (preg_match('/^([a-z0-9_]+)([\s]+as)?[\s]+([a-z0-9_]+)$/i', $table, $match)) {
			$sql = ' '.$type.' JOIN `'.$this->tableWithPrefix($match[1]).'` AS `'.$match[3].'` ON '.$sql;
		} else {
			$sql = ' '.$type.' JOIN '.$table.' ON '.$sql;
		}
		if (is_array($ret)) {
			return array($sql, $ret[1]);
		} else {
			return $sql;
		}
	}

	/**
	 * สร้าง query เรียงลำดับ
	 *
	 * @param mixed $fields array('field ASC','field DESC') หรือ 'field ASC', 'field DESC', ....
	 * @assert (array('id', 'id ASC')) [==] "`id`, `id` ASC"
	 * @assert ('id ASC') [==] "`id` ASC"
	 * @assert ('user.id DESC') [==] "`user`.`id` DESC"
	 * @assert ('U.id DESC') [==] "U.`id` DESC"
	 * @assert ('id ASCD') [==] ""
	 * @return string
	 */
	protected function buildOrder($fields)
	{
		$sqls = array();
		foreach ((array)$fields as $item) {
			if (preg_match('/^([A-Z0-9]{1,2}\.)([a-z0-9_]+)([\s]{1,}(ASC|DESC))?$/i', $item, $match)) {
				// U.id DESC
				$sqls[] = $match[1].'`'.$match[2].'`'.(isset($match[4]) ? " $match[4]" : '');
			} elseif (preg_match('/^([a-z0-9_]+)(\.([a-z0-9_]+))?(([\s]+)?(ASC|DESC))?$/i', $item, $match)) {
				// field.id DESC
				$sqls[] = '`'.$match[1].'`'.(empty($match[3]) ? '' : '.`'.$match[3].'`').(isset($match[6]) ? " $match[6]" : '');
			}
		}
		return implode(', ', $sqls);
	}

	/**
	 * แปลงข้อความสำหรับชื่อฟิลด์หรือชื่อตาราง
	 *
	 * @param string $name
	 * @assert (1) [===] 1
	 * @assert ("*") [==] "*"
	 * @assert ("field") [==] "`field`"
	 * @assert ("table.field") [==] "`table`.`field`"
	 * @assert ("table.field alias") [==] "`table`.`field` AS `alias`"
	 * @assert ("U.id  user_id") [==] "U.`id` AS `user_id`"
	 * @assert ("U.id as user_id") [==] "U.`id` AS `user_id`"
	 * @assert ("user user_id") [==] "`user` AS `user_id`"
	 * @assert ("(...) pos") [==] "(...) AS `pos`"
	 * @assert ("(...) `pos`") [==] "(...) AS `pos`"
	 * @assert ("CONCAT_WS(...) AS `name`") [==] "CONCAT_WS(...) AS `name`"
	 * @assert ("CONCAT_WS(...) name") [==] "CONCAT_WS(...) AS `name`"
	 * @assert (array("table.field", "table.field alias")) [==] "`table`.`field`, `table`.`field` AS `alias`"
	 * @return string
	 */
	protected function fieldName($name)
	{
		if (is_array($name)) {
			$rets = array();
			foreach ($name AS $item) {
				$rets[] = $this->fieldName($item);
			}
			$ret = implode(', ', $rets);
		} elseif (is_int($name)) {
			$ret = $name;
		} else {
			$name = trim($name);
			if (preg_match('/^(.*?)\((.*)\)(([\s]+as)?[\s]+`?([a-z0-9_]+)`?)$/i', $name, $match)) {
				// (...) as pos
				$ret = "$match[1]($match[2]) AS `$match[5]`";
			} elseif (preg_match('/^([A-Z0-9]{1,2})\.([\*a-zA-Z0-9_]+)((\s+(as|AS))?\s+([a-zA-Z0-9_]+))?$/', $name, $match)) {
				// U.id as user_id
				$ret = $match[1].'.'.($match[2] == '*' ? '*' : '`'.$match[2].'`').(isset($match[6]) ? ' AS `'.$match[6].'`' : '');
			} elseif (preg_match('/^`?([a-z0-9_]+)`?\.([\*a-z0-9_]+)(([\s]+as)?[\s]+([a-z0-9_]+))?$/i', $name, $match)) {
				// `user`.id, user.id as user_id
				$ret = '`'.$match[1].'`.'.($match[2] == '*' ? '*' : '`'.$match[2].'`').(isset($match[5]) ? ' AS `'.$match[5].'`' : '');
			} elseif (preg_match('/^([a-z0-9_]+)(([\s]+as)?[\s]+([a-z0-9_]+))?$/i', $name, $match)) {
				// user as user_id
				$ret = '`'.$match[1].'`'.(isset($match[4]) ? ' AS `'.$match[4].'`' : '');
			} else {
				$ret = $name == '*' ? '*' : '`'.$name.'`';
			}
		}
		return $ret;
	}

	/**
	 * แปลงข้อความสำหรับ value
	 *
	 * @param string $value
	 * @assert (1) [===] 1
	 * @assert ("string") [==] "'string'"
	 * @assert ("table.field") [==] "`table`.`field`"
	 * @assert ("table.field alias") [==] "`table`.`field` AS `alias`"
	 * @assert ("table.field as alias") [==] "`table`.`field` AS `alias`"
	 * @assert (array("table.field", "table.field alias")) [==] "(`table`.`field`, `table`.`field` AS `alias`)"
	 * @return string
	 */
	protected function fieldValue($value)
	{
		if (is_array($value)) {
			$rets = array();
			foreach ($value AS $item) {
				$rets[] = $this->fieldValue($item);
			}
			$ret = '('.implode(', ', $rets).')';
		} elseif (is_int($value)) {
			$ret = $value;
		} elseif (preg_match('/^([a-z0-9_]+)\.([a-z0-9_]+)(([\s]+as)?[\s]+([a-z0-9]+))?$/i', $value, $match)) {
			$ret = "`$match[1]`.`$match[2]`".(isset($match[5]) ? ' AS `'.$match[5].'`' : '');
		} else {
			$ret = '\''.$value.'\'';
		}
		return $ret;
	}

	/**
	 * แปลงข้อมูลรูปแบบ SQL
	 *
	 * @param array $params
	 * รูปแบบ array('field1', 'condition', 'field2')
	 * ไม่ระบุ condition หมายถึง = หรือ IN
	 * @assert (1) [==] 1
	 * @assert ('string') [==] "string"
	 * @assert (array('table.id', 'table.id')) [==] "`table`.`id` = `table`.`id`"
	 * @assert (array('table.id', '=', 'table.id')) [==] "`table`.`id` = `table`.`id`"
	 * @assert (array('id', array(1, 2, '3'))) [==] "`id` IN (1, 2, '3')"
	 * @assert (array('id', 'IN', array(1, 2, '3'))) [==] "`id` IN (1, 2, '3')"
	 * @assert (array('table.id', '=', 'one')) [==] "`table`.`id` = 'one'"
	 * @assert (array('id', '=', 1)) [==] "`id` = 1"
	 * @assert (array('U.id', '=', 1)) [==] "U.`id` = 1"
	 * @assert (array('id', 'IN', array(1, 2, '3'))) [==] "`id` IN (1, 2, '3')"
	 * @assert (array('id', '!=', '(...) alias')) [==] "`id` != (...) AS `alias`"
	 * @return string
	 */
	protected function buildValue($params)
	{
		if (is_array($params)) {
			if (sizeof($params) == 2) {
				$params = array($params[0], '=', $params[1]);
			} else {
				$params = array($params[0], trim($params[1]), $params[2]);
			}
			$key = $this->fieldName($params[0]);
			if (is_int($params[2]) || is_bool($params[2])) {
				// value เป็นตัวเลข หรือ boolean
				$value = $params[2];
			} elseif (is_array($params[2])) {
				// value เป็น array
				if ($params[1] == '=') {
					$params[1] = 'IN';
				}
				$qs = array();
				foreach ($params[2] as $item) {
					if (is_int($item) || is_bool($item)) {
						$qs[] = $item;
					} else {
						$qs[] = "'$item'";
					}
				}
				$value = '('.implode(', ', $qs).')';
			} elseif (preg_match('/^\((.*)\)([\s]+as)?[\s]+([a-z0-9_]+)$/i', $params[2], $match)) {
				// value เป็น query string
				$value = "($match[1]) AS `$match[3]`";
			} elseif (preg_match('/^([A-Z0-9]{1,2})\.([a-zA-Z0-9_]+)$/', $params[2], $match)) {
				// U.id
				$value = $match[1].'.`'.$match[2].'`';
			} elseif (preg_match('/^([a-z0-9_]+)\.([a-z0-9_]+)$/i', $params[2], $match)) {
				// value เป็น table.field
				$value = '`'.$match[1].'`.`'.$match[2].'`';
			} else {
				// value เป็น string
				$value = "'".$params[2]."'";
			}
			$params = $key.' '.$params[1].' '.$value;
		}
		return $params;
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง WHERE
	 *
	 * @param mixed $condition
	 * @param string $oprator (optional) เช่น AND หรือ OR
	 * @param string $id (optional )ชื่อฟิลด์ที่เป็น key
	 * @assert (1) [==] "`id`=1"
	 * @assert ('string') [==] "string"
	 * @assert (array('user_id', 1)) [==] "`user_id`=1"
	 * @assert (array(array('id', 1), array('id', array(1, 2, '3')))) [==] array("`id`=1 AND `id` IN (:id0, :id1, :id2)", array(':id0' => 1, ':id1' => 2, ':id2' => '3'))
	 * @assert (array('(...)')) [==] "(...)"
	 * @return string|array คืนค่า string สำหรับคำสั่ง WHERE หรือคืนค่า array(where, values) สำหรับใช้กับการ bind
	 */
	protected function buildWhere($condition, $oprator = 'AND', $id = 'id')
	{
		if (is_array($condition)) {
			if (is_array($condition[0])) {
				$qs = array();
				$ps = array();
				foreach ($condition as $item) {
					$ret = $this->whereValue($item);
					if (is_array($ret)) {
						$qs[] = $ret[0];
						$ps = ArrayTool::replace($ps, $ret[1]);
					} else {
						$qs[] = $ret;
					}
				}
				$condition = implode(' '.$oprator.' ', $qs);
				if (!empty($ps)) {
					$condition = array($condition, $ps);
				}
			} elseif (strpos($condition[0], '(') !== false) {
				$condition = $condition[0];
			} else {
				$condition = $this->whereValue($condition);
			}
		} elseif (preg_match('/^[0-9]+$/', $condition)) {
			// primaryKey
			$condition = $this->fieldName($id).'='.$condition;
		}
		return $condition;
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง WHERE และ values ไม่ใส่ alias ให้กับชื่อฟิลด์
	 *
	 * @param mixed $condition
	 * @param string $oprator (optional) เช่น AND หรือ OR
	 * @param string $id (optional )ชื่อฟิลด์ที่เป็น key
	 * @assert (1) [==] array("`id` = :id", array(':id' => 1))
	 * @assert ('string') [==] array("string", array())
	 * @assert (array('user_id', 1)) [==] array("`user_id` = :user_id", array(':user_id' => 1))
	 * @assert (array(array('id', 1), array('id', array(1, 2, '3')))) [==] array("`id` = :id AND `id` IN (:id0,:id1,:id2)", array(':id0' => 1, ':id1' => 2, ':id2' => '3', ':id' => 1))
	 * @assert (array('(...)')) [==] array('(...)', array())
	 * @return array ($condition, $values)
	 */
	protected function buildWhereValues($condition, $oprator = 'AND', $id = 'id')
	{
		if (is_array($condition)) {
			$values = array();
			$qs = array();
			if (is_array($condition[0])) {
				foreach ($condition as $item) {
					$ret = $this->buildWhereValues($item, $oprator, $id);
					$qs[] = $ret[0];
					$values = ArrayTool::replace($values, $ret[1]);
				}
				$condition = implode(' '.$oprator.' ', $qs);
			} elseif (strpos($condition[0], '(') !== false) {
				$condition = $condition[0];
			} else {
				if (sizeof($condition) == 2) {
					$condition = array($condition[0], '=', $condition[1]);
				} else {
					$condition[1] = strtoupper(trim($condition[1]));
				}
				if (is_array($condition[2])) {
					$operator = $condition[1] == '=' ? 'IN' : $condition[1];
					$qs = array();
					foreach ($condition[2] as $k => $v) {
						$qs[] = ":$condition[0]$k";
						$values[":$condition[0]$k"] = $v;
					}
					$condition = $this->fieldName($condition[0]).' '.$operator.' ('.implode(',', $qs).')';
				} else {
					$values[":$condition[0]"] = $condition[2];
					$condition = $this->fieldName($condition[0]).' '.$condition[1].' :'.$condition[0];
				}
			}
		} elseif (is_int($condition)) {
			// primaryKey
			$values = array(":$id" => $condition);
			$condition = "`$id` = :$id";
		} else {
			$values = array();
		}
		return array($condition, $values);
	}

	/**
	 * สร้างคำสั่ง WHERE
	 *
	 * @param array $params
	 * @return array|string
	 */
	private function whereValue($params)
	{
		$result = array();
		if (is_array($params)) {
			if (sizeof($params) == 2) {
				$operator = '=';
				$value = $params[1];
			} else {
				$operator = trim($params[1]);
				$value = $params[2];
			}
			$key = $this->fieldName($params[0]);
			if (is_array($value)) {
				if ($operator == '=') {
					$operator = 'IN';
				}
				$q = ':'.preg_replace('/[\.`]/', '', strtolower($key));
				$qs = array();
				$vs = array();
				foreach ($value as $i => $item) {
					$qs[] = $q.$i;
					$vs[$q.$i] = $item;
				}
				$result = array($key.' '.$operator.' ('.implode(', ', $qs).')', $vs);
			} elseif (preg_match('/^(\-?[0-9\s\.]+|true|false)$/i', $value)) {
				// value เป็น ตัวเลข จุดทศนิยม เครื่องหมาย - / , และ true, false
				// เช่น ตัวเลข, จำนวนเงิน, boolean
				$result = "$key $operator ".(is_string($value) ? "'$value'" : $value);
			} elseif (preg_match('/^[0-9\s\-:]+$/', $value)) {
				// วันที่
				$result = "$key $operator '$value'";
			} elseif (preg_match('/^\((.*)\)([\s]+as)?[\s]+([a-z0-9_]+)$/i', $value, $match)) {
				// value เป็น query string
				$result = "$key $operator ($match[1]) AS `$match[3]`";
			} elseif (preg_match('/^([A-Z0-9]{1,2})\.([a-zA-Z0-9_]+)$/', $value, $match)) {
				// U.id
				if ($operator == 'IN' || $operator == 'NOT IN') {
					$result = "$key $operator ($match[1].`$match[2]`)";
				} else {
					$result = "$key $operator $match[1].`$match[2]`";
				}
			} elseif (preg_match('/^([a-z0-9_]+)\.([a-z0-9_]+)$/i', $value, $match)) {
				// value เป็น table.field
				if ($operator == 'IN' || $operator == 'NOT IN') {
					$result = "$key $operator (`$match[1]`.`$match[2]`)";
				} else {
					$result = "$key $operator `$match[1]`.`$match[2]`";
				}
			} else {
				// value เป็น string
				$q = ':'.preg_replace('/[\.`]/', '', strtolower($key));
				$result = array($key.$operator.$q, array($q => $value));
			}
		} else {
			$result = $params;
		}
		return $result;
	}
}