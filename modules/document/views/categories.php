<?php
/**
 * @filesource document/views/categories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\Categories;

/**
 * แสดงผลโมดูล document
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class View extends \View
{

	public static function index($index)
	{
		$categories = \Document\Categories\Model::get($index);
		// รายการ
		$listitem = \Template::create($index->owner, $index->module, 'categoryitem');
		foreach ($categories as $item) {
			if (!empty($item->icon) && is_file(ROOT_PATH.\Kotchasan::$data_folder.'document/'.$item->icon)) {
				$icon = WEB_URL.\Kotchasan::$data_folder.'document/'.$item->icon;
			} else {
				$icon = WEB_URL.$index->default_icon;
			}
			$listitem->add(array(
				'TOPIC' => $item->topic,
				'DETAIL' => $item->detail,
				'COUNT' => $item->c1,
				'COMMENTS' => $item->c2,
				'THUMB' => $icon,
				'URL' => \Url::create($index->module, '', $item->category_id)
			));
		}
		// template
		$template = \Template::create($index->owner, $index->module, 'category');
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
		if (!\Gcms::isHome($index->module)) {
			// ถ้าไม่ใช่หน้าหลักจะมี breadcrumb ของโมดูล
			$index->canonical = \Url::create($index->module);
			$menu = self::$menu->moduleMenu($index->module);
			self::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
		}
		return $result;
	}
}