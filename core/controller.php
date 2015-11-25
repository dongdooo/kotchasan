<?php
/**
 * @filesource core/controller.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
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
	 * รายการ header
	 *
	 * @var array
	 */
	public $headers = array();
	/**
	 * ตัวแปรเก็บเนื่อหาของเว็บไซต์
	 *
	 * @var array
	 */
	public $contents = array();
}