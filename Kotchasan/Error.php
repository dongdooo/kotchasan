<?php
/*
 * @filesource Kotchasan/Error.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

/**
 * Error Controller class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Error
{

	/**
	 * send http header message and exit
	 *
	 * @param string $message
	 * @param int $code
	 */
	public static function send($message = 'File Not Found!', $code = 404)
	{
		http_response_code($code);
		echo $message;
		exit;
	}
}