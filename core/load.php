<?php
/**
 * @filesource core/load.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
/**
 *  เวลาเริ่มต้นในการประมวลผลเว็บไซต์
 */
if (!defined('DOC_ROOT')) {
	define('BEGIN_TIME', microtime(true));
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
define('ROOT_PATH', str_replace('core/load.php', '', str_replace('\\', '/', __FILE__)));
/**
 *  http:// หรือ https://
 */
define('URL_SCHEME', ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://'));
/**
 *  domain ของ Server เช่น http://domain.tld/
 */
define('HOST_NAME', URL_SCHEME.(empty($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']).'/');
/**
 * URL ของเว็บไซต์รวม path เช่น http://domain.tld/folder
 */
define('WEB_URL', HOST_NAME.str_replace(DOC_ROOT, '', APP_ROOT));

/**
 * จัดการข้อความผิดพลาด.
 *
 * @param string $message ข้อความผิดพลาด
 */
function log_message($erargs, $errstr, $errfile, $errline)
{
	// ข้อความ error
	$error_msg = '<br>'.$erargs.' : <em>'.$errstr.'</em> in <b>'.$errfile.'</b> on line <b>'.$errline.'</b>';
	// ไฟล์ debug
	$debug = ROOT_PATH.\Kotchasan::$data_folder.'debug.php';
	$exists = false;
	if (file_exists($debug)) {
		if (filesize($debug) > \Kotchasan::$log_file_size) {
			rename($debug, $debug = ROOT_PATH.\Kotchasan::$data_folder.date('Ymd', \Kotchasan::$mktime).'.log');
		} else {
			$exists = true;
		}
	}
	// save
	if ($exists) {
		$f = fopen($debug, 'a');
	} else {
		$f = fopen($debug, 'w');
		fwrite($f, '<'.'?php exit() ?'.'>');
	}
	fwrite($f, "\n".\Kotchasan::$mktime.'|'.preg_replace('/[\s\n\t\r]+/', ' ', $error_msg));
	fclose($f);
}

/**
 * custom error handler
 * ถ้าอยู่ใน mode debug จะแสดง error ถ้าไม่จะเขียนลง log อย่างเดียว
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 *
 * @return boolean
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
	log_message($type, $errstr, $errfile, $errline);
}

/**
 * custom exception handler
 * ถ้าอยู่ใน mode debug จะแสดง error ถ้าไม่จะเขียนลง log อย่างเดียว
 *
 * @param resource $e
 */
function _exception_handler($e)
{
	$tract = $e->getTrace();
	$tract = next($tract);
	log_message('Exception', $e->getMessage(), $tract['file'], $tract['line']);
}
set_error_handler('_error_handler');
set_exception_handler('_exception_handler');

/**
 * โหลดคลาสหลักเตรียมไว้
 */
include ROOT_PATH.'core/kbase.php';
include ROOT_PATH.'core/kotchasan.php';
include ROOT_PATH.'core/router.php';
include ROOT_PATH.'core/input.php';
include ROOT_PATH.'core/controller.php';
include ROOT_PATH.'core/config.php';

/**
 * โหลดคลาสโดยอัตโนมัติตามชื่อของ Classname เมื่อมีการเรียกใช้งานคลาส
 * PSR-4
 *
 * @param string $className
 */
function autoload($className)
{
	$className = str_replace('\\', '/', strtolower($className));
	if (preg_match('/([a-z]+)\/([a-z0-9]+)\/([a-z]+)/i', $className, $match) && is_file(APP_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php')) {
		include APP_PATH.'modules/'.$match[1].'/'.$match[3].'s/'.$match[2].'.php';
	} elseif (preg_match('/[a-z]+/i', $className) && is_file(ROOT_PATH.'core/'.$className.'.php')) {
		include ROOT_PATH.'core/'.$className.'.php';
	} elseif (preg_match('/[\a-z0-9]+/i', $className) && is_file(ROOT_PATH.$className.'.php')) {
		include ROOT_PATH.$className.'.php';
	}
}
spl_autoload_register('autoload');
