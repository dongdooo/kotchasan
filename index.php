<?php
define('BEGIN_TIME', microtime(true));
/* ขณะออกแบบ แสดง error และ warning ของ PHP */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);
/**
 * index.php.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
// ตัวแปรที่จำเป็นสำหรับ Framework ใช้ระบุ root folder
define('APP_PATH', dirname(__FILE__).'/');
// debug mode
define('DEBUG', false);
// log
define('LOG', false);
// load Kotchasan
include APP_PATH.'core/load.php';
// inint Kotchasan Framework
Kotchasan::createWebApplication()->run();
if (\Input::get($_GET, 'skin') == 'benchmark') {
	printf(
	"\n%' 8d:%f", memory_get_peak_usage(true), microtime(true) - BEGIN_TIME
	);
}