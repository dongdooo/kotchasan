<?php
/**
 * @filesource core/template.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Template engine
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Template
{
	/**
	 * ข้อมูล template
	 *
	 * @var string
	 */
	private $skin;
	/**
	 * แอเรย์ของข้อมูล
	 *
	 * @var array
	 */
	private $items;
	/**
	 * จำนวนคอลัมน์ สำหรับการแสดงผลด้วย Grid
	 *
	 * @var int
	 */
	private $cols;
	/**
	 * ตัวแปรสำหรับการขึ้นแถวใหม่ (Grid)
	 *
	 * @var int
	 */
	private $num;

	/**
	 * โหลด template
	 * ครั้งแรกจะตรวจสอบไฟล์จาก module ถ้าไม่พบ จะใช้ไฟล์จาก owner
	 *
	 * @param string $owner ชื่อโมดูลที่ติดตั้ง
	 * @param string $module ชื่อโมดูล
	 * @param string $name ชื่อ template ไม่ต้องระบุนามสกุลของไฟล์
	 * @param int $cols 0 (default) แสดงผลแบบปกติ มากกว่า 0 แสดงผลด้วยกริด
	 * @return \static
	 */
	public static function create($owner, $module, $name, $cols = 0)
	{
		$obj = new static();
		$obj->skin = $obj->load($owner, $module, $name);
		$obj->items = array();
		$obj->cols = (int)$cols;
		$obj->num = $obj->cols;
		return $obj;
	}

	/**
	 * ฟังก์ชั่นกำหนดค่าตัวแปรของ template
	 * ฟังก์ชั่นนี้จะแทนที่ตัวแปรที่ส่งทั้งหมดลงใน template ทันที
	 *
	 * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1=>val1,key2=>val2)
	 * @param int $option FORMAT_TEXT = คีย์แบบข้อความ, FORMAT_PCRE = คีย์แบบ PCRE
	 */
	public function add($array, $option = FORMAT_TEXT)
	{
		$datas = array();
		foreach ($array as $key => $value) {
			if ($option === FORMAT_TEXT) {
				$datas['/{'.$key.'}/'] = $value;
			} else {
				$datas[$key] = $value;
			}
		}
		if (!empty($this->cols) && $this->num == 0) {
			$this->items[] = "</div>\n<div class=row>";
			$this->num = $this->cols;
		}
		$this->items[] = \String::pregReplace(array_keys($datas), array_values($datas), $this->skin);
		$this->num--;
	}

	/**
	 * แสดงผล เป็น HTML.
	 */
	public function render()
	{
		if (empty($this->cols)) {
			return isset($this->items) ? implode("\n", $this->items) : $this->skin;
		} else {
			return isset($this->items) ? "<div class=row>\n".implode("\n", $this->items)."\n</div>" : $this->skin;
		}
	}

	/**
	 * โหลด template
	 * ครั้งแรกจะตรวจสอบไฟล์จาก $module ถ้าไม่พบ จะใช้ไฟล์จาก $owner
	 *
	 * @param string $owner ชื่อโมดูลที่ติดตั้ง
	 * @param string $module ชื่อโมดูลที่ลงทะเบียน
	 * @param string $name ชื่อ template ไม่ต้องระบุนามสกุลของไฟล์
	 * @return string ถ้าไม่พบคืนค่าว่าง
	 */
	public static function load($owner, $module, $name)
	{
		$template = \Kotchasan::$template_root.\Kotchasan::$template;
		if ($module != '' && is_file($template.$module.'/'.$name.'.html')) {
			$result = file_get_contents($template.$module.'/'.$name.'.html');
		} elseif ($owner != '' && is_file($template.$owner.'/'.$name.'.html')) {
			$result = file_get_contents($template.$owner.'/'.$name.'.html');
		} elseif (is_file($template.$name.'.html')) {
			$result = file_get_contents($template.$name.'.html');
		} else {
			$result = '';
		}
		return $result;
	}
}