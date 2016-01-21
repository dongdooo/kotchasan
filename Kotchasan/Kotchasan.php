<?php
/*
 * @filesource Kotchasan/Kotchasan.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

use \Kotchasan\Http\Server;
use \Kotchasan\Config;

/**
 * Kotchasan PHP Framework
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Kotchasan extends \Kotchasan\KBase
{
	/**
	 * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
	 */
	private static $instance = null;
	/**
	 * default charset (แนะนำ utf-8)
	 *
	 * @var string
	 */
	private $char_set = 'utf-8';
	/**
	 * Controller หลัก
	 *
	 * @var string
	 */
	private $defaultController = 'Index\Index\Controller';
	/**
	 * Router หลัก
	 *
	 * @var string
	 */
	private $defaultRouter = 'Kotchasan\Router';

	/**
	 * Singleton
	 */
	private function __construct()
	{
		/* create Server  */
		self::$server = new Server;
		self::$cfg = Config::create();
		/* charset */
		ini_set('default_charset', $this->char_set);
		if (extension_loaded('mbstring')) {
			mb_internal_encoding($this->char_set);
		}
		/* time zone */
		@date_default_timezone_set(self::$cfg->timezone);
	}

	/**
	 * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น
	 *
	 * @return self
	 */
	public static function &createWebApplication()
	{
		if (null === self::$instance) {
			self::$instance = new static;
		}
		return self::$instance;
	}

	/**
	 * แสดงผลหน้าเว็บไซต์
	 */
	public function run()
	{
		createClass($this->defaultRouter)->inint($this->defaultController);
	}
}