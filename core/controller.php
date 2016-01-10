<?php
/*
 * @filesource core/controller.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Controller base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends KBase
{

	/**
	 * สร้าง View สำหรับ Controller นี้
	 *
	 * @param string $view ชื่อของ View
	 * @return \View
	 */
	public function createView($view)
	{
		return new $view($this);
	}
}