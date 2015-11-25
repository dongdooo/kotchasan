<?php
/**
 * @filesource index/views/menu.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;
/*
 * default View
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */

class View extends \View
{

	/**
	 * ฟังก์ชั่นสร้างเมนู
	 *
	 * @param array $module หน้าที่เรียก มาจาก Controller
	 *
	 * @return string
	 */
	public function render($module)
	{
		// รายการเมนู
		$menus['home'] = 'index.php';
		$menus['about'] = 'index.php?module=about';
		// สร้างเมนู
		$menu = '';
		foreach ($menus as $key => $value) {
			$c = $module == $key ? ' class=select' : '';
			$menu .= '<li'.$c.'><a href="'.$value.'"><span>'.ucfirst($key).'</span></a></li>';
		}
		return $menu;
	}
}