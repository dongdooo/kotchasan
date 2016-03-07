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
	protected $db;
	/**
	 * ตัวแปรเก็บคำสั่ง SQL
	 *
	 * @var array
	 */
	protected $sqls;

	/**
	 * Class constructor
	 *
	 * @param string $conn ชื่อของการเชื่อมต่อ ถ้าไม่ระบุจะไม่มีการเชื่อมต่อ database
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
	 * ฟังก์ชั่นสร้าง SQL สำหรับหาค่าสูงสุด + 1
	 * ใช้ในการหาค่า id ถัดไป
	 *
	 * @param string $field ชื่อฟิลด์ที่ต้องการหาค่าสูงสุด
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param string $alias ชื่อของผลลัพท์ ถ้าไม่ระบุจะเป็นชื่อเดียวกับชื่อฟิลด์
	 * @return string SQL Command
	 */
	public function buildNext($field, $table, $condition = null, $alias = null)
	{
		return $this->db->buildNext($field, $table, $condition, $alias);
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
	 * @return string
	 */
	protected function buildSelect($fields)
	{
		if (is_array($fields)) {
			if ($fields[0] instanceof QueryBuilder) {
				// QueryBuilder
				$ret = '('.$fields[0]->text().') AS `'.$fields[1].'`';
			} else {
				// multiples
				$rets = array();
				foreach ($fields AS $item) {
					$rets[] = $this->buildSelect($item);
				}
				$ret = implode(', ', $rets);
			}
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
	 * ฟังก์ชั่นสร้างคีย์ สำหรับการ execute
	 *
	 * @param string $name ชื่อฟิลด์
	 * @param string $prefix คำนำหน้าชื่อฟิลด์ ใช้เพื่อป้องกันการใช้ตัวแปรซ้ำ
	 * @return string
	 */
	protected function aliasName($name, $prefix = '')
	{
		return ':'.$prefix.trim(preg_replace('/[`\._\-]/', '', $name));
	}

	/**
	 * แปลงข้อความสำหรับชื่อฟิลด์หรือชื่อตาราง
	 *
	 * @param string $name
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
			$condition = $this->fieldName($id).' = '.$condition;
		}
		return $condition;
	}

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง WHERE และ values ไม่ใส่ alias ให้กับชื่อฟิลด์
	 *
	 * @param mixed $condition
	 * @param string $oprator (optional) เช่น AND หรือ OR
	 * @param string $id (optional )ชื่อฟิลด์ที่เป็น key
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
			if ($value instanceof QueryBuilder) {
				$values = $value->getValues();
				if (empty($values)) {
					$result = $key.' '.$operator.' ('.$value->text().')';
				} else {
					$result = array($key.' '.$operator.' ('.$value->text().')', $values);
				}
			} elseif (is_array($value)) {
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
			} elseif (empty($value)) {
				// value เป็น string ว่าง, 0, null
				$result = $key.' '.$operator.' '.($value === 0 ? 0 : "'$value'" );
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