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
			$param = (object)array(
				'settings' => (object)array(
					'char_set' => 'utf8',
					'dbdriver' => 'mysql',
					'hostname' => '127.0.0.1'
				),
				'tables' => (object)array(
					
				)
			);
			if (is_file(APP_PATH.'settings/database.php')) {
				$config = include APP_PATH.'settings/database.php';
			} elseif (is_file(APP_ROOT.'settings/database.php')) {
				$config = include APP_ROOT.'settings/database.php';
			}
			if (isset($config)) {
				foreach ($config as $key => $values) {
					if ($key == $name) {
						foreach ($values as $k => $v) {
							$param->settings->$k = $v;
						}
					} elseif ($key == 'tables') {
						foreach ($values as $k => $v) {
							$param->tables->$k = $v;
						}
					}
				}
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