<?php
/**
 * @filesource core/orm/field.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/desktop
 */

namespace Core\Orm;

use \Core\Orm\Recordset as Recordset;

/**
 * ORM Field base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Field extends \KBase
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
	 * @var boolean
	 */
	protected $exists;
	/**
	 * ชื่อฟิลด์ที่จะใช้เป็น Primary Key INT(11)
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
	 * create new Model
	 *
	 * @param array $param
	 * @param \Core\Database\Cache $cache  database cache class default null
	 */
	public function __construct($param = null, $cache = null)
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
	 * create new model
	 *
	 * @return \Core\Orm\Field
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
		$class = get_called_class();
		$recordset = Recordset::create($class);
		return $recordset->delete(array($this->primaryKey, $this->getAttribute($this->primaryKey)), 1);
	}

	/**
	 * insert or update record
	 */
	public function save()
	{
		$class = get_called_class();
		$recordset = Recordset::create($class);
		if ($this->exists) {
			$recordset->update(array($this->primaryKey, (int)$this->getAttribute($this->primaryKey)), $this);
		} else {
			$recordset->insert($this);
		}
	}
}