<?php
/**
 * @filesource board/views/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Board\View;

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
		$story = \Board\View\Model::get($index);
		// login
		$login = \Input::get($_SESSION, 'login', array('id' => 0, 'status' => -1, 'email' => '', 'password' => ''));
		// true = login, else false
		$isMember = !empty($login['id']);
		// สถานะสมาชิกที่สามารถเปิดดูกระทู้ได้
		$canView = \Input::canConfig($login, $index, 'can_view');
		if ($canView || $index->viewing == 1) {
			// แสดงความคิดเห็นได้
			$canReply = \Input::canConfig($login, $story, 'can_reply');
			// ผู้ดูแล
			$moderator = \Input::canConfig($login, $index, 'moderator');
			// สามารถลบได้ (mod=ลบ,สมาชิก=แจ้งลบ)
			$canDelete = $moderator || ($isMember && defined('DB_PM'));
			// แก้ไขบอร์ด (mod หรือ ตัวเอง)
			$canEdit = $moderator || ($isMember && $login['id'] == $story->member_id);
			// template
			$template = \Template::create($index->owner, $index->module, 'view');
			$result = new \stdClass();
			$result->canonical = $this->controller->url($index->module, '', 0, 0, 'wbid='.$story->id);
			if ($story->id == 0) {
				// 404
				$result->topic = \Language::get('Sorry, can not find a page called Please check the URL or try the call again.');
				$result->detail = '<div class=error>'.$result->topic.'</div>';
			} else {
				// dir ของรูปภาพอัปโหลด
				$imagedir = ROOT_PATH.\Kotchasan::$data_folder.'board/';
				$imageurl = WEB_URL.\Kotchasan::$data_folder.'board/';
				// ความคิดเห็น
				$comments = array();
				// antispam
				$register_antispamchar = \String::rndname(32);
				$_SESSION[$register_antispamchar] = \String::rndname(4);
				// รายละเอียดเนื้อหา
				$detail = \Gcms::showDetail($story->detail, $canView, true, true);
				// รูปภาพของกระทู้
				if (!empty($story->picture) && is_file($imagedir.$story->picture)) {
					$result->image_src = $imageurl.$story->picture;
					$detail = '<figure class="center"><img src="'.$result->image_src.'" alt="'.$story->topic.'"></figure>'.$detail;
				}
				$search = '';
				$replace = array(
					'/{COMMENTLIST}/' => implode("\n", $comments),
					'/(edit-{QID}-0-0-{MODULE})/' => $canEdit ? '\\1' : 'hidden',
					'/(delete-{QID}-0-0-{MODULE})/' => $canDelete ? '\\1' : 'hidden',
					'/(quote-{QID}-([0-9]+)-([0-9]+)-{MODULE})/' => !$canReply || $story->locked == 1 ? 'hidden' : '\\1',
					'/(pin-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
					'/(lock-{QID}-0-0-{MODULE})/' => $moderator ? '\\1' : 'hidden',
					'/{URL}/' => $result->canonical,
					'/{TOPIC}/' => $story->topic,
					'/{PIN}/' => empty($index->pin) ? 'un' : '',
					'/{LOCK}/' => empty($index->locked) ? 'un' : '',
					'/{PIN_TITLE}/' => \Language::get(empty($index->pin) ? 'Pin' : 'Unpin'),
					'/{LOCK_TITLE}/' => \Language::get(empty($index->locked) ? 'Lock' : 'Unlock'),
					'/{DETAIL}/' => \Gcms::highlightSearch($detail, $search),
					'/{UID}/' => (int)$story->member_id,
					'/{DISPLAYNAME}/' => $story->displayname,
					'/{STATUS}/' => $story->status,
					'/{DATE}/' => \Datetool::format($story->create_date),
					'/{DATEISO}/' => date(DATE_ISO8601, $story->create_date),
					'/{COMMENTS}/' => number_format($story->comments),
					'/{VISITED}/' => number_format($story->visited),
					'/{REPLYFORM}/' => !$canReply || $story->locked == 1 ? '' : \Template::load($index->owner, $index->module, 'reply'),
					'/<MEMBER>(.*)<\/MEMBER>/s' => $isMember ? '' : '$1',
					'/<UPLOAD>(.*)<\/UPLOAD>/s' => empty($index->img_upload_type) ? '' : '$1',
					'/{LOGIN_PASSWORD}/' => $login['password'],
					'/{LOGIN_EMAIL}/' => $login['email'],
					'/{ANTISPAM}/' => isset($register_antispamchar) ? $register_antispamchar : '',
					'/{ANTISPAMVAL}/' => isset($register_antispamchar) && \Login::isAdmin() ? $_SESSION[$register_antispamchar] : '',
					'/{QID}/' => $story->id,
					'/{DELETE}/' => \Language::get($moderator ? 'Delete' : 'Request to Remove'),
					'/{MODULE}/' => $index->module,
					'/{MODULEID}/' => $story->module_id
				);
			}
		} else {
			// not login
			$replace = array(
			);
		}
		// แสดงบทความ
		$template->add($replace, FORMAT_PCRE);
		// แทนที่ลงใน template
		$result->detail = $template->render();
		$result->topic = $story->topic;
		$result->description = $story->topic;
		$result->keywords = $story->topic;
		// breadcrumb
		$menu = self::$menu->moduleMenu($index->module);
		self::$view->addBreadcrumb(\Url::create($index->module), $menu->menu_text, $menu->menu_tooltip);
		self::$view->addBreadcrumb($result->canonical, $result->topic, $result->description);
		return $result;
	}
}