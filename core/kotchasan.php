<?php
/**
 * @filesource core/kotchasan.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Kotchasan PHP Framework
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
final class Kotchasan extends KBase
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
	private static $char_set = 'utf-8';
	/**
	 * Controller หลัก
	 *
	 * @var string
	 */
	private static $defaultController = 'Index\Index\Controller';
	/**
	 * Router หลัก
	 *
	 * @var string
	 */
	private static $defaultRouter = 'Router';

	/**
	 * เรียกใช้งาน Class แบบสามารถเรียกได้ครั้งเดียวเท่านั้น
	 *
	 * @param array $config ค่ากำหนดของ แอพพลิเคชั่น
	 * @return Singleton
	 */
	public function __construct()
	{
		/* display error */
		if (defined('DEBUG') && DEBUG === true) {
			/* ขณะออกแบบ แสดง error และ warning ของ PHP */
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(-1);
		} else {
			/* ขณะใช้งานจริง */
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
		}
		/* config */
		self::$cfg = \Config::create();
		/* charset default UTF-8 */
		ini_set('default_charset', self::$char_set);
		if (extension_loaded('mbstring')) {
			mb_internal_encoding(self::$char_set);
		}
		/* inint Input */
		Input::normalizeRequest();
		// template ที่กำลังใช้งานอยู่
		Template::inint(Input::get($_GET, 'skin', self::$cfg->skin));
		/* time zone default Thailand */
		@date_default_timezone_set(self::$cfg->timezone);
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
	 * โหลด GCMS เพื่อแสดงผลหน้าเว็บไซต์
	 */
	public function run()
	{
		return createClass(self::$defaultRouter)->inint(self::$defaultController);
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