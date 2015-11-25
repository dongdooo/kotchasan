<?php
/**
 * @filesource core/model.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * database query base class
 */
use Core\Database\Query as Query;

/**
 * Model base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends Query
{
	/**
	 * ชื่อของการเชื่อมต่อ ใช้สำหรับโหลด config จาก settings/database.php
	 *
	 * @var string
	 */
	protected $conn = 'mysql';

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		if (!isset($this->db)) {
			$this->db = Database::create($this->conn);
			$this->cache = new \Core\Database\Cache();
		}
	}

	/**
	 * create Model
	 *
	 * @return \Model
	 */
	public static function create()
	{
		return new static;
	}
}