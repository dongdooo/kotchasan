<?php
/**
 * @filesource core/database.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Database class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Database
{
	/**
	 * database connection instances
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * Create Database Connection
	 *
	 * @param string $name (option)
	 * @param boolead $cache (option)
	 * @return \Core\Database\Driver
	 */
	public static function create($name = 'mysql')
	{
		if (isset(self::$instances[$name])) {
			$conn = self::$instances[$name];
		} else {
			if (is_file(APP_PATH.'settings/database.php')) {
				$config = include APP_PATH.'settings/database.php';
			} elseif (is_file(APP_ROOT.'settings/database.php')) {
				$config = include APP_ROOT.'settings/database.php';
			}
			$param = new \stdClass();
			foreach ($config as $key => $val) {
				if ($key == $name) {
					$param->settings = (object)$val;
				} elseif ($key == 'tables') {
					$param->tables = (object)$val;
				}
			}
			if (empty($param->settings->char_set)) {
				$param->settings->char_set = 'utf8';
			}
			if (empty($param->settings->dbdriver)) {
				$param->settings->dbdriver = 'mysql';
			}
			// โหลด driver (base)
			include ROOT_PATH.'core/database/driver.php';
			// โหลด driver ตาม config ถ้าไม่พบ ใช้ PdoMysqlDriver
			if (is_file(ROOT_PATH.'core/database/'.$param->settings->dbdriver.'driver.php')) {
				$class = ucwords($param->settings->dbdriver).'Driver';
			} else {
				// default driver
				$class = 'PdoMysqlDriver';
			}
			include ROOT_PATH.'core/database/'.strtolower($class).'.php';
			$db = new $class($name);
			$conn = $db->connect($param);
			self::$instances[$name] = $conn;
		}
		return $conn;
	}
}