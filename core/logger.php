<?php
/*
 * @filesource core/logger.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

use \Psr\Log\LoggerInterface;
use \Psr\Log\LogLevel;

/**
 * Kotchasan Logger Class (PSR-3)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Logger implements LoggerInterface
{
	use \Psr\Log\LoggerTrait;
	/**
	 * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
	 */
	private static $instance = null;

	/**
	 * Singleton
	 */
	private function __construct()
	{
		// do nothing
	}

	/**
	 * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น
	 *
	 * @return \static
	 */
	public static function create()
	{
		if (null === self::$instance) {
			self::$instance = new static;
		}
		return self::$instance;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 *
	 * @return null
	 */
	public function log($level, $message, array $context = array())
	{
		if (\File::makeDirectory(ROOT_PATH.DATA_FOLDER.'logs/')) {
			// ไฟล์ log
			switch ($level) {
				case LogLevel::DEBUG:
				case LogLevel::INFO:
				case LogLevel::ALERT:
					$file = ROOT_PATH.DATA_FOLDER.'logs/'.date('Y-m-d').'.php';
					break;
				default:
					$file = ROOT_PATH.DATA_FOLDER.'logs/error_log.php';
					break;
			}
			// save
			if (file_exists($file)) {
				$f = fopen($file, 'a');
			} else {
				$f = fopen($file, 'w');
				fwrite($f, '<'.'?php exit() ?'.'>');
			}
			fwrite($f, "\n".time().'|'.preg_replace('/[\s\n\t\r]+/', ' ', $message));
			fclose($f);
		} else {
			echo sprintf('The file or folder %s can not be created or is read-only, please create or adjust the chmod it to 775 or 777.', 'logs/'.date('Y-m-d').'.php');
		}
	}
}