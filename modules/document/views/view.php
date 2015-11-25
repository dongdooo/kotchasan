<?php
/**
 * @filesource document/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Document\View;

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
		// อ่านรายการที่เลือก
		$story = \Document\View\Model::get($index);
		// ข้อความค้นหา
		$search = preg_replace('/[+\s]+/u', ' ', \Input::get($_GET, 'q'));
		// login
		$login = \Input::get($_SESSION, 'login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''));
		// แสดงความคิดเห็นได้
		$canReply = $story->can_reply == 1;
		// ผู้ดูแล,เจ้าของเรื่อง (ลบ-แก้ไข บทความ,ความคิดเห็นได้)
		$moderator = \Input::canConfig($login, $index, 'moderator');
		$moderator = $moderator || $story->member_id == $login['id'];
		// สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
		$canview = \Input::canConfig($login, $index, 'can_view');
		// template
		$template = \Template::create($index->owner, $index->module, 'view');
		$result = new \stdClass();
		$result->canonical = $this->controller->url($index->module, $story->alias, $story->id);
		if ($story->id == 0) {
			// 404
			$result->topic = \Language::get('Sorry, can not find a page called Please check the URL or try the call again.');
			$result->detail = '<div class=error>'.$result->topic.'</div>';
		} else {
			$comments = array();
			if ($canReply) {
				// antispam
				$register_antispamchar = \String::rndname(32);
				$_SESSION[$register_antispamchar] = \String::rndname(4);
			}
			$replace = array(
				'COMMENTLIST' => implode("\n", $comments),
				'REPLYFORM' => \Template::load('document', $index->module, 'reply'),
				'TOPIC' => $story->topic,
				'DETAIL' => \Gcms::HighlightSearch(\Gcms::showDetail($story->detail, $canview), $search),
				'DATE' => \Datetool::format($story->create_date),
				'DATEISO' => date(DATE_ISO8601, $story->create_date),
				'COMMENTS' => number_format($story->comments),
				'VISITED' => number_format($story->visited),
				'DISPLAYNAME' => empty($story->displayname) ? $story->email : $story->displayname,
				'UID' => (int)$story->member_id,
				'LOGIN_PASSWORD' => $login['password'],
				'LOGIN_EMAIL' => $login['email'],
				'QID' => $story->id,
				'MODULE' => $index->module,
				'MODULEID' => $story->module_id,
				'TAGS' => $story->relate,
				'ANTISPAM' => isset($register_antispamchar) ? $register_antispamchar : '',
				'ANTISPAMVAL' => isset($register_antispamchar) && \Login::isAdmin() ? $_SESSION[$register_antispamchar] : ''
			);
			// แสดงบทความ
			$template->add($replace);
			// แทนที่ลงใน template
			$result->detail = $template->render();
			$result->topic = $story->topic;
			$result->description = $story->description;
			$result->keywords = $story->keywords;
			// breadcrumb
			$menu = $this->controller->menu()->moduleMenu($index->module);
			$view = $this->controller->view();
			$view->addBreadcrumb(\Url::create($index->module), $menu->menu_text, $menu->menu_tooltip);
			$view->addBreadcrumb($result->canonical, $result->topic, $result->description);
		}
		return $result;
	}
}