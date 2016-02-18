<?php
/*
 * @filesource Kotchasan/Orm/Field.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/desktop
 */

namespace Kotchasan\Orm;

use \Kotchasan\Orm\Recordset;

/**
 * ORM Field base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Field extends \Kotchasan\KBase
{
	/**
	 * ชื่อของการเชื่อมต่อ ใช้สำหรับโหลด config จาก settings/database.php
	 *
	 * @var string
	 */
	protected $conn = 'mysql';
	/**
	 * true ถ้ามาจากการ query, false ถ้าเป็นรายการใหม่
	 *
	 * @var bool
	 */
	protected $exists;
	/**
	 * ชื่อฟิลด์ที่จะใช้เป็น Primary Key INT(11) AUTO_INCREMENT
	 *
	 * @var string
	 */
	protected $primaryKey = 'id';
	/**
	 * ชื่อตาราง
	 *
	 * @var string
	 */
	protected $table;

	/**
	 * class constructor
	 *
	 * @param array|object $param ข้อมูลเริ่มต้น
	 */
	public function __construct($param = null)
	{
		if (!empty($param)) {
			foreach ($param as $key => $value) {
				$this->$key = $value;
			}
			$this->exists = true;
		} else {
			$this->exists = false;
		}
	}

	/**
	 * สร้าง record
	 *
	 * @return \static
	 */
	public static function create()
	{
		$obj = new static;
		return $obj;
	}

	/**
	 * ลบ record
	 */
	public function delete()
	{
		$rs = new Recordset(get_called_class());
		return $rs->delete(array($this->primaryKey, (int)$this->{$this->primaryKey}), 1);
	}

	/**
	 * insert or update record
	 */
	public function save()
	{
		$rs = new Recordset(get_called_class());
		if ($this->exists) {
			$rs->update(array($this->primaryKey, (int)$this->{$this->primaryKey}), $this);
		} else {
			$rs->insert($this);
		}
	}

	/**
	 * อ่านค่าตัวแปร conn (ชื่อของการเชื่อมต่อ)
	 *
	 * @return string
	 */
	public function getConn()
	{
		return $this->conn;
	}

	/**
	 * อ่านชื่อตาราง
	 *
	 * @return string
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * vjkoชื่อฟิลด์ที่เป็น Primary Key
	 *
	 * @return string
	 */
	public function getPrimarykey()
	{
		return $this->primaryKey;
	}
}