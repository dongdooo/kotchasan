<?php
/*
 * @filesource core/load.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
/**
 *  เวลาเริ่มต้นในการประมวลผลเว็บไซต์
 */
if (!defined('BEGIN_TIME')) {
	define('BEGIN_TIME', microtime(true));
}

/**
 * การแสดงข้อผิดพลาด
 * 0 บันทึกข้อผิดพลาดร้ายแรงลง error_log .php (ขณะใช้งานจริง)
 * 1 บันทึกข้อผิดพลาดและคำเตือนลง error_log .php
 * 2 แสดงผลข้อผิดพลาดและคำเตือนออกทางหน้าจอ (เฉพาะตอนออกแบบเท่านั้น)
 *
 * $var integer
 */
if (!defined('DEBUG')) {
	define('DEBUG', 0);
}
/* display error */
if (DEBUG > 0) {
	/* ขณะออกแบบ แสดง error และ warning ของ PHP */
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(-1);
} else {
	/* ขณะใช้งานจริง */
	error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
}
/**
 * Framework Version
 *
 * @var string
 */
define('VERSION', '0.6.0');
/**
 * กำหนดการบันทึกการ query ฐานข้อมูล
 * ควรกำหนดเป็น false ขณะใช้งานจริง
 *
 * $var boolean
 */
if (!defined('DB_LOG')) {
	define('DB_LOG', false);
}
/**
 *  document root (Server)
 */
if (!defined('DOC_ROOT')) {
	define('DOC_ROOT', str_replace('\\', '/', (substr($_SERVER['DOCUMENT_ROOT'], -1) == DIRECTORY_SEPARATOR) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['DOCUMENT_ROOT'].'/'));
}
/**
 * root ของเว็บไซต์
 */
if (!defined('APP_ROOT')) {
	define('APP_ROOT', APP_PATH);
}
/**
 * พาธของ Server ตั้งแต่ระดับราก เช่น D:/htdocs/gcms/
 */
if (!defined('ROOT_PATH')) {
	define('ROOT_PATH', str_replace('core/load.php', '', str_replace('\\', '/', __FILE__)));
}
/**
 *  http:// หรือ https://
 */
if (!defined('URL_SCHEME')) {
	define('URL_SCHEME', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://'));
}
/**
 *  domain ของ Server เช่น http://domain.tld/
 */
if (!defined('HOST_NAME')) {
	define('HOST_NAME', URL_SCHEME.(empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']).'/');
}
/**
 * URL ของเว็บไซต์รวม path เช่น http://domain.tld/folder
 */
if (!defined('WEB_URL')) {
	define('WEB_URL', HOST_NAME.str_replace(DOC_ROOT, '', APP_ROOT));
}
/**
 * โฟลเดอร์ของเว็บ ตั้งแต่ DOCUMENT_ROOT
 * เช่น kotchasan/
 */
if (!defined('BASE_PATH')) {
	define('BASE_PATH', str_replace(DOC_ROOT, '', APP_ROOT));
}
/**
 * โฟลเดอร์เก็บข้อมูล
 */
if (!defined('DATA_FOLDER')) {
	define('DATA_FOLDER', 'datas/');
}
/**
 * โฟลเดอร์เก็บ Template
 */
if (!defined('TEMPLATE_ROOT')) {
	define('TEMPLATE_ROOT', APP_PATH);
}

/**
 * ฟังก์ชั่นใช้สำหรับสร้างคลาส
 *
 * @param string $className ชื่อคลาส
 * @return \static
 */
function createClass($className)
{
	return new $className();
}

/**
 * custom error handler
 * ถ้าอยู่ใน mode debug จะแสดง error ถ้าไม่จะเขียนลง log อย่างเดียว
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 */
function _error_handler($errno, $errstr, $errfile, $errline)
{
	switch ($errno) {
		case E_WARNING:
			$type = 'PHP warning';
			break;
		case E_NOTICE:
			$type = 'PHP notice';
			break;
		case E_USER_ERROR:
			$type = 'User error';
			break;
		case E_USER_WARNING:
			$type = 'User warning';
			break;
		case E_USER_NOTICE:
			$type = 'User notice';
			break;
		case E_RECOVERABLE_ERROR:
			$type = 'Recoverable error';
			break;
		default:
			$type = 'PHP Error';
	}
	\Core\Log\Logger::create()->error('<br>'.$type.' : <em>'.$errstr.'</em> in <b>'.$errfile.'</b> on line <b>'.$errline.'</b>');
}

/**
 * custom exception handler
 *
 * @param Exception $e
 */
function _exception_handler($e)
{
	$tract = $e->getTrace();
	if (empty($tract)) {
		$tract = array(
			'file' => $e->getFile(),
			'line' => $e->getLine()
		);
	} else {
		$tract = next($tract);
	}
	\Core\Log\Logger::create()->error('<br>Exception : <em>'.$e->getMessage().'</em> in <b>'.$tract['file'].'</b> on line <b>'.$tract['line'].'</b>');
}
if (DEBUG != 2) {
	set_error_handler('_error_handler');
	set_exception_handler('_exception_handler');
}

/**
 * base class
 */
include ROOT_PATH.'core/kbase.php';
include ROOT_PATH.'core/kotchasan.php';
include ROOT_PATH.'core/config.php';
include ROOT_PATH.'core/input.php';

/**
 * โหลดคลาสโดยอัตโนมัติตามชื่อของ Classname เมื่อมีการเรียกใช้งานคลาส
 * PSR-4
 *
 * @param string $className
 */
function autoload($className)
{
	$className = str_replace('\\', '/', strtolower($className));
	if (preg_match('/([a-z]+)\/([a-z0-9]+)\/([a-z]+)/', $className, $match)) {
		if (is_file(APP_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php')) {
			include APP_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php';
			unset($className);
		} elseif (is_file(ROOT_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php')) {
			include ROOT_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php';
			unset($className);
		}
	}
	if (isset($className)) {
		if (preg_match('/[a-z]+/', $className) && is_file(ROOT_PATH.'core/'.$className.'.php')) {
			include ROOT_PATH.'core/'.$className.'.php';
		} elseif (preg_match('/core\/([a-z]+)interface/', $className, $match) && is_file(ROOT_PATH.'core/interfaces/'.$match[1].'interface.php')) {
			include ROOT_PATH.'core/interfaces/'.$match[1].'interface.php';
		} elseif (preg_match('/[\/a-z0-9]+/', $className) && is_file(ROOT_PATH.$className.'.php')) {
			include ROOT_PATH.$className.'.php';
		}
	}
}
spl_autoload_register('autoload');
