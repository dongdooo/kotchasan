<?php
/*
 * @filesource core/database/driver.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Database;

use Core\Database\QueryBuilder;
use Core\Database\Schema;
use \Core\Database\DbCache as Cache;

/**
 * Kotchasan Database driver Class (base class)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class Driver extends Query
{
	/**
	 * database connection
	 *
	 * @var resource
	 */
	protected $connection = null;
	/**
	 * database error message
	 *
	 * @var string
	 */
	protected $error_message = '';
	/**
	 * นับจำนวนการ query
	 *
	 * @var int
	 */
	protected static $query_count = 0;
	/**
	 * เก็บ Object ที่เป็นผลลัพท์จากการ query
	 *
	 * @var resource|object
	 */
	protected $result_id;
	/**
	 * ตัวแปรเก็บ query สำหรับการ execute
	 *
	 * @var array
	 */
	protected $sqls;
	/**
	 * cache class
	 *
	 * @var Cache
	 */
	protected $cache;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->cache = Cache::create();
	}

	/**
	 * เปิดการใช้งานแคช
	 * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล
	 *
	 * @param bool $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
	 * @return \static
	 */
	public function cacheOn($auto_save = true)
	{
		$this->cache->cacheOn($auto_save);
		return $this;
	}

	/**
	 * ฟังก์ชั่นคืนค่า Database Cache
	 *
	 * @param Cache
	 */
	public function cache()
	{
		return $this->cache;
	}

	/**
	 * close database.
	 */
	public function close()
	{
		$this->connection = null;
	}

	/**
	 * ฟังก์ชั่นอ่านค่า resource ID ของการเชื่อมต่อปัจจุบัน.
	 *
	 * @return resource
	 */
	public function connection()
	{
		return $this->connection;
	}

	/**
	 * ฟังก์ชั่นสร้าง query builder
	 *
	 * @return QueryBuilder
	 */
	public function createQuery()
	{
		return new QueryBuilder($this);
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL สำหรับสอบถามข้อมูล คืนค่าผลลัพท์เป็นแอเรย์ของข้อมูลที่ตรงตามเงื่อนไข.
	 *
	 * @param string $sql query string
	 * @param bool $toArray (option) default  false คืนค่าผลลัทเป็น Object, true คืนค่าเป็น Array
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return array คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข ไม่พบคืนค่าแอเรย์ว่าง
	 */
	public function customQuery($sql, $toArray = false, $values = array())
	{
		$result = $this->doCustomQuery($sql, $values);
		if ($result === false) {
			$this->logError($sql, $this->error_message);
			$result = array();
		} elseif (!$toArray) {
			foreach ($result as $i => $item) {
				$result[$i] = (object)$item;
			}
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบว่ามี database หรือไม่
	 *
	 * @param string $database ชื่อฐานข้อมูล
	 * @return bool คืนค่า true หากมีฐานข้อมูลนี้อยู่ ไม่พบคืนค่า false
	 */
	public function databaseExists($database)
	{
		$search = $this->doCustomQuery("SELECT 1 FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$database'");
		return $search && sizeof($search) == 1 ? true : false;
	}

	/**
	 * ฟังก์ชั่นลบ record
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param int $limit (option) จำนวนรายการที่ต้องการลบ
	 */
	public function delete($table, $condition, $limit = 1)
	{
		$condition = $this->buildWhere($condition);
		if (is_array($condition)) {
			$values = $condition[1];
			$condition = $condition[0];
		} else {
			$values = array();
		}
		$sql = 'DELETE FROM `'.$table.'` WHERE '.$condition;
		if (is_int($limit) && $limit > 0) {
			$sql .= ' LIMIT '.$limit;
		}
		return $this->doQuery($sql, $values) === false ? false : true;
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL จาก query builder
	 *
	 * @param array $sqls
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return mixed
	 */
	public function execQuery($sqls, $values = array())
	{
		$sql = $this->makeQuery($sqls);
		if (isset($sqls['values'])) {
			$values = \ArrayTool::replace($sqls['values'], $values);
		}
		if ($sqls['function'] == 'customQuery') {
			$result = $this->customQuery($sql, false, $values);
		} else {
			$result = $this->query($sql, $values);
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบว่ามีฟิลด์ หรือไม่.
	 *
	 * @param string $table ชื่อตาราง
	 * @param string $field ชื่อฟิลด์
	 * @return bool คืนค่า true หากมีฟิลด์นี้อยู่ ไม่พบคืนค่า false
	 */
	public function fieldExists($table, $field)
	{
		if (!empty($table) && !empty($field)) {
			$field = strtolower($field);
			foreach (Schema::create($this->db)->fields($table) as $key => $values) {
				if (strtolower($key) == $field) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * ฟังก์ชั่น query ข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @return object|bool คืนค่า object ของข้อมูล ไม่พบคืนค่า false
	 */
	public function first($table, $condition)
	{
		$result = $this->select($table, $condition, array(), 1);
		return sizeof($result) == 1 ? (object)$result[0] : false;
	}

	/**
	 * คืนค่าข้อความผิดพลาดของฐานข้อมูล
	 *
	 * @return string
	 */
	public function getError()
	{
		return $this->error_message;
	}

	/**
	 * ฟังก์ชั่นอ่าน ID ล่าสุดของตาราง สำหรับตารางที่มีการกำหนด Auto_increment ไว้.
	 *
	 * @param string $table ชื่อตาราง
	 * @return int คืนค่า id ล่าสุดของตาราง
	 */
	public function lastId($table)
	{
		$result = $this->doCustomQuery("SHOW TABLE STATUS LIKE '$table'");
		return $result && sizeof($result) == 1 ? (int)$result[0]['Auto_increment'] : 0;
	}

	/**
	 * ฟังก์ชั่นบันทึกการ query sql
	 *
	 * @param string $type
	 * @param string $sql
	 * @param array $values (options)
	 */
	protected function log($type, $sql, $values = array())
	{
		if (DB_LOG == true) {
			$datas = array();
			$datas[] = $type.' : <em>'.\KString::replace($sql, $values).'</em>';
			foreach (debug_backtrace() as $a => $item) {
				if (isset($item['file']) && isset($item['line'])) {
					$f = $item['function'];
					if ($f == 'all' || $f == 'first' || $f == 'count' || $f == 'save' || $f == 'find' || $f == 'execute') {
						$datas[] = '<br>['.$a.'] <b>'.$f.'</b> in <b>'.$item['file'].'</b> line <b>'.$item['line'].'</b>';
						break;
					}
				}
			}
			if (!empty($datas)) {
				// บันทึก log
				save_log(implode('', $datas));
			}
		}
	}

	/**
	 * ฟังก์ชั่นจัดการ error ของ database
	 *
	 * @param string $sql
	 * @param string $message
	 */
	protected function logError($sql, $message)
	{
		$trace = debug_backtrace();
		$trace = next($trace);
		log_message($sql, $message, $trace['file'], $trace['line']);
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL ที่ไม่ต้องการผลลัพท์ เช่น CREATE INSERT UPDATE.
	 *
	 * @param string $sql
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return bool สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false
	 */
	public function query($sql, $values = array())
	{
		$result = $this->doQuery($sql, $values);
		if (!$result) {
			$this->logError($sql, $this->error_message);
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นอ่านจำนวน query ทั้งหมดที่ทำงาน.
	 *
	 * @return int
	 */
	public static function queryCount()
	{
		return self::$query_count;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบว่ามีตาราง หรือไม่.
	 *
	 * @param string $table ชื่อตาราง
	 * @return bool คืนค่า true หากมีตารางนี้อยู่ ไม่พบคืนค่า false
	 */
	public function tableExists($table)
	{
		return $this->doQuery("SELECT 1 FROM `$table` LIMIT 1") === false ? false : true;
	}

	/**
	 * ฟังก์ชั่นลบข้อมูลทั้งหมดในตาราง
	 *
	 * @param  string $table table name
	 * @return bool คืนค่า true ถ้าสำเร็จ
	 */
	public function truncate($table)
	{
		return $this->query("TRUNCATE TABLE `$table`") === false ? false : true;
	}

	/**
	 * อัปเดทข้อมูลทุก record
	 *
	 * @param array $save ข้อมูลที่ต้องการบันทึก
	 * array('key1'=>'value1', 'key2'=>'value2', ...)
	 * @return bool สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	public function updateAll($table, $save)
	{
		return $this->update($table, array(1, 1), $save);
	}

	/**
	 * จำนวนฟิลด์ทั้งหมดในผลลัพท์จากการ query
	 *
	 * @return int
	 */
	abstract public function fieldCount();

	/**
	 * รายชื่อฟิลด์ทั้งหมดจากผลัพท์จองการ query
	 *
	 * @return array
	 */
	abstract public function getFileds();

	/**
	 * ฟังก์ชั่นเพิ่มข้อมูลใหม่ลงในตาราง
	 *
	 * @param string $table ชื่อตาราง
	 * @param array $save ข้อมูลที่ต้องการบันทึก
	 * @return int|bool สำเร็จ คืนค่า id ที่เพิ่ม ผิดพลาด คืนค่า false
	 */
	abstract public function insert($table, $save);

	/**
	 * ฟังก์ชั่นสร้างคำสั่ง sql query
	 *
	 * @param array $sqls คำสั่ง sql จาก query builder
	 * @return string sql command
	 */
	abstract public function makeQuery($sqls);

	/**
	 * เรียกดูข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param array $sort เรียงลำดับ
	 * @param int $limit จำนวนข้อมูลที่ต้องการ
	 * @return array ผลลัพท์ในรูป array ถ้าไม่สำเร็จ คืนค่าแอเรย์ว่าง
	 */
	abstract public function select($table, $condition, $sort = array(), $limit = 0);

	/**
	 * ฟังก์ชั่นแก้ไขข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param array $save ข้อมูลที่ต้องการบันทึก รูปแบบ array('key1'=>'value1', 'key2'=>'value2', ...)
	 * @return bool สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	abstract public function update($table, $condition, $save);
}