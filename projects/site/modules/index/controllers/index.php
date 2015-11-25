<?php
/**
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

/**
 * default Controller
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Controller
{

	/**
	 * inint index
	 */
	public function index()
	{
		// ถ้าไม่มีโมดูลเลือกหน้า home
		$module = empty($_GET['module']) ? 'home' : $_GET['module'];
		// สร้าง View
		$view = $this->createClass('Index\Index\View');
		// template default
		$view->add(array(
			// menu
			'MENU' => $this->createClass('Index\Menu\Controller')->render($module),
			// web title
			'TITLE' => 'Welcome to GCMS++',
			// โหลดหน้าที่เลือก (html)
			'CONTENT' => \Template::load('', '', $module),
			// แสดงเวลาปัจจุบัน
			'TIME' => \Datetool::format(\Kotchasan::$mktime)
		));
		// output เป็น HTML
		$view->renderHTML();
	}
}