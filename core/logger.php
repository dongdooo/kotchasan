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
	protected static $instance = null;
	/**
	 * รูปแบบของ log
	 *
	 * @var array
	 */
	protected $options = array(
		'dateFormat' => 'Y-m-d G:i:s',
		'logFormat' => '[%datetime%] %level%: %message% %context%'
	);
	/**
	 * Log Levels
	 *
	 * @var array
	 */
	protected $logLevels = array(
		LogLevel::EMERGENCY => 0,
		LogLevel::ALERT => 1,
		LogLevel::CRITICAL => 2,
		LogLevel::ERROR => 3,
		LogLevel::WARNING => 4,
		LogLevel::NOTICE => 5,
		LogLevel::INFO => 6,
		LogLevel::DEBUG => 7
	);

	/**
	 * Singleton
	 */
	private function __construct($options)
	{
		foreach ($options as $key => $value) {
			$this->options[$key] = $value;
		}
	}

	/**
	 * สร้าง Application สามารถเรียกใช้ได้ครั้งเดียวเท่านั้น
	 *
	 * @return \static
	 */
	public static function create(array $options = array())
	{
		if (null === self::$instance) {
			self::$instance = new static($options);
		}
		return self::$instance;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 */
	public function log($level, $message, array $context = array())
	{
		if (\File::makeDirectory(ROOT_PATH.DATA_FOLDER.'logs/')) {
			// save log
			$file = ROOT_PATH.DATA_FOLDER.'logs/'.date('Y-m-d').'.php';
			if (file_exists($file)) {
				$f = fopen($file, 'a');
			} else {
				$f = fopen($file, 'w');
				fwrite($f, '<'.'?php exit() ?'.'>');
			}
			$patt = array(
				'datetime' => date($this->options['dateFormat'], time()),
				'level' => isset($this->logLevels[$level]) ? strtoupper($level) : 'UNKNOW',
				'message' => preg_replace('/[\s\n\t\r]+/', ' ', $message),
				'context' => empty($context) ? '' : json_encode($context)
			);
			$message = $this->options['logFormat'];
			foreach ($patt as $key => $value) {
				$message = str_replace('%'.$key.'%', $value, $message);
			}
			fwrite($f, "\n".$message);
			fclose($f);
		} else {
			echo sprintf('The file or folder %s can not be created or is read-only, please create or adjust the chmod it to 775 or 777.', 'logs/'.date('Y-m-d').'.php');
		}
	}
}