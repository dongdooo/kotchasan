<?php
/**
 * @filesource board/views/stories.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\Stories;

/**
 * แสดงผลโมดูล board
 *
 * @author Goragod Wiriya <admin@goragod.com>
 * @since 1.0
 */
class View extends \View
{

	public function index($index)
	{
		$stories = \Board\Stories\Model::get($index);
		// วันที่สำหรับเครื่องหมาย new
		$valid_date = \Kotchasan::$mktime - $index->new_date;
		// รายการ
		$listitem = \Template::create($index->owner, $index->module, 'listitem');
		foreach ($stories as $item) {
			if ($item->pin > 0) {
				$thumb = WEB_URL.self::$template.'board/img/pin.png';
			} elseif (is_file(ROOT_PATH.\Kotchasan::$data_folder.'board/thumb-'.$item->picture)) {
				$thumb = WEB_URL.\Kotchasan::$data_folder.'board/thumb-'.$item->picture;
			} elseif (is_file(ROOT_PATH.\Kotchasan::$data_folder.'board/'.$item->picture)) {
				$thumb = WEB_URL.\Kotchasan::$data_folder.'board/'.$item->picture;
			} else {
				$thumb = WEB_URL.$index->default_icon;
			}
			$ctiime = empty($item->comment_date) ? $item->last_update : $item->comment_date;
			$listitem->add(array(
				'ID' => $item->id,
				'PICTURE' => $thumb,
				'URL' => $this->controller->url($index->module, $item->id),
				'TOPIC' => $item->topic,
				'UID' => (int)$item->member_id,
				'SENDER' => $item->sender,
				'STATUS' => $item->status,
				'DATE' => \Datetool::format($item->create_date),
				'DATEISO' => date(DATE_ISO8601, $item->create_date),
				'VISITED' => number_format($item->visited),
				'REPLY' => number_format($item->comments),
				'REPLYDATE' => empty($item->comment_date) ? '&nbsp;' : \Datetool::format($item->comment_date),
				'REPLYER' => empty($item->comment_date) ? '&nbsp;' : $item->commentator,
				'RID' => (int)$item->commentator_id,
				'STATUS2' => $item->replyer_status,
				'ICON' => $ctiime >= $valid_date ? ($item->comment_date > 0 ? ' update' : ' new') : ''
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
		// breadcrumb
		$index->canonical = \Url::create($index->module);
		$menu = self::$menu->moduleMenu($index->module);
		self::$view->addBreadcrumb($index->canonical, $menu->menu_text, $menu->menu_tooltip);
		if (!empty($index->category_id)) {
			$index->canonical = \Url::create($index->module, '', $index->category_id);
			self::$view->addBreadcrumb($index->canonical, $index->topic, $index->topic);
		}
		return $result;
	}
}