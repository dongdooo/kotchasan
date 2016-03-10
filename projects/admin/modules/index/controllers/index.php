<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Http\Request;
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
	 *
	 * @param Request $request
	 */
	public function index(Request $request)
	{
		// session cookie
		$request->inintSession();
		// ตรวจสอบการ login
		Login::create();
		if (Login::isMember()) {
			var_dump($_SESSION);
		} else {
			// forgot or login
			if ($request->get('action')->toString() == 'forgot') {
				$main = new \Index\Forgot\Controller();
			} else {
				$main = new \Index\Login\Controller();
			}
			echo $main->render();
		}
	}
}