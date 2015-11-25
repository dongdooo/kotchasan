<?php
/**
 * @filesource index/views/datatable.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Datatable;

/**
 * คลาสสำหรับการสร้างหน้าเพจของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \View
{

	public function index()
	{
		$table = new \Datatable(array(
			'id' => 'datatable',
			'model' => 'Index\Page\Model',
			'class' => 'data',
			'border' => true,
			'cache' => true,
			'hideColumns' => array('detail'),
			'perPage' => 10,
			'headers' => array(
				'id' => array(
					'text' => 'ID',
					'align' => 'center',
					'sort' => 'id'
				),
				'module' => array(
					'sort' => 'module'
				)
			),
			'cols' => array(
				'id' => array(
					'align' => 'center'
				),
				'visited' => array(
					'align' => 'center'
				)
			)
		));
		return $table->render();
	}

	public function title()
	{
		return 'ตัวอย่างการใช้งาน Datatable';
	}
}