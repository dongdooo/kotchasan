<?php
/*
 * @filesource Kotchasan/Language.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\ArrayTool;
use \Kotchasan\File;

/**
 * Class สำหรับการโหลด config
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
final class Language extends \Kotchasan\KBase
{
	/**
	 * ภาษาทั้งหมดที่ติดตั้ง
	 *
	 * @var array
	 */
	private static $installed_languages;
	/**
	 * ชื่อภาษาที่กำลังใช้งานอยู่
	 *
	 * @var string
	 */
	private static $language_name;
	/**
	 * รายการภาษา
	 *
	 * @var array
	 */
	private static $languages = null;

	/**
	 * โหลดภาษา
	 */
	private function __construct()
	{
		// โฟลเดอร์ ภาษา
		$language_folder = self::languageFolder();
		// ภาษาที่เลือก
		$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_COOKIE['my_lang']) ? $_COOKIE['my_lang'] : '');
		// ตรวจสอบภาษา ใช้ภาษาแรกที่เจอ
		foreach (ArrayTool::replace(array($lang => $lang), self::$cfg->languages) as $item) {
			if (!empty($item)) {
				if (is_file($language_folder.$item.'.php')) {
					$language = include $language_folder.$item.'.php';
					if (isset($language)) {
						self::$languages = (object)$language;
						self::$language_name = $item;
						// บันทึกภาษาที่กำลังใช้งานอยู่ลงใน cookie
						setcookie('my_lang', $item, time() + 2592000, '/');
						break;
					}
				}
			}
		}
		if (null === self::$languages) {
			// default language
			self::$language_name = 'th';
			self::$languages = (object)array(
				'DATE_FORMAT' => 'd M Y เวลา H:i น.',
				'DATE_LONG' => array(
					0 => 'อาทิตย์',
					1 => 'จันทร์',
					2 => 'อังคาร',
					3 => 'พุธ',
					4 => 'พฤหัสบดี',
					5 => 'ศุกร์',
					6 => 'เสาร์'
				),
				'DATE_SHORT' => array(
					0 => 'อา.',
					1 => 'จ.',
					2 => 'อ.',
					3 => 'พ.',
					4 => 'พฤ.',
					5 => 'ศ.',
					6 => 'ส.'
				),
				'YEAR_OFFSET' => 543,
				'MONTH_LONG' => array(
					1 => 'มกราคม',
					2 => 'กุมภาพันธ์',
					3 => 'มีนาคม',
					4 => 'เมษายน',
					5 => 'พฤษภาคม',
					6 => 'มิถุนายน',
					7 => 'กรกฎาคม',
					8 => 'สิงหาคม',
					9 => 'กันยายน',
					10 => 'ตุลาคม',
					11 => 'พฤศจิกายน',
					12 => 'ธันวาคม'
				),
				'MONTH_SHORT' => array(
					1 => 'ม.ค.',
					2 => 'ก.พ.',
					3 => 'มี.ค.',
					4 => 'เม.ย.',
					5 => 'พ.ค.',
					6 => 'มิ.ย.',
					7 => 'ก.ค.',
					8 => 'ส.ค.',
					9 => 'ก.ย.',
					10 => 'ต.ค.',
					11 => 'พ.ย.',
					12 => 'ธ.ค.'
				)
			);
		}
	}

	/**
	 * ฟังก์ชั่นอ่านชื่อโฟลเดอร์เก็บไฟล์ภาษา
	 *
	 * @return string
	 */
	public static function languageFolder()
	{
		return ROOT_PATH.'language/';
	}

	/**
	 * รายชื่อภาษาที่ติดตั้ง
	 *
	 * @return array
	 */
	public static function installedLanguage()
	{
		if (!isset(self::$installed_languages)) {
			$language_folder = self::languageFolder();
			$files = array();
			File::listFiles($language_folder, $files);
			foreach ($files as $file) {
				if (preg_match('/(.*\/([a-z]{2,2}))\.(php|js)/', $file, $match)) {
					self::$installed_languages[$match[2]] = $match[2];
				}
			}
		}
		return self::$installed_languages;
	}

	/**
	 * โหลดไฟล์ภาษาทั้งหมดที่ติดตั้ง
	 * คืนค่าข้อมูลภาษาทั้งหมด
	 *
	 * @param string $type
	 * @return array
	 */
	public static function installed($type)
	{
		$language_folder = self::languageFolder();
		$datas = array();
		foreach (self::installedLanguage() as $lng) {
			if ($type == 'php') {
				if (is_file($language_folder.$lng.'.php')) {
					// php
					$datas[$lng] = include($language_folder.$lng.'.php');
				}
			} elseif (is_file($language_folder.$lng.'.js')) {
				// js
				$list = file($language_folder.$lng.'.js');
				foreach ($list as $item) {
					if (preg_match('/var\s+(.*)\s+=\s+[\'"](.*)[\'"];/', $item, $values)) {
						$datas[$lng][$values[1]] = $values[2];
					}
				}
			}
		}
		// จัดกลุ่มภาษาตาม key
		$languages = array();
		foreach ($datas as $language => $values) {
			foreach ($values as $key => $value) {
				$languages[$key][$language] = $value;
				if (is_array($value)) {
					$languages[$key]['array'] = true;
				}
			}
		}
		// จัดกลุามภาษาตาม id
		$datas = array();
		$i = 0;
		foreach ($languages as $key => $row) {
			$datas[$i] = ArrayTool::replace(array('id' => $i, 'key' => $key), $row);
			$i++;
		}
		return $datas;
	}

	/**
	 * ตรวจสอบคีย์ของภาษาซ้ำ
	 *
	 * @param array $languages ข้อมูลภาษาที่ต้องการตรวจสอบ
	 * @param string $key รายการที่ต้องการตรวจสอบ
	 * @return int คืนค่าลำดับที่พบ (รายการแรกคือ 0), คืนค่า -1 ถ้าไม่พบ
	 */
	public static function keyExists($languages, $key)
	{
		foreach ($languages as $item) {
			if (strcasecmp($item['key'], $key) == 0) {
				return $item['id'];
				break;
			}
		}
		return -1;
	}

	/**
	 * บันทึกไฟล์ภาษา
	 *
	 * @param array $languages
	 * @param string $type
	 * @return string
	 */
	public static function save($languages, $type)
	{
		$datas = array();
		foreach ($languages as $items) {
			foreach ($items as $key => $value) {
				if (!in_array($key, array('id', 'key', 'array'))) {
					$datas[$key][$items['key']] = $value;
				}
			}
		}
		$language_folder = self::languageFolder();
		foreach ($datas as $lang => $items) {
			$list = array();
			foreach ($items as $key => $value) {
				if ($type == 'js') {
					if (is_string($value)) {
						$list[] = "var $key = '$value';";
					} else {
						$list[] = "var $key = $value;";
					}
				} elseif (is_array($value)) {
					$save = array();
					foreach ($value as $k => $v) {
						$data = '';
						if (preg_match('/^[0-9]+$/', $k)) {
							$data = $k.' => ';
						} else {
							$data = '\''.$k.'\' => ';
						}
						if (is_string($v)) {
							$data .= '\''.$v.'\'';
						} else {
							$data .= $v;
						}
						$save[] = $data;
					}
					$list[] = '\''.$key."' => array(\n    ".implode(",\n    ", $save)."\n  )";
				} elseif (is_string($value)) {
					$list[] = '\''.$key.'\' => \''.($value).'\'';
				} else {
					$list[] = '\''.$key.'\' => '.$value;
				}
			}
			$f = @fopen($language_folder.$lang.'.'.$type, 'wb');
			if ($f !== false) {
				if ($type == 'php') {
					$content = "<"."?php\n/* language/$lang.php */\nreturn array(\n  ".implode(",\n  ", $list)."\n);";
				} else {
					$content = implode("\n", $list);
				}
				fwrite($f, $content);
				fclose($f);
			} else {
				return sprintf(Language::get('File %s cannot be created or is read-only.'), $lang.'.'.$type);
			}
		}
		return '';
	}

	/**
	 * อ่านชื่อภาษาที่กำลังใช้งานอยู่
	 *
	 * @return string
	 */
	public static function name()
	{
		if (null === self::$languages) {
			new static;
		}
		return self::$language_name;
	}

	/**
	 * อ่านภาษา
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key)
	{
		if (null === self::$languages) {
			new static;
		}
		if (is_array($key)) {
			// มาจากการ Parse Theme
			$key = $key[1];
		}
		return isset(self::$languages->$key) ? self::$languages->$key : $key;
	}

	/**
	 * อ่านภาษาหลายรายการ ตามที่กำหนดโดย $keys
	 *
	 * @param array $keys
	 * @return array
	 */
	public static function getItems(array $keys = array())
	{
		if (null === self::$languages) {
			new static;
		}
		$result = array();
		foreach ($keys as $key) {
			$result[$key] = isset(self::$languages->$key) ? self::$languages->$key : $key;
		}
		return $result;
	}

	/**
	 * ค้นหาข้อความภาษาที่ต้องการ ถ้าไม่พบคืนค่า $default
	 *
	 * @param string $key
	 * @param mixed $default
	 */
	public static function find($key, $default = '')
	{
		if (null === self::$languages) {
			new static;
		}
		return isset(self::$languages->$key) ? self::$languages->$key : $default;
	}
}