<?php
/*
 * @filesource core/database.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Database class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
final class Database
{
	/**
	 * database connection instances
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Create Database Connection
	 *
	 * @param string $name ชื่อของการเชื่อมต่อกำหนดค่าใน config
	 * @return \static
	 */
	public static function create($name = 'mysql')
	{
		if (empty(self::$instances[$name])) {
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
			$obj = new $class($name);
			self::$instances[$name] = $obj->connect($param);
		}
		return self::$instances[$name];
	}
}