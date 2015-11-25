<?php
/**
 * @filesource index/models/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Page;

use \Core\Orm\Model as OrmModel;

/**
 * โมเดลสำหรับแสดงรายการหน้าเว็บไซต์ที่สร้างแล้ว (pages.php)
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class Model extends OrmModel
{
	protected $table = 'modules M';

	public function getConfig()
	{
		return array(
			'select' => array(
				'I.id',
				'M.module',
				'D.topic',
				'D.description',
				'D.keywords',
				'D.detail',
				'I.visited'
			),
			'join' => array(
				array(
					'INNER',
					'Index\Pageindex\Model',
					array(
						array('I.module_id', 'M.id')
					)
				),
				array(
					'INNER',
					'Index\Pagedetail\Model',
					array(
						array('D.id', 'I.id'),
						array('D.module_id', 'I.module_id'),
						array('D.language', 'I.language')
					)
				)
			)
		);
	}
}