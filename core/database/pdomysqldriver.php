<?php
/**
 * @filesource core/database/pdomysqldriver.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Database driver class
 */
use Core\Database\Driver as Driver;

/**
 * PDO MySQL Database Adapter Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class PdoMysqlDriver extends Driver
{

	/**
	 * close database.
	 */
	protected function _close()
	{

	}

	/**
	 * เชื่อมต่อ database
	 *
	 * @param array $param
	 * @return \PdoMysqlDriver
	 */
	public function connect($param)
	{
		foreach ($param as $key => $value) {
			$this->$key = $value;
		}
		$options = array();
		$options[PDO::ATTR_PERSISTENT] = true;
		$options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
		if ($this->settings->dbdriver == 'mysql') {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES '.$this->settings->char_set;
		}
		try {
			$sql = $this->settings->dbdriver.':host='.$this->settings->hostname;
			$sql .= empty($this->settings->port) ? '' : ';port='.$this->settings->port;
			$sql .= empty($this->settings->dbname) ? '' : ';dbname='.$this->settings->dbname;
			$this->connection = new PDO($sql, $this->settings->username, $this->settings->password, $options);
			return $this;
		} catch (PDOException $e) {
			$this->sendError(__FUNCTION__, $e->getMessage());
		}
	}

	/**
	 * ประมวลผลคำสั่ง SQL สำหรับสอบถามข้อมูล คืนค่าผลลัพท์เป็นแอเรย์ของข้อมูลที่ตรงตามเงื่อนไข.
	 *
	 * @param string $sql query string
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @param \Core\Database\Cache $cache  database cache class default null
	 * @return array คืนค่าผลการทำงานเป็น record ของข้อมูลทั้งหมดที่ตรงตามเงื่อนไข
	 */
	protected function doCustomQuery($sql, $values = array(), $cache = null)
	{
		if ($cache && $cache->action > 0) {
			$result = $cache->get($sql, $values);
		} else {
			$result = false;
		}
		if (!$result) {
			try {
				if (empty($values)) {
					$this->result_id = $this->connection->query($sql);
				} else {
					$this->result_id = $this->connection->prepare($sql);
					$this->result_id->execute($values);
				}
				self::$query_count++;
				$result = $this->result_id->fetchAll(PDO::FETCH_ASSOC);
				if ($cache && $cache->action == 1) {
					$cache->save($result);
				}
			} catch (PDOException $e) {
				$this->error_message = $e->getMessage();
				$result = array();
			}
			$this->used_cache = false;
		} else {
			$this->used_cache = true;
		}
		$this->log($this->used_cache ? 'Cached' : 'Database', $sql, $values);
		return $result;
	}

	/**
	 * ประมวลผลคำสั่ง SQL ที่ไม่ต้องการผลลัพท์ เช่น CREATE INSERT UPDATE.
	 *
	 * @param string $sql
	 * @param array $values ถ้าระบุตัวแปรนี้จะเป็นการบังคับใช้คำสั่ง prepare แทน query
	 * @return boolean สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false
	 */
	protected function doQuery($sql, $values = array())
	{
		try {
			if (empty($values)) {
				$query = $this->connection->query($sql);
			} else {
				$query = $this->connection->prepare($sql);
				$query->execute($values);
			}
			self::$query_count++;
			$this->log('Query', $sql, $values);
			return true;
		} catch (PDOException $e) {
			$this->sendError($sql, $e->getMessage());
			return false;
		}
	}

	/**
	 * จำนวนฟิลด์ทั้งหมดในผลลัพท์จากการ query
	 *
	 * @param resource $res ผลลัพท์จากการ query
	 */
	public function fieldCount()
	{
		if (isset($this->result_id)) {
			return $this->result_id->columnCount();
		} else {
			return 0;
		}
	}

	/**
	 * รายชื่อฟิลด์ทั้งหมดจากผลัพท์จองการ query
	 *
	 * @return array
	 */
	public function getFileds()
	{
		$filed_list = array();
		for ($i = 0, $c = $this->fieldCount(); $i < $c; $i++) {
			$result = @$this->result_id->getColumnMeta($i);
			if ($result) {
				$filed_list[$result['name']] = $result;
			}
		}
		$this->log('getFileds', var_export($this->result_id, true));
		return $filed_list;
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
		$keys = array();
		$values = array();
		foreach ($recArr AS $key => $value) {
			$keys[] = $key;
			$values[':'.$key] = $value;
		}
		$sql = 'INSERT INTO `'.$table.'` (`'.implode('`,`', $keys);
		$sql .= '`) VALUES (:'.implode(',:', $keys).')';
		try {
			$query = $this->connection->prepare($sql);
			$query->execute($values);
			self::$query_count++;
			return (int)$this->connection->lastInsertId();
		} catch (PDOException $e) {
			$this->sendError($sql, $e->getMessage());
			return false;
		}
	}

	/**
	 * เรียกดูข้อมูล
	 *
	 * @param string $table ชื่อตาราง
	 * @param mixed $condition query WHERE
	 * @param array $sort เรียงลำดับ
	 * @param int $limit จำนวนข้อมูลที่ต้องการ
	 * @param \Core\Database\Cache $cache  database cache class default null
	 * @return array ผลลัพท์ในรูป array ถ้าไม่สำเร็จ คืนค่าแอเรย์ว่าง
	 */
	public function select($table, $condition, $sort = array(), $limit = 0, $cache = null)
	{
		$values = array();
		$condition = $this->buildWhere($condition);
		if (is_array($condition)) {
			$values = $condition[1];
			$condition = $condition[0];
		}
		$sql = 'SELECT * FROM `'.$table.'` WHERE '.$condition;
		if (!empty($sort)) {
			$qs = array();
			foreach ($sort as $item) {
				if (preg_match('/^([a-z0-9_]+)\s(asc|desc)$/i', trim($item), $match)) {
					$qs[] = '`'.$match[1].'`'.(empty($match[2]) ? '' : ' '.$match[2]);
				}
			}
			if (sizeof($qs) > 0) {
				$sql .= ' SORT '.implode(', ', $qs);
			}
		}
		if (is_int($limit) && $limit > 0) {
			$sql .= ' LIMIT '.$limit;
		}
		$result = $this->doCustomQuery($sql, $values, $cache);
		if ($result === false) {
			$this->sendError($sql, $this->error_message);
			return array();
		} else {
			return $result;
		}
	}

	/**
	 * เลือกฐานข้อมูล.
	 *
	 * @param string $database
	 * @return bool false หากไม่สำเร็จ
	 */
	public function selectDB($database)
	{
		$this->settings->dbname = $database;
		$result = $this->connection->query("USE $database");
		return $result === false ? false : true;
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
		$sets = array();
		$values = array();
		foreach ($recArr AS $key => $value) {
			$sets[] = '`'.$key.'` = :'.$key;
			$values[':'.$key] = $value;
		}
		$condition = $this->buildWhere($condition);
		if (is_array($condition)) {
			$values = \Arraytool::replace($values, $condition[1]);
			$condition = $condition[0];
		}
		$sql = 'UPDATE `'.$table.'` SET '.implode(', ', $sets).' WHERE '.$condition;
		try {
			$query = $this->connection->prepare($sql);
			$query->execute($values);
			self::$query_count++;
			return true;
		} catch (PDOException $e) {
			$this->sendError($sql, $e->getMessage());
			return false;
		}
	}
}