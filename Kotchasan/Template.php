<?php
/*
 * @filesource Kotchasan/Template.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

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
	 * ชื่อ template ที่กำลังใช้งานอยู่ รวมโฟลเดอร์ที่เก็บ template ด้วย
	 * เช่น skin/default/
	 *
	 * @var string
	 */
	public static $src;

	/**
	 * เรียกใช้งาน template ในครั้งแรก
	 *
	 * @param string $skin
	 */
	public static function inint($skin)
	{
		self::$src = 'skin/'.($skin == '' ? '' : $skin.'/' );
	}

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
		$obj = new static;
		$obj->skin = $obj->load($owner, $module, $name);
		$obj->items = array();
		$obj->cols = (int)$cols;
		$obj->num = $obj->cols;
		return $obj;
	}

	/**
	 * โหลด template จากไฟล์
	 *
	 * @param string $filename
	 * @param int $cols 0 (default) แสดงผลแบบปกติ มากกว่า 0 แสดงผลด้วยกริด
	 * @throws \InvalidArgumentException ถ้าไม่พบไฟล์
	 */
	public static function createFromFile($filename, $cols = 0)
	{
		if (is_file($filename)) {
			$obj = new static;
			$obj->skin = file_get_contents($filename);
			$obj->items = array();
			$obj->cols = (int)$cols;
			$obj->num = $obj->cols;
			return $obj;
		} else {
			throw new \InvalidArgumentException(Language::get('Template file not found'));
		}
	}

	/**
	 * ฟังก์ชั่นกำหนดค่าตัวแปรของ template
	 * ฟังก์ชั่นนี้จะแทนที่ตัวแปรที่ส่งทั้งหมดลงใน template ทันที
	 *
	 * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1=>val1,key2=>val2)
	 */
	public function add($array)
	{
		$datas = array();
		foreach ($array as $key => $value) {
			$datas[$key] = $value;
		}
		if (!empty($this->cols) && $this->num == 0) {
			$this->items[] = "</div>\n<div class=row>";
			$this->num = $this->cols;
		}
		$this->items[] = self::pregReplace(array_keys($datas), array_values($datas), $this->skin);
		$this->num--;
	}

	/**
	 * ฟังก์ชั่น preg_replace
	 *
	 * @param array $patt คีย์ใน template
	 * @param array $replace ข้อความที่จะถูกแทนที่ลงในคีย์
	 * @param string $skin template
	 * @return string คืนค่า HTML template
	 * @assert ('/{TITLE}/', 'Title', '<b>{TITLE}</b>') [==] '<b>Title</b>'
	 * @assert ('/{LNG_([\w\s\.\-\'\(\),%\/:&\#;]+)}/e', '\Kotchasan\Language::get(array(1=>"$1"))', '<b>{LNG_Language test}</b>') [==] '<b>Language test</b>'
	 */
	public static function pregReplace($patt, $replace, $skin)
	{
		if (!is_array($patt)) {
			$patt = array($patt);
		}
		if (!is_array($replace)) {
			$replace = array($replace);
		}
		foreach ($patt AS $i => $item) {
			if (preg_match('/(.*\/(.*?))[e](.*?)$/', $item, $patt) && preg_match('/^([\\\\a-z0-9]+)::([a-z0-9_\\\\]+).*/i', $replace[$i], $func)) {
				$skin = preg_replace_callback($patt[1].$patt[3], array($func[1], $func[2]), $skin);
			} else {
				$skin = preg_replace($item, $replace[$i], $skin);
			}
		}
		return $skin;
	}

	/**
	 * แสดงผล เป็น HTML.
	 */
	public function render()
	{
		if (empty($this->items)) {
			return $this->skin;
		} elseif (empty($this->cols)) {
			return implode("\n", $this->items);
		}
		return "<div class=row>\n".implode("\n", $this->items)."\n</div>";
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
		$src = self::getPath();
		if ($module != '' && is_file($src.$module.'/'.$name.'.html')) {
			$result = file_get_contents($src.$module.'/'.$name.'.html');
		} elseif ($owner != '' && is_file($src.$owner.'/'.$name.'.html')) {
			$result = file_get_contents($src.$owner.'/'.$name.'.html');
		} elseif (is_file($src.$name.'.html')) {
			$result = file_get_contents($src.$name.'.html');
		} else {
			$result = '';
		}
		return $result;
	}

	/**
	 * คืนค่าโฟลเดอร์ของ template
	 *
	 * @return string
	 */
	public static function getPath()
	{
		return TEMPLATE_ROOT.self::$src;
	}
}