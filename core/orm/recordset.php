<?php
/**
 * @filesource core/orm/recordset.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Orm;

use \Core\Database\Query as Query;
use \Core\Database\Schema as Schema;
use \Core\Database\Cache as Cache;

/**
 * ORM model base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Recordset extends Query implements \Iterator
{
	/**
	 * ข้อมูล
	 *
	 * @var array
	 */
	private $datas;
	/**
	 * รายการเริ่มต้นสำหรับการ query เพื่อแบ่งหน้า
	 *
	 * @var int
	 */
	private $firstRecord;
	/**
	 * คลาส Model
	 *
	 * @var Core\Orm\Field
	 */
	private $model;
	/**
	 * จำนวนรายการต่อหน้า สำหรับใช้ในการแบ่งหน้า
	 *
	 * @var int
	 */
	private $perPage;
	/**
	 * คลาส Schema
	 *
	 * @var Core\Database\Schema
	 */
	private $schema;
	/**
	 * ตัวแปรเก็บคำสั่ง SQL
	 *
	 * @var array
	 */
	private $sqls;
	/**
	 * ชื่อรองของตาราง
	 *
	 * @var string
	 */
	private $table_alias;
	/**
	 * ชื่อตาราง
	 *
	 * @var string
	 */
	private $table_name;
	/**
	 * กำหนดผลลัพท์ของ Recordset
	 * true ผลลัพท์เป็น Array
	 * false ผลลัพท์เป็น Model
	 *
	 * @var boolean
	 */
	private $toArray = false;

	/**
	 * create new Recordset
	 *
	 * @param string $model ชื่อ Model
	 */
	public function __construct($model)
	{
		$this->cache = new Cache();
		$this->model = new $model(null, $this->cache);
		$this->sqls = array();
		$this->values = array();
		if (!isset($this->db)) {
			$this->db = \Database::create($this->model->getAttribute('conn'));
			$this->inintTableName($this->model->getAttribute('table'));
			$this->schema = new Schema($this->db, $this->table_name);
		}
		if (method_exists($this->model, 'getConfig')) {
			$result = $this->model->getConfig();
			foreach ($result as $key => $value) {
				$this->queryBuilder($key, $value);
			}
		}
	}

	/**
	 * query ข้อมูลทุกรายการ
	 * SELECT ....
	 *
	 * @param array|string $fields (options) null หมายถึง SELECT ตามที่กำหนดโดย model
	 * @return array|\Core\Orm\Recordset
	 */
	public function all($fields = null)
	{
		if (!empty($fields)) {
			$qs = array();
			foreach (func_get_args() AS $item) {
				if (!empty($item)) {
					$qs[] = $this->fieldName($item);
				}
			}
			$this->sqls['select'] = empty($qs) ? '*' : implode(', ', $qs);
		} elseif (empty($this->sqls['select'])) {
			$this->sqls['select'] = '*';
		}
		return $this->doExecute(0, 0);
	}

	/**
	 * create new Recordset
	 *
	 * @param string $model ชื่อ Model
	 * @return \Core\Orm\Recordset
	 */
	public static function create($model)
	{
		$obj = new static($model);
		return $obj;
	}

	/**
	 * build query string
	 *
	 * @return string
	 */
	private function createQuery($start, $count)
	{
		$this->sqls['from'] = $this->tableWithAlias();
		if (!empty($start) || !empty($count)) {
			$this->sqls['limit'] = $count;
			$this->sqls['start'] = $start;
		}
		return $this->db->makeQuery($this->sqls);
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL สำหรับสอบถามข้อมูล คืนค่าผลลัพท์เป็นแอเรย์ของข้อมูลที่ตรงตามเงื่อนไข.
	 *
	 * @param string $sql query string
	 * @param boolean $toArray (option) default true คืนค่าเป็น Array, false คืนค่าผลลัทเป็น Object
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return array|object คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข
	 */
	public function customQuery($sql, $toArray = true, $values = array())
	{
		return $this->db->customQuery($sql, $toArray, $values, $this->cache);
	}

	/**
	 * เปิดการใช้งานแคช
	 * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล
	 *
	 * @param boolean $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
	 * @return array|\Core\Orm\Recordset
	 */
	private function cacheOn($auto_save = true)
	{
		$this->cache->cacheOn($auto_save);
		return $this;
	}

	/**
	 * นับจำนวน record ใช้สำหรับการแบ่งหน้า
	 *
	 * @return int
	 */
	public function count()
	{
		$old_sqls = $this->sqls;
		$old_values = $this->values;
		$this->sqls = array();
		$this->sqls['select'] = 'COUNT(*) AS `count`';
		foreach ($old_sqls as $key => $value) {
			if ($key !== 'order' && $key !== 'limit' && $key !== 'select') {
				$this->sqls[$key] = $value;
			}
		}
		$sql = $this->createQuery(0, 0);
		$result = $this->db->customQuery($sql, true, $this->values, $this->cache);
		$count = empty($result) ? 0 : (int)$result[0]['count'];
		$this->sqls = $old_sqls;
		$this->values = $old_values;
		return $count;
	}

	/**
	 * ลบ record กำหนดโดย $condition
	 *
	 * @param mixed $condition int (primaryKey), string (SQL QUERY), array
	 * @param boolean $all false (default) ลบรายการเดียว, true ลบทุกรายการที่ตรงตามเงื่อนไข
	 * @param string $oprator สำหรับเชื่อมแต่ละ $condition เข้าด้วยกัน AND (default), OR
	 * @return boolean true ถ้าสำเร็จ
	 */
	public function delete($condition = array(), $all = false, $oprator = 'AND')
	{
		$ret = $this->buildWhereValues($condition, $oprator, $this->model->getAttribute('primaryKey'));
		$sqls = array(
			'delete' => '`'.$this->table_name.'`',
			'where' => $ret[0]
		);
		if (!$all) {
			$sqls['limit'] = 1;
		}
		$sql = $this->makeQuery($sqls);
		return $this->db->query($sql, $ret[1]);
	}

	/**
	 * query ข้อมูลที่มีการแบ่งหน้า
	 * SELECT ....
	 *
	 * @param int $start
	 * @param int $end
	 * @return array|\Core\Orm\Recordset
	 */
	private function doExecute($start, $end)
	{
		$sql = $this->createQuery($start, $end);
		$result = $this->db->customQuery($sql, true, $this->values, $this->cache);
		if ($this->toArray) {
			return $result;
		} else {
			$class = get_class($this->model);
			$this->datas = array();
			foreach ($result as $item) {
				$this->datas[] = new $class($item, $this->cache);
			}
			return $this;
		}
	}

	/**
	 * INNER JOIN table ON ....
	 *
	 * @param string $model model class ของตารางที่ join
	 * @param string $type เช่น LEFT, RIGHT, INNER...
	 * @param mixed $on where condition สำหรับการ join
	 * @return \Core\Orm\Recordset
	 */
	private function doJoin($model, $type, $on)
	{
		if (preg_match('/^([a-zA-Z0-9\\\\]+)(\s+(as|AS))?[\s]+([A-Z0-9]{1,2})?$/', $model, $match)) {
			$model = $match[1];
		}
		$rs = Recordset::create($model);
		$table = $rs->tableWithAlias(isset($match[4]) ? $match[4] : null);
		$ret = $rs->buildJoin($table, $type, $on);
		if (is_array($ret)) {
			$this->sqls['join'][] = $ret[0];
			$this->values = \Arraytool::replace($this->values, $ret[1]);
		} else {
			$this->sqls['join'][] = $ret;
		}
		return $this;
	}

	/**
	 * query ข้อมูลที่มีการแบ่งหน้า
	 * SELECT ....
	 *
	 * @param array|string $fields (options) null หมายถึง SELECT ตามที่กำหนดโดย model
	 * @return array|\Core\Orm\Recordset
	 */
	public function execute($fields = null)
	{
		if (!empty($fields)) {
			$qs = array();
			foreach (func_get_args() AS $item) {
				if (!empty($item)) {
					$qs[] = $this->fieldName($item);
				}
			}
			$this->sqls['select'] = empty($qs) ? '*' : implode(', ', $qs);
		} elseif (empty($this->sqls['select'])) {
			$this->sqls['select'] = '*';
		}
		return $this->doExecute($this->firstRecord, $this->perPage);
	}

	/**
	 * เรียกข้อมูลที่ $primaryKey
	 *
	 * @param int $id
	 * @return Core\Orm\Field
	 */
	public function find($id)
	{
		return $this->where((int)$id)->first();
	}

	/**
	 * Query ข้อมูลรายการเดียว
	 * SELECT .... LIMIT 1
	 *
	 * @param array|string $fields (options) null หมายถึง SELECT ตามที่กำหนดโดย model
	 * @return boolean|array|Core\Orm\Field ไม่พบคืนค่า false พบคืนค่า record ของข้อมูลรายการเดียว
	 */
	public function first($fields = null)
	{
		$sqls = array(
			'from' => $this->tableWithAlias(),
			'limit' => 1
		);
		if (!empty($fields)) {
			$qs = array();
			foreach (func_get_args() AS $item) {
				if (!empty($item)) {
					$qs[] = $this->fieldName($item);
				}
			}
			$sqls['select'] = empty($qs) ? '*' : implode(', ', $qs);
		} elseif (empty($this->sqls['select'])) {
			$sqls['select'] = '*';
		}
		$sqls = \Arraytool::replace($this->sqls, $sqls);
		$sql = $this->db->makeQuery($sqls);
		$this->datas = $this->db->customQuery($sql, true, $this->values, $this->cache);
		if (empty($this->datas)) {
			return false;
		} elseif ($this->toArray) {
			return $this->datas[0];
		} else {
			$class = get_class($this->model);
			return new $class($this->datas[0], $this->cache);
		}
	}

	/**
	 * รายชื่อฟิลด์ทั้งหมดของ Model
	 *
	 * @return array
	 */
	public function getFileds()
	{
		if (empty($this->datas)) {
			$this->first();
		}
		return $this->db->getFileds();
	}

	/**
	 * ฟังก์ชั่นสำหรับจัดกลุ่มคำสั่ง และ เชื่อมแต่ละกลุ่มด้วย $oprator
	 *
	 * @param array $params คำสั่ง รูปแบบ array('field1', 'condition', 'field2')
	 * @param string $oprator AND หรือ OR
	 * @return string query ภายใต้ ()
	 */
	public static function group($params, $oprator = 'AND')
	{
		$oprator = strtoupper($oprator);
		$obj = self::create();
		if ($oprator == 'AND') {
			return $obj->groupAnd($params);
		} elseif ($oprator == 'OR') {
			return $obj->groupOr($params);
		}
	}

	/**
	 * ฟังก์ชั่นตรวจสอบชื่อตารางและชื่อรอง
	 */
	private function inintTableName($table)
	{
		if (empty($table)) {
			$class = get_called_class();
			if (preg_match('/[a-z]+\\\\([a-z_]+)\\\\Model/i', $class, $match)) {
				$t = strtolower($match[1]);
			} elseif (preg_match('/Models\\\\([a-z_]+)/i', $class, $match)) {
				$t = strtolower($match[1]);
			} else {
				$t = strtolower($class);
			}
			$this->table_name = $this->tableWithPrefix($t);
			$this->table_alias = $t;
		} elseif (preg_match('/([a-zA-Z_]+)(\s+(as|AS))?\s+([A-Z0-9]{1,2})/', $table, $match)) {
			$this->table_name = $this->tableWithPrefix($match[1]);
			$this->table_alias = $match[4];
		} elseif (preg_match('/([a-zA-Z_]+)(\s+(as|AS))?\s+([a-zA-Z0-9]{1,2})/', $table, $match)) {
			$this->table_name = $this->tableWithPrefix($match[1]);
			$this->table_alias = $match[4];
		} else {
			// ใช้ชื่อตารางเป็นชื่อรอง
			$this->table_name = $this->tableWithPrefix($table);
			$this->table_alias = $table;
		}
	}

	/**
	 * insert ข้อมูล
	 *
	 * @param Core\Orm\Field $model
	 * @return int|boolean สำเร็จ คืนค่า id ที่เพิ่ม ผิดพลาด คืนค่า false
	 */
	public function insert($model)
	{
		$save = array();
		foreach ($this->schema->fields($this->cache) as $field) {
			if (isset($model->$field)) {
				$save[$field] = $model->$field;
			}
		}
		if (empty($save)) {
			$result = false;
		} else {
			$result = $this->db->insert($this->table_name, $save);
			if (isset($this->cache)) {
				$this->cache->save(array($save));
			}
		}
		return $result;
	}

	/**
	 * INNER JOIN table ON ....
	 *
	 * @param string $model model class ของตารางที่ join
	 * @param string $type เช่น LEFT, RIGHT, INNER...
	 * @param mixed $on where condition สำหรับการ join
	 * @return \Core\Orm\Recordset
	 */
	public function join($model, $type, $on)
	{
		return $this->doJoin($model, $className, $on);
	}

	/**
	 * สร้าง query เรียงลำดับ
	 *
	 * @param mixed $sort array('field ASC','field DESC') หรือ 'field ASC', 'field DESC', ....
	 * @return \Core\Orm\Recordset
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
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL ที่ไม่ต้องการผลลัพท์ เช่น CREATE INSERT UPDATE.
	 *
	 * @param string $sql
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return boolean สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false
	 */
	public function query($sql, $values = array())
	{
		$this->db->query($sql, $values);
	}

	/**
	 * สร้าง query จาก config
	 *
	 * @param string $func
	 * @param mixed $param
	 */
	private function queryBuilder($method, $param)
	{
		if ($method == 'join') {
			foreach ($param as $item) {
				$this->doJoin($item[1], $item[0], $item[2]);
			}
		} else {
			$func = 'build'.ucwords($method);
			if (method_exists($this, $func)) {
				$ret = $this->$func($param);
				if (is_array($ret)) {
					$this->sqls[$method] = $ret[0];
					$this->values = \Arraytool::replace($this->values, $ret[1]);
				} else {
					$this->sqls[$method] = $ret;
				}
			}
		}
	}

	/**
	 * ฟังก์ชั่นอ่านชื่อตาราง
	 *
	 * @return string
	 */
	public function tableName()
	{
		return $this->table_name;
	}

	/**
	 * ฟังก์ชั่นอ่านชื่อตารางและชื่อรอง
	 *
	 * @return string
	 */
	public function tableWithAlias($alias = null)
	{
		return '`'.$this->table_name.'` AS '.(empty($alias) ? $this->table_alias : $alias);
	}

	/**
	 * จำกัดจำนวนผลลัพท์
	 * LIMIT $start, $count
	 *
	 * @param int $start ข้อมูลเริ่มต้น
	 * @param int $count จำนวนผลลัพธ์ที่ต้องการ
	 * @return \Core\Orm\Recordset
	 */
	public function take($count)
	{
		$count = func_num_args();
		if ($count == 1) {
			$this->perPage = (int)func_get_arg(0);
			$this->firstRecord = 0;
		} else {
			$this->perPage = (int)func_get_arg(1);
			$this->firstRecord = (int)func_get_arg(0);
		}
		return $this;
	}

	/**
	 * คืนค่าข้อมูลเป็น Array
	 * ฟังก์ชั่นนี้ใช้เรียกก่อนการสอบถามข้อมูล
	 *
	 * @return \Core\Orm\Recordset
	 */
	public function toArray()
	{
		$this->toArray = true;
		return $this;
	}

	/**
	 * ฟังก์ชั่นลบข้อมูลทั้งหมดในตาราง
	 *
	 * @return bool คืนค่า true ถ้าสำเร็จ
	 */
	public function truncate()
	{
		return $this->db->truncate($this->table_name);
	}

	/**
	 *
	 * @param array $condition
	 * @param array|Core\Orm\Field $save
	 * @return boolean สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	public function update($condition, $save)
	{
		$datas = array();
		$fields = $this->schema->fields($this->cache);
		if (is_array($save)) {
			foreach ($fields as $field) {
				if (isset($save[$field])) {
					$datas[$field] = $save[$field];
				}
			}
		} else {
			foreach ($fields as $field) {
				if (isset($save->$field)) {
					$datas[$field] = $save->$field;
				}
			}
		}
		if (empty($datas)) {
			$result = false;
		} else {
			$result = $this->db->update($this->table_name, $condition, $datas);
			if (isset($this->cache)) {
				$this->cache->save(array($datas));
			}
		}
		return $result;
	}

	/**
	 * อัปเดทข้อมูลทุก record
	 *
	 * @param array $recArr ข้อมูลที่ต้องการบันทึก
	 * array('key1'=>'value1', 'key2'=>'value2', ...)
	 * @return boolean สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	public function updateAll($array)
	{
		return $this->db->updateAll($this->table_name, $array);
	}

	/**
	 * WHERE ....
	 * int ค้นหาจาก primaryKey เช่น id=1 หมายถึง WHERE `id`=1
	 * string เช่น QUERY ต่างๆ `email`='xxx.com' หมายถึง WHERE `email`='xxx.com'
	 * array เช่น ('id', 1) หมายถึง WHERE `id`=1
	 * array เช่น ('email', '!=', 'xxx.com') หมายถึง WHERE `email`!='xxx.com'
	 * ถ้าเป็น array สามารถรุบได้หลายค่าโดยแต่ละค่าจะเชื่อมด้วย $oprator
	 *
	 * @param mixed $where
	 * @param string $oprator (options) AND (default), OR
	 * @return \Core\Orm\Recordset
	 */
	public function where($where = array(), $oprator = 'AND')
	{
		if ((is_string($where) && $where != '') || !empty($where)) {
			$where = $this->buildWhere($where, $oprator, $this->table_alias.'.'.$this->model->getAttribute('primaryKey'));
			if (is_array($where)) {
				$this->values = \Arraytool::replace($this->values, $where[1]);
				$where = $where[0];
			}
			$this->sqls['where'] = $where;
		}
		return $this;
	}

	/**
	 * Magic method สำหรับการอ่านรายการ Model
	 *
	 * @param (int) $id
	 * @return array|Core\Orm\Field
	 */
	public function __get($id)
	{
		return $this->get($id);
	}

	/**
	 * อ่านข้อมูลที่ $id
	 *
	 * @param (int) $id
	 * @return array|Core\Orm\Field
	 */
	public function get($id)
	{
		if (isset($this->datas[$id])) {
			return $this->datas[$id];
		}
	}

	/**
	 * Magic method สำหรับการกำหนดค่า Model ลงใน recordset
	 *
	 * @param (int) $id
	 * @param array|Core\Orm\Field $value
	 */
	public function __set($id, $value)
	{
		$this->set($id, $value);
	}

	/**
	 * กำหนดค่า Model ลงใน recordset
	 *
	 * @param (int) $id
	 * @param array|Core\Orm\Field $value
	 */
	public function set($id, $value)
	{
		if (isset($this->datas[$id])) {
			$this->datas[$id] = $value;
		}
	}

	/**
	 * inherited from Iterator
	 */
	public function rewind()
	{
		reset($this->datas);
	}

	public function current()
	{
		$var = current($this->datas);
		return $var;
	}

	public function key()
	{
		$var = key($this->datas);
		return $var;
	}

	public function next()
	{
		$var = next($this->datas);
		return $var;
	}

	public function valid()
	{
		$key = key($this->datas);
		$var = ($key !== NULL && $key !== FALSE);
		return $var;
	}
}