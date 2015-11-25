<?php
/**
 * @filesource core/database/schema.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Database;

/**
 * Database schema
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Schema
{
	/**
	 * รายการ Schema ที่โหลดแล้ว
	 *
	 * @var array
	 */
	protected static $tables = array();
	/**
	 * Database object
	 *
	 * @var	object
	 */
	private $db;
	/**
	 * ชื่อตาราง
	 *
	 * @var	string
	 */
	private $table;

	/**
	 * Class constructor
	 *
	 * @param object $db
	 * @return void
	 */
	public function __construct($db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}

	/**
	 * อ่านข้อมูล Schema จากตาราง
	 *
	 * @param \Core\Database\Cache $cache
	 */
	public function inint($cache = null)
	{
		$table = $this->table;
		if (isset(self::$tables[$table])) {
			$columns = self::$tables[$table];
		} else {
			if ($cache instanceof \Core\Database\Cache && $cache->action > 0) {
				$cache = new \Core\Database\Cache();
				$cache->action = 1;
			}
			$sql = "SHOW FULL COLUMNS FROM `$table`";
			$columns = $this->db->customQuery($sql, true, array(), $cache);
			$datas = array();
			foreach ($columns as $column) {
				$datas[$column['Field']] = $column;
			}
			self::$tables[$table] = $datas;
		}
	}

	/**
	 * อ่านรายชื่อฟิลด์ของตาราง
	 *
	 * @param \Core\Database\Cache $cache
	 * @return array รายชื่อฟิลด์ทั้งหมดในตาราง
	 */
	public function fields($cache = null)
	{
		$columns = $this->inint($cache);
		return array_keys(self::$tables[$this->table]);
	}
}