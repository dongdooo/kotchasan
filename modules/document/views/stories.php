<?php
/**
 * @filesource document/views/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Stories;

/**
 * แสดงผลโมดูล document
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class View extends \View
{

	public function index($index)
	{
		$stories = \Document\Stories\Model::get($index);
		// วันที่สำหรับเครื่องหมาย new
		$valid_date = \Kotchasan::$mktime - $index->new_date;
		// รายการ
		$listitem = \Template::create($index->owner, $index->module, 'listitem');
		foreach ($stories as $item) {
			if (!empty($item->picture) && is_file(ROOT_PATH.\Kotchasan::$data_folder.'document/'.$item->picture)) {
				$thumb = WEB_URL.\Kotchasan::$data_folder.'document/'.$item->picture;
			} elseif (!empty($index->icon) && is_file(ROOT_PATH.\Kotchasan::$data_folder.'document/'.$index->icon)) {
				$thumb = WEB_URL.\Kotchasan::$data_folder.'document/'.$index->icon;
			} else {
				$thumb = WEB_URL.$index->default_icon;
			}
			if ((int)$item->create_date > $valid_date && empty($item->comment_date)) {
				$icon = ' new';
			} elseif ((int)$item->last_update > $valid_date || (int)$item->comment_date > $valid_date) {
				$icon = ' update';
			} else {
				$icon = '';
			}
			$listitem->add(array(
				'URL' => $this->controller->url($index->module, $item->alias, $item->id),
				'TOPIC' => $item->topic,
				'DATE' => \Datetool::format($item->create_date),
				'COMMENTS' => number_format($item->comments),
				'VISITED' => number_format($item->visited),
				'DETAIL' => $item->description,
				'THUMB' => $thumb,
				'ICON' => $icon
			));
		}
		// template
		$template = \Template::create($index->owner, $index->module, 'list');
		$template->add(array(
			'TOPIC' => $index->topic,
			'DETAIL' => $index->detail,
			'LIST' => $listitem->render(),
			'SPLITPAGE' => '',
			'MODULE' => $index->module
		));
		// แทนที่ลงใน template
		$result = new \stdClass();
		$result->detail = $template->render();
		$result->topic = $index->topic;
		$result->description = $index->description;
		$result->keywords = $index->keywords;
		$result->canonical = \Url::create($index->module);
		/*
		  // breadcrumb
		  $index->canonical = \Url::create($index->module);
		  $menu = self::$menu->moduleMenu($index->module);
		  self::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
		  if (!empty($index->category_id)) {
		  $index->canonical = \Url::create($index->module, '', $index->category_id);
		  self::$view->addBreadcrumb($index->canonical, $index->topic, $index->topic);
		  }
		 *
		 */
		return $result;
	}
}