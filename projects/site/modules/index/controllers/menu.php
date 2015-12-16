<?php
/**
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

/**
 * default Controller
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Controller
{
	/*
	 * inint Controller.
	 *
	 * @param array $modules
	 *
	 * @return string
	 */

	public function render($module)
	{
		// สร้างเมนู
		return $this->createView('Index\Menu\View')->render($module);
	}
}