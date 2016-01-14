<?php
/**
 * @filesource Index/Models/World.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\World;

/**
 * คลาสสำหรับเชื่อมต่อกับฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Orm\Field
{
	/**
	 * table name
	 *
	 * @var string
	 */
	protected $table = 'world';
}