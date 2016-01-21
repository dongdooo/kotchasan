<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Login;

/**
 * default Controller
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

	/**
	 * แสดงผล
	 */
	public function index()
	{
		// session cookie
		self::$server->inintSession();
		// ตรวจสอบการ login
		$login = Login::create();
		if (!$login->isMember()) {
			// forgot or login
			if ((string)self::$server->get('action') == 'forgot') {
				$main = new \Index\Forgot\Controller;
			} else {
				$main = new \Index\Login\Controller;
			}
			echo $main->render();
		} else {
			var_dump($_SESSION);
		}
	}
}