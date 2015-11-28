<?php
/**
 * @filesource core/database/driver.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Database;

/**
 * Database Driver Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Driver extends Query
{
	/**
	 * นับจำนวนการ query
	 *
	 * @var int
	 */
	public static $query_count = 0;
	/**
	 * database connection
	 *
	 * @var resource
	 */
	protected $connection = null;
	/**
	 * เก็บ Object ที่เป็นผลลัพท์จากการ query
	 *
	 * @var resource|object
	 */
	protected $result_id;
	/**
	 * database error message
	 *
	 * @var string
	 */
	protected $error_message = '';
	/**
	 * ตัวแปรเก็บ query สำหรับการ execute
	 *
	 * @var array
	 */
	protected $sqls;

	/**
	 * close database.
	 */
	public function close()
	{
		$this->_close();
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
	 * @return \Core\Database\QueryBuilder
	 */
	public function createQuery()
	{
		return new \Core\Database\QueryBuilder($this);
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL สำหรับสอบถามข้อมูล คืนค่าผลลัพท์เป็นแอเรย์ของข้อมูลที่ตรงตามเงื่อนไข.
	 *
	 * @param string $sql query string
	 * @param boolean $toArray (option) default true คืนค่าเป็น Array, false คืนค่าผลลัทเป็น Object
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @param \Core\Database\Cache $cache  database cache class default null
	 * @return array|object คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข
	 */
	public function customQuery($sql, $toArray = true, $values = array(), $cache = null)
	{
		$result = $this->doCustomQuery($sql, $values, $cache);
		if ($result === false) {
			$this->sendError($sql, $this->error_message);
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
		return sizeof($search) == 1 ? true : false;
	}

	/**
	 * ฟังก์ชั่นลบ record
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $where query WHERE
	 * @param int $limit (option) จำนวนรายการที่ต้องการลบ
	 */
	public function delete($table, $where, $limit = 1)
	{
		$where = $this->buildWhere($where);
		if (is_array($where)) {
			$values = $where[1];
			$where = $where[0];
		} else {
			$values = array();
		}
		$sql = 'DELETE FROM `'.$table.'` WHERE '.$where;
		if (is_int($limit) && $limit > 0) {
			$sql .= ' LIMIT '.$limit;
		}
		return $this->doQuery($sql, $values) === false ? false : true;
	}

	/**
	 * ฟังก์ชั่นประมวลผลคำสั่ง SQL จาก query builder
	 *
	 * @return boolean|array
	 */
	public function execQuery($sqls, $values = array(), $cache = null)
	{
		$sql = $this->makeQuery($sqls);
		if (isset($sqls['values'])) {
			$values = \Arraytool::replace($sqls['values'], $values);
		}
		if ($sqls['function'] == 'customQuery') {
			$result = $this->customQuery($sql, true, $values, $cache);
		} else {
			$result = $this->query($sql, $values);
		}
		return $result;
	}

	/**
	 * จำนวนฟิลด์ทั้งหมดในผลลัพท์จากการ query
	 *
	 * @return int
	 */
	public function fieldCount()
	{
		return 0;
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
			// query table fields
			$result = $this->doCustomQuery("SHOW COLUMNS FROM `$table`");
			if ($result === false) {
				$this->sendError(__FUNCTION__, $this->error_message);
			} else {
				foreach ($result as $item) {
					if (strtolower($item['Field']) == $field) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * ฟังก์ชั่น query ข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $where query WHERE
	 * @return array|bool พบคืนค่ารายการที่พบเพียงรายการเดียว ไม่พบคืนค่า false
	 */
	public function first($table, $where)
	{
		$result = $this->select($table, $where, array(), 1);
		return sizeof($result) == 1 ? $result[0] : false;
	}

	/**
	 * รายชื่อฟิลด์ทั้งหมดจากผลัพท์จองการ query
	 *
	 * @return array
	 */
	public function getFileds()
	{
		return array();
	}

	/**
	 * ฟังก์ชั่นเพิ่มข้อมูลใหม่ลงในตาราง
	 *
	 * @param string $table ชื่อตาราง
	 * @param array $recArr ข้อมูลที่ต้องการบันทึก
	 * @return int|boolean สำเร็จ คืนค่า id ที่เพิ่ม ผิดพลาด คืนค่า false
	 */
	public function insert($table, $recArr)
	{

	}

	/**
	 * ฟังก์ชั่นอ่าน ID ล่าสุดของตาราง สำหรับตารางที่มีการกำหนด Auto_increment ไว้.
	 *
	 * @param string $table ชื่อตาราง
	 * @return int คืนค่า id ล่าสุดของตาราง
	 */
	public function lastId($table)
	{
		$sql = "SHOW TABLE STATUS LIKE '$table'";
		$result = $this->doCustomQuery($sql);
		return sizeof($result) == 1 ? (int)$result[0]['Auto_increment'] : 0;
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
		if (!empty($this->settings->log)) {
			$datas = array();
			$datas[] = $type.' : <em>'.\String::replaceAll($sql, $values).'</em>';
			foreach (debug_backtrace() as $a => $item) {
				if (isset($item['file']) && isset($item['line'])) {
					$f = $item['function'];
					if ($f == 'all' || $f == 'first' || $f == 'count' || $f == 'save' || $f == 'find' || $f == 'execute') {
						$datas[] = '<br>['.$a.'] <b>'.$f.'</b> in <b>'.$item['file'].'</b> line <b>'.$item['line'].'</b>';
						break;
					}
				}
			}
			// ไฟล์ debug
			$debug = ROOT_PATH.\Kotchasan::$data_folder.'debug.php';
			// save
			if (is_file($debug)) {
				$f = fopen($debug, 'a');
			} else {
				$f = fopen($debug, 'w');
				fwrite($f, '<'.'?php exit() ?'.'>');
			}
			fwrite($f, "\n".\Kotchasan::$mktime.'|'.preg_replace('/[\s\n\t\r]+/', ' ', implode('', $datas)));
			fclose($f);
		}
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
		$result = $this->doQuery($sql, $values);
		if (!$result) {
			$this->sendError($sql, $this->error_message);
		}
		return $result;
	}

	/**
	 * ฟังก์ชั่นอ่านจำนวน query ทั้งหมดที่ทำงาน.
	 *
	 * @return int
	 */
	public function queryCount()
	{
		return $this->time;
	}

	/**
	 * เรียกดูข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $where query WHERE
	 * @param array $sort เรียงลำดับ
	 * @param int $limit จำนวนข้อมูลที่ต้องการ
	 * @param \Core\Database\Cache $cache  database cache class default null
	 * @return array ผลลัพท์ในรูป array ถ้าไม่สำเร็จ คืนค่าแอเรย์ว่าง
	 */
	public function select($table, $where, $sort = array(), $limit = 0, $cache = null)
	{

	}

	/**
	 * ฟังก์ชั่นจัดการ error ของ database
	 *
	 * @param string $sql
	 * @param string $message
	 */
	protected function sendError($sql, $message)
	{
		$trace = debug_backtrace();
		$trace = next($trace);
		log_message($sql, $message, $trace['file'], $trace['line']);
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
		return $this->query("TRUNCATE TABLE $table") === false ? false : true;
	}

	/**
	 * ฟังก์ชั่นแก้ไขข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param array $recArr ข้อมูลที่ต้องการบันทึก รูปแบบ array('key1'=>'value1', 'key2'=>'value2', ...)
	 * @return boolean สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	public function update($table, $condition, $recArr)
	{

	}

	/**
	 * อัปเดทข้อมูลทุก record
	 *
	 * @param array $recArr ข้อมูลที่ต้องการบันทึก
	 * array('key1'=>'value1', 'key2'=>'value2', ...)
	 * @return boolean สำเร็จ คืนค่า true, ผิดพลาด คืนค่า false
	 */
	public function updateAll($table, $recArr)
	{
		return $this->update($table, array(1, 1), $recArr);
	}
}