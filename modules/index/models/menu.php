<?php
/**
 * index/models/menu.php
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @link http://www.kotchasan.com/
 *
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Menu;

/**
 * คลาสสำหรับโหลดรายการเมนูจากฐานข้อมูลของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Model
{
	/**
	 * รายการเมนูตามประเภทของเมนู
	 *
	 * @var array
	 */
	private $menus;

	/**
	 * อ่านรายการเมนูทั้งหมดที่ติดตั้งแล้ว
	 *
	 * @return array
	 */
	public function getMenus()
	{
		// โหลดเมนูทั้งหมดเรียงตามลำดับเมนู (รายการแรกคือหน้า Home)
		$sql = "SELECT M.`id` AS `module_id`,M.`module`,M.`owner`,M.`config`";
		$sql .= ",U.`index_id`,U.`parent`,U.`level`,U.`menu_text`,U.`menu_tooltip`,U.`accesskey`,U.`menu_url`,U.`menu_target`,U.`alias`,U.`published`";
		$sql .= ",(CASE U.`parent` WHEN 'MAINMENU' THEN 0 WHEN 'BOTTOMMENU' THEN 1 WHEN 'SIDEMENU' THEN 2 ELSE 3 END ) AS `pos`";
		$sql .= " FROM `".$this->tableWithPrefix('menus')."` AS U";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('index')."` AS I ON I.`id`=U.`index_id` AND I.`index`='1' AND I.`language` IN (:language,'')";
		$sql .= " LEFT JOIN `".$this->tableWithPrefix('modules')."` AS M ON M.`id`=I.`module_id`";
		$sql .= " WHERE U.`language` IN (:language,'')";
		$sql .= " ORDER BY `pos` ASC,U.`parent` ASC ,U.`menu_order` ASC";
		$where = array(':language' => \Language::name());
		// จัดลำดับเมนูตามระดับของเมนู
		$datas = array();
		$this->cache->cacheOn();
		$result = $this->db->customQuery($sql, false, $where, $this->cache);
		foreach ($result AS $i => $item) {
			if (!empty($item->config)) {
				$config = @unserialize($item->config);
				if (!is_array($config)) {
					$save = array();
					foreach (explode("\n", $item->config) as $value) {
						if (preg_match('/^(.*)=(.*)$/', $value, $match)) {
							$save[$match[1]] = trim($match[2]);
						}
					}
					$item->config = serialize($save);
					$this->db->update($this->tableWithPrefix('modules'), array('id', $item->module_id), array('config' => $item->config));
				}
				foreach (unserialize($item->config) as $key => $value) {
					$item->$key = $value;
				}
			}
			unset($item->config);
			$level = $item->level;
			if ($level == 0) {
				$datas[$item->parent]['toplevel'][$i] = $item;
			} else {
				$datas[$item->parent][$toplevel[$level - 1]][$i] = $item;
			}
			$toplevel[$level] = $i;
		}
		$this->menus = (object)$datas;
		// คืนค่ารายการทั้งหมด
		return $result;
	}

	/**
	 * อ่านเมนูรายการแรกสุด (หน้าหลัก)
	 *
	 * @return array รายการเมนู แรก ถ้าไม่พบคืนค่าแอเรย์ว่าง
	 */
	public function homeMenu()
	{
		if (isset($this->menus->MAINMENU['toplevel'][0])) {
			$menu = $this->menus->MAINMENU['toplevel'][0];
		} else {
			$menu = false;
		}
		return $menu;
	}

	/**
	 * อ่านเมนู (MAINMENU) ของโมดูล
	 *
	 * @param string $module ชื่อโมดูลที่ต้องการ
	 *
	 * @return array รายการเมนูของเมนูที่เลือก ถ้าไม่พบคืนค่าแอเรย์ว่าง
	 */
	public function moduleMenu($module)
	{
		$result = array();
		if (isset($this->menus->MAINMENU['toplevel'])) {
			foreach ($this->menus->MAINMENU['toplevel'] as $item) {
				if ($item->module == $module) {
					$result = $item;
					break;
				}
			}
		}
		return $result;
	}

	/**
	 * สร้างเมนูตามตำแหน่งของเมนู (parent)
	 *
	 * @return array รายการเมนูทั้งหมด
	 */
	public function render()
	{
		$view = new \Index\Menu\View(null);
		$result = array();
		foreach ($this->menus AS $parent => $items) {
			if ($parent != '') {
				$result[$parent] = $view->render($items);
			}
		}
		return $result;
	}
}