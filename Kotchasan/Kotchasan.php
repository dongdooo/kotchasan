<?php
/*
 * @filesource Kotchasan/Kotchasan.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\KBase;

/**
 * Kotchasan PHP Framework
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Kotchasan extends KBase
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
	protected static $char_set = 'utf-8';
	/**
	 * Controller หลัก
	 *
	 * @var string
	 */
	protected static $defaultController = 'Index\Index\Controller';
	/**
	 * Router หลัก
	 *
	 * @var string
	 */
	protected static $defaultRouter = 'Kotchasan\Router';

	/**
	 * Singleton
	 */
	private function __construct()
	{
		/* config */
		self::$cfg = Config::create();
		/* charset default UTF-8 */
		ini_set('default_charset', self::$char_set);
		if (extension_loaded('mbstring')) {
			mb_internal_encoding(self::$char_set);
		}
		/* time zone default Thailand */
		@date_default_timezone_set(self::$cfg->timezone);
		/* remove slashes (/) ตัวแปร GLOBAL  */
		//Input::normalizeRequest();
	}

	/**
	 * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น
	 *
	 * @return \static
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
		return \createClass(self::$defaultRouter)->inint(self::$defaultController);
	}

	/**
	 * ฟังก์ชั่นเริ่มต้นใช้งาน session
	 */
	public static function inintSession()
	{
		if (isset($_GET['sessid']) && preg_match('/[a-zA-Z0-9]{20,}/', $_GET['sessid'])) {
			session_id($_GET['sessid']);
		}
		session_start();
		if (!ob_get_status()) {
			if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
				// เปิดใช้งานการบีบอัดหน้าเว็บไซต์
				ob_start('ob_gzhandler');
			} else {
				ob_start();
			}
		}
		return true;
	}
}