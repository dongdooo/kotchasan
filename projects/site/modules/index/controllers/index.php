<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
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
	 * แสดงผล
	 */
	public function index()
	{
		\Template::inint(self::$cfg->skin);
		// ถ้าไม่มีโมดูลเลือกหน้า home
		$module = \Input::get('module', 'home')->toString();
		// สร้าง View
		$view = $this->createView('Index\Index\View');
		// template default
		$view->setContents(array(
			// menu
			'/{MENU}/' => \createClass('Index\Menu\Controller')->render($module),
			// web title
			'/{TITLE}/' => self::$cfg->web_title,
			// โหลดหน้าที่เลือก (html)
			'/{CONTENT}/' => \Template::load('', '', $module),
			// แสดงเวลาปัจจุบัน
			'/{TIME}/' => \Date::format()
		));
		// output เป็น HTML
		$view->renderHTML();
	}
}