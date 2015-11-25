<?php
/**
 * @filesource index/models/world.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
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
class Model extends \Core\Orm\Field
{
	/**
	 * table name
	 *
	 * @var string
	 */
	protected $table = 'world';
}