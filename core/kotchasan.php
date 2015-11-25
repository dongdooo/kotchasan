<?php
/**
 * @filesource core/kotchasan.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
/**
 * Framework Version
 *
 * @var string
 */
define('VERSION', '0.6.0');
/**
 * FORMAT_PCRE set แบบ PCRE
 */
define('FORMAT_PCRE', 0);
/**
 * FORMAT_TEXT set แบบ text
 */
define('FORMAT_TEXT', 1);

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
	 * default charset (แนะนำ utf-8)
	 *
	 * @var string
	 */
	public static $char_set = 'utf-8';
	/**
	 * class Config เก็บค่ากำหนดของเว็บไซต์
	 *
	 * @var \Config
	 */
	public static $config;
	/**
	 * โฟลเดอร์เก็บข้อมูลของเว็บไซต์
	 *
	 * @var string default datas/
	 */
	public static $data_folder = 'datas/';
	/**
	 * กำหนดค่าการแสดงผล error และ warnning ของ PHP
	 * ถ้ากำหนดเป็น true จะแสดงผลข้อผิดพลาดทั้งหมดทางหน้าเว็บไซต์
	 * แนะนำให้เปิดใช้ในขณะออกแบบเท่านั้น
	 *
	 * @var boolean
	 */
	public static $debug = true;
	/**
	 * Controller หลัก
	 *
	 * @var string
	 */
	public static $defaultController = 'Index\Index\Controller';
	/**
	 * Router หลัก
	 *
	 * @var string
	 */
	public static $defaultRouter = 'Router';
	/**
	 * current instance
	 *
	 * @var \Kotchasan
	 */
	private static $instance;
	/**
	 * class ภาษา
	 *
	 * @var \Language
	 */
	public static $language;
	/**
	 * ขนาดสูงสุดของไฟล์ log
	 * 1048576 = 1M
	 *
	 * @var int
	 */
	public static $log_file_size = 1048576;
	/**
	 * เวลา Unix time stamp
	 *
	 * @var int
	 */
	public static $mktime;
	/**
	 * เดือนนี้
	 *
	 * @var int
	 */
	public static $month;
	/**
	 * กฏของ Router สำหรับการแยกหน้าเว็บไซต์
	 *
	 * @var array
	 */
	public static $router_rules;
	/**
	 * ชื่อ template ที่กำลังใช้งานอยู่ รวมโฟลเดอร์ที่เก็บ template ด้วย
	 * เช่น skin/default/
	 *
	 * @var string
	 */
	public static $template;
	/**
	 * โฟลเดอร์รากของ template
	 * ปกติจะเป็นโฟลเดอร์เดียวกันกับโฟลเดอร์ของ project
	 *
	 * @var string
	 */
	public static $template_root = APP_PATH;
	/**
	 * วันที่ วันนี้
	 *
	 * @var int
	 */
	public static $today;
	/**
	 * รูปแบบของ URL สัมพันธ์กันกับ router_rules
	 *
	 * @var array
	 */
	public static $urls;
	/**
	 * โฟลเดอร์เก็บไฟล์รูปภาพประจำตัวสมาชิก
	 *
	 * @var string default datas/member/
	 */
	public static $userion_folder = 'datas/member/';
	/**
	 * ปีนี้
	 *
	 * @var int
	 */
	public static $year;

	/**
	 * class construstor
	 */
	public function __construct()
	{
		/* display error */
		if (self::$debug) {
			/* ขณะออกแบบ แสดง error และ warning ของ PHP */
			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
			error_reporting(-1);
		} else {
			/* ขณะใช้งานจริง */
			error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
		}
		/* โหลด config */
		\Kotchasan::$config = new Config;
		/* charset default UTF-8 */
		ini_set('default_charset', self::$char_set);
		if (extension_loaded('mbstring')) {
			mb_internal_encoding(self::$char_set);
		}
		/* ค่ากำหนดอื่นๆ */
		self::$urls = array(
			'index.php?module={module}-{document}&amp;cat={catid}&amp;id={id}',
			'{module}/{catid}/{id}/{document}.html'
		);
		self::$router_rules = array(
			// index/model/page/function
			'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)\/([a-z0-9_]+)/i' => array('module', 'method', 'page', 'function'),
			// index/model/action
			'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)/i' => array('module', 'method', 'page'),
			// module/action/cat/id
			'/^([a-z]+)\/([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'action', 'cat', 'id'),
			// module/action/cat
			'/^([a-z]+)\/([a-z]+)\/([0-9]+)$/' => array('module', 'action', 'cat'),
			// module/cat/id
			'/^([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'cat', 'id'),
			// module/cat
			'/^([a-z]+)\/([0-9]+)$/' => array('module', 'cat'),
			// module/document
			'/^([a-z]+)\/(.*)?$/' => array('module', 'document'),
			// module, module.php
			'/^([a-z0-9_]+)(\.php)?$/' => array('module'),
			// document
			'/^(.*)$/' => array('document')
		);
		/* inint Input */
		Input::normalizeRequest();
		// template ที่กำลังใช้งานอยู่
		$skin = Input::get($_GET, 'skin', \Kotchasan::$config->skin);
		self::$template = 'skin/'.($skin == '' ? '' : $skin.'/' );
		/* time zone default Thailand */
		date_default_timezone_set(\Kotchasan::$config->timezone);
		/* เวลา ปัจจุบัน รูปแบบ Unix time stamp */
		\Kotchasan::$mktime = mktime(date('H'));
		self::$today = (int)date('j', \Kotchasan::$mktime);
		self::$month = (int)date('n', \Kotchasan::$mktime);
		self::$year = (int)date('Y', \Kotchasan::$mktime);
	}

	/**
	 * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น
	 *
	 * @return object
	 */
	public static function createWebApplication()
	{
		if (!isset(self::$instance)) {
			self::$instance = new static;
		}
		return self::$instance;
	}

	/**
	 * โหลด GCMS เพื่อแสดงผลหน้าเว็บไซต์
	 */
	public function run()
	{
		return $this->createClass(self::$defaultRouter)->inint();
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

	/**
	 * แสดงผล Widget
	 *
	 * @param array $matches
	 */
	public static function getWidgets($matches)
	{
		unset($matches[0]);
		return implode(' ', $matches);
	}
}