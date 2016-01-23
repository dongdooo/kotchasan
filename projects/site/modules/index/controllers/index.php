<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Template;
use \Kotchasan\Date;

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
		Template::inint(self::$cfg->skin);
		// ถ้าไม่มีโมดูลเลือกหน้า home
		$module = $this->request->get('module', 'home')->toString();
		// สร้าง View
		$view = $this->createView('\Kotchasan\View');
		// template default
		$view->setContents(array(
			// menu
			'/{MENU}/' => createClass('Index\Menu\Controller', $this->request)->render($module),
			// web title
			'/{TITLE}/' => self::$cfg->web_title,
			// โหลดหน้าที่เลือก (html)
			'/{CONTENT}/' => Template::load('', '', $module),
			// แสดงเวลาปัจจุบัน
			'/{TIME}/' => Date::format()
		));
		// output เป็น HTML
		$view->renderHTML();
	}
}