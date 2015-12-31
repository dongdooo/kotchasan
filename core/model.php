<?php
/*
 * @filesource core/model.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

use Core\Database\Query;
use Core\Database\DbCache as Cache;

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
			$this->cache = new Cache();
		}
	}

	/**
	 * create Model
	 *
	 * @return \static
	 */
	public static function create()
	{
		return new static;
	}
}