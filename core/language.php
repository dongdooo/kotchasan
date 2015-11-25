<?php
/**
 * @filesource core/language.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Class สำหรับการโหลด config
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Language
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
	private $language_name;
	/**
	 * รายการภาษา
	 *
	 * @var array
	 */
	private $languages;

	/**
	 * โหลดภาษา
	 */
	public function __construct()
	{
		// โฟลเดอร์ ภาษา
		$datas_folder = self::languageFolder();
		// ภาษาที่เลือก
		$lang = \Input::get('GET,COOKIE', 'lang,my_lang');
		// ตรวจสอบภาษา ใช้ภาษาแรกที่เจอ
		foreach (array_merge((array)$lang, \Kotchasan::$config->languages) as $item) {
			if (!empty($item)) {
				if (is_file($datas_folder.$item.'.php')) {
					$language = include $datas_folder.$item.'.php';
					if (isset($language)) {
						$this->languages = $language;
						$this->language_name = $item;
						// บันทึกภาษาที่กำลังใช้งานอยู่ลงใน cookie
						setcookie('my_lang', $item, time() + 2592000, '/');
						break;
					}
				}
			}
		}
		if (empty($this->languages)) {
			// default language
			$this->language_name = 'th';
			$this->languages = array(
				'date_format' => 'd M Y เวลา H:i น.',
				'date_long' => array(
					0 => 'อาทิตย์',
					1 => 'จันทร์',
					2 => 'อังคาร',
					3 => 'พุธ',
					4 => 'พฤหัสบดี',
					5 => 'ศุกร์',
					6 => 'เสาร์'
				),
				'date_short' => array(
					0 => 'อา.',
					1 => 'จ.',
					2 => 'อ.',
					3 => 'พ.',
					4 => 'พฤ.',
					5 => 'ศ.',
					6 => 'ส.'
				),
				'year_offset' => 543,
				'month_long' => array(
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
				'month_short' => array(
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
		\Kotchasan::$language = $this;
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
			$datas_folder = self::languageFolder();
			$files = array();
			\File::listFiles($datas_folder, $files);
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
	 * @return array
	 */
	public static function installed($type)
	{
		$datas_folder = self::languageFolder();
		$datas = array();
		foreach (self::installedLanguage() as $lng) {
			if ($type == 'php') {
				if (is_file($datas_folder.$lng.'.php')) {
					// php
					$datas[$lng] = include($datas_folder.$lng.'.php');
				}
			} elseif (is_file($datas_folder.$lng.'.js')) {
				// js
				$list = file($datas_folder.$lng.'.js');
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
			$datas[$i] = \Arraytool::replace(array('id' => $i, 'key' => $key), $row);
			$i++;
		}
		return $datas;
	}

	/**
	 * ตรวจสอบ key ซ้ำ
	 *
	 * @param type $param
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
		$datas_folder = self::languageFolder();
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
			$f = @fopen($datas_folder.$lang.'.'.$type, 'wb');
			if ($f !== false) {
				if ($type == 'php') {
					$content = "<"."?php\n/* language/$lang.php */\nreturn array(\n  ".implode(",\n  ", $list)."\n);";
				} else {
					$content = implode("\n", $list);
				}
				fwrite($f, $content);
				fclose($f);
			} else {
				return str_replace('%s', $lang.'.'.$type, \Kotchasan::trans('Your file or folder %s is not writable, please CHMOD it to 775 or 777'));
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
		if (!isset(\Kotchasan::$language)) {
			\Kotchasan::$language = new static;
		}
		return \Kotchasan::$language->language_name;
	}

	/**
	 * อ่านภาษา
	 *
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key)
	{
		if (!isset(\Kotchasan::$language)) {
			\Kotchasan::$language = new static;
		}
		if (is_array($key)) {
			// มาจากการ Parse Theme
			$key = $key[1];
		}
		return isset(\Kotchasan::$language->languages[$key]) ? \Kotchasan::$language->languages[$key] : $key;
	}

	/**
	 * ค้นหาข้อความภาษาที่ต้องการ ถ้าไม่พบคืนค่า $default
	 *
	 * @param string $key
	 * @param mixed $default
	 */
	public static function find($key, $default = '')
	{
		if (!isset(\Kotchasan::$language)) {
			\Kotchasan::$language = new static;
		}
		return isset(\Kotchasan::$language->languages[$key]) ? \Kotchasan::$language->languages[$key] : $default;
	}
}