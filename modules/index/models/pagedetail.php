<?php
/**
 * @filesource index/models/pagedetail.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Pagedetail;

use \Core\Orm\Model as OrmModel;

/**
 * โมเดลสำหรับแสดงรายการหน้าเว็บไซต์ที่สร้างแล้ว (pages.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class Model extends OrmModel
{
	protected $table = 'index_detail D';
}