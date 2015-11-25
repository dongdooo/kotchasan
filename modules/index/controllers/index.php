<?php
/**
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

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
	 * แสดงผล
	 */
	public function index()
	{
		// session cookie
		\Kotchasan::inintSession();
		// ตรวจสอบการ login
		\Login::create();
		// front end
		\Gcms::$view = $this->createView('Index\Index\View');
		// รายการเมนูทั้งหมด
		\Gcms::$menu = new \Index\Menu\Model();
		// โหลดเมนู
		foreach (\Gcms::$menu->getMenus() as $item) {
			$module = $item->module;
			if (!empty($module) && !isset(\Gcms::$install_modules[$module])) {
				\Gcms::$install_modules[$module] = $item;
				\Gcms::$install_owners[$item->owner][] = $module;
			}
		}
		// โหลดโมดูลทั้งหมด
		foreach (\Index\Module\Model::getModules() AS $item) {
			$module = $item->module;
			if (!isset(\Gcms::$install_modules[$module])) {
				\Gcms::$install_modules[$module] = $item;
				\Gcms::$install_owners[$item->owner][] = $module;
			}
		}
		// รายชื่อโมดูลทั้งหมด
		$module_list = array_keys(\Gcms::$install_modules);
		// หน้า home มาจากเมนูรายการแรก
		$home = \Gcms::$menu->homeMenu();
		if ($home) {
			$home->canonical = WEB_URL.'index.php';
			// breadcrumb หน้า home
			\Gcms::$view->addBreadcrumb($home->canonical, $home->menu_text, $home->menu_tooltip, 'icon-home');
		}
		// ตรวจสอบหน้าที่เรียก
		if (empty($_GET['module'])) {
			if (!empty($home->menu_url)) {
				$url = \Url::create($home->menu_url);
				foreach ($url->get('query') AS $k => $v) {
					$_GET[$k] = $v;
				}
				if (empty($_GET['module'])) {
					$_GET = $this->route->parseRoutes($url->get('path'), $_GET);
				}
			}
			if (empty($_GET['module']) && !empty($home->module)) {
				// ถ้าไม่มีโมดูล เลือกเมนูรายการแรก
				$_GET['module'] = $home->module;
			}
		}
		// ถ้าไม่มีโมดูล เลือกโมดูลแรกสุด
		if (empty($_GET['module']) && !empty($module_list)) {
			$_GET['module'] = $module_list[0];
		}
		if (isset($module_list) && in_array($_GET['module'], $module_list)) {
			// โมดูลที่เลือก
			$module = \Gcms::$install_modules[$_GET['module']];
			// โหลดโมดูลที่เลือก
			if ($module->owner == 'index') {
				// เรียกจากโมดูล index
				$page = \Index\Index\Model::getIndex($module->module_id);
				// canonical
				$page->canonical = \Url::create($module->module);
			} else {
				// เรียกจากโมดูลที่ติดตั้ง
				$page = $this->createClass(ucwords($module->owner).'\Index\Controller')->inint($module);
			}
		} elseif (isset($module_list) && $_GET['module'] == 'index' && isset($_GET['id'])) {
			// ไม่มีโมดูลถูกเลือก
			$module = null;
			// เรียกจากโมดูล index
			$page = \Index\Index\Model::getIndexById($_GET['id']);
			// canonical
			$page->canonical = \Url::create($page->module);
		}
		// หน้า home
		if (empty($module) || ($home && ($home->module_id == $module->module_id))) {
			$page->canonical = $home->canonical;
		}
		// เมนู
		\Gcms::$view->add(\Gcms::$menu->render());
		if (empty($page)) {
			// ไม่พบหน้าที่เรียก
			$page_not_found = \Language::get('Page not found!');
			$page = (object)array(
				'topic' => $page_not_found,
				'detail' => '<div class=error>'.$page_not_found.'</div>',
				'description' => \Kotchasan::$config->web_description,
				'keywords' => \Kotchasan::$config->web_title
			);
		}
		// meta tag
		$heads = array();
		$heads[] = '<meta property="og:title" content="'.$page->topic.'">';
		$heads[] = '<meta name=description content="'.$page->description.'">';
		$heads[] = '<meta name=keywords content="'.$page->keywords.'">';
		$heads[] = '<meta property="og:site_name" content="'.strip_tags(\Kotchasan::$config->web_title).'">';
		$heads[] = '<meta property="og:type" content="article">';
		if (isset($page->canonical)) {
			$heads[] = '<meta name=canonical content="'.$page->canonical.'">';
			$heads[] = '<meta property="og:url" content="'.$page->canonical.'">';
		}
		\Gcms::$view->addHead($heads);
		// ภาษาที่ติดตั้ง
		$languages = \Template::create('', '', 'language');
		foreach (\Kotchasan::$config->languages as $lng) {
			$languages->add(array(
				'LNG' => $lng
			));
		}
		// เนื้อหา
		\Gcms::$view->add(array(
			// content
			'CONTENT' => \Gcms::showDetail($page->detail, true, false),
			// กรอบ login
			'LOGIN' => 'Login',
			// title
			'TITLE' => $page->topic,
			// ขนาดตัวอักษร
			'FONTSIZE' => '<a class="font_size small" title="{LNG_change font small}">A<sup>-</sup></a><a class="font_size normal" title="{LNG_change font normal}">A</a><a class="font_size large" title="{LNG_change font large}">A<sup>+</sup></a>',
			// script
			'SCRIPT' => '',
			// ภาษาที่ติดตั้ง
			'LANGUAGES' => $languages->render()
		));
		// output เป็น HTML
		\Gcms::$view->renderHTML();
	}
}