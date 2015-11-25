<?php
/**
 * @filesource documet/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Index;

/**
 * Controller หลัก สำหรับแสดง frontend ของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Controller
{

	/**
	 * Controller หลักของโมดูล ใช้เพื่อตรวจสอบว่าจะเรียกหน้าไหนมาแสดงผล
	 *
	 * @param Object $module ข้อมูลโมดูลจาก database
	 * @return Object
	 */
	public function inint($module)
	{
		// ตรวจสอบโมดูลและอ่านข้อมูลโมดูล
		$index = \Document\Module\Model::get($module);
		if (empty($index)) {
			// 404
			$list = $this->page404();
		} elseif (!empty($_GET['document']) || isset($_GET['id'])) {
			// หน้าแสดงบทความ
			$list = $this->createClass('Document\View\View')->index($index);
		} elseif ($index->categories > 0 && empty($_GET['cat'])) {
			// หน้าหมวดหมู่
			$list = $this->createClass('Document\Categories\View')->index($index);
		} else {
			// หน้าแสดงรายการบทความ
			$list = $this->createClass('Document\Stories\View')->index($index);
		}
		return $list;
	}

	public function page404()
	{
		// 404
		$message = \Language::get('Sorry, can not find a page called Please check the URL or try the call again.');
		$result = array(
			'topic' => $message,
			'detail' => '<div class=error>'.$message.'</div>',
			'description' => $message,
			'keywords' => $message
		);
		return (object)$result;
	}

	public function url($module, $alias, $id)
	{
		if (\Kotchasan::$config->module_url == 1) {
			return \Url::create($module, $alias);
		} else {
			return \Url::create($module, '', 0, $id);
		}
	}
}