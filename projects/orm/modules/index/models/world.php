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
	 * ชื่อตาราง
	 *
	 * @var string
	 */
	protected $table = 'world W';
	
	/**
	  CREATE TABLE IF NOT EXISTS `world` (
	  `id` int(11) NOT NULL,
	  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `created_at` datetime NOT NULL,
	  `user_id` int(11) NOT NULL,
	  `randomNumber` int(11) NOT NULL
	  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
	 */
}