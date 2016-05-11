<?php
/*
 * @filesource Kotchasan/Http/NotFound.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

/**
 * Response Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class NotFound extends Response
{

	/**
	 * Send HTTP Error 404
	 *
	 * @param string||null $message ถ้าไม่กำหนดจะใช้ข้อความจากระบบ
	 * @param int $code Error Code (default 404)
	 */
	public function __construct($message = null, $code = 404)
	{
		parent::__construct($code, $message);
		$response = $this->withProtocolVersion('1.0')->withAddedHeader('Status', '404 Not Found');
		if ($message) {
			$response->setContent($message);
		}
		$response->send();
	}
}