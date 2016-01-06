<?php
/*
 * @filesource core/input.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับจัดการตัวแปรต่างๆของ Server
 * เช่น $_GET $_POST $_SESSION $_COOKIE $_REQUEST
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Input
{

	/**
	 * อ่านค่าจากตัวแปร $_GET
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems
	 */
	public static function get($name, $default = '')
	{
		return self::getFromGlobal('GET', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_POST
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems
	 */
	public static function post($name, $default = '')
	{
		return self::getFromGlobal('POST', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_REQUEST
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems
	 */
	public static function request($name, $default = '')
	{
		return self::getFromGlobal('REQUEST', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_SESSION
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem
	 */
	public static function session($name, $default = '')
	{
		return self::getFromGlobal('SESSION', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_COOKIE
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem
	 */
	public static function cookie($name, $default = '')
	{
		return self::getFromGlobal('COOKIE', $name, $default);
	}

	/**
	 * อ่านค่าจากไฟล์อัปโหลด
	 *
	 * @return InputItem|InputItems
	 */
	public static function files()
	{
		print_r($_FILES);
	}

	/**
	 * อ่านค่าจากตัวแปรของ Server เช่น $_GET $_POST  $_REQUEST
	 * ถ้าไม่พบจะใช้ค่า $default
	 *
	 * @param string $var ชื่อตัวแปรของ Server เช่น GET POST REQUEST
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems
	 */
	private static function getFromGlobal($var, $name, $default)
	{
		switch ($var) {
			case 'GET':
				$result = isset($_GET[$name]) ? $_GET[$name] : null;
				break;
			case 'POST':
				$result = isset($_POST[$name]) ? $_POST[$name] : null;
				break;
			case 'COOKIE':
				$result = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
				break;
			case 'SESSION':
				$result = isset($_SESSION[$name]) ? $_SESSION[$name] : null;
				break;
			case 'REQUEST':
				if (isset($_POST[$name])) {
					$result = $_POST[$name];
				} elseif (isset($_GET[$name])) {
					$result = $_GET[$name];
				} elseif (isset($_COOKIE[$name])) {
					$result = $_COOKIE[$name];
				} else {
					$result = null;
				}
				break;
			default:
				$result = null;
		}
		if ($result === null) {
			return new InputItem($default);
		} else {
			switch ($var) {
				case 'GET':
				case 'POST':
					return is_array($result) ? new InputItems($result) : new InputItem($result);
					break;
				default:
					return new InputItem($result);
			}
		}
	}

	/**
	 * รับค่าจาก input เช่น $_GET หรือ $_POST
	 * มีการฟิลเตอร์ข้อมูลตามชื่อของ input
	 *
	 * @param array $array $_GET หรือ $_POST
	 * @return array
	 */
	public static function filter($array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if (preg_match('/^(text|topic|detail|textarea|email|url|bool|boolean|number|int|float|double|date)_([a-zA-Z0-9_]+)/', $key, $match)) {
				if (is_array($value)) {
					foreach ($value as $k => $v) {
						$result[$match[2]][$k] = self::filterByType($match[1], $v);
					}
				} else {
					$result[$match[2]] = self::filterByType($match[1], $value);
				}
			} elseif (preg_match('/^[^_][a-z0-9_]+$/', $key)) {
				// อื่นๆ
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * ตรวจสอบตัวแปรตามที่กำหนดโดย $key
	 *
	 * @param string $key ประเภทของฟังก์ชั่นที่ต้องการใช้ทดสอบ
	 * @param mixed $value ตัวแปรที่ต้องการทดสอบ
	 * @return mixed คืนค่าข้อมูลตามชนิดของ $key
	 */
	private static function filterByType($key, $value)
	{
		if ($key === 'text') {
			// input text
			return InputItem::create($value)->text();
		} elseif ($key === 'topic') {
			// topic
			return InputItem::create($value)->topic();
		} elseif ($key === 'detail') {
			// ckeditor
			return InputItem::create($value)->detail();
		} elseif ($key === 'textarea') {
			// textarea
			return InputItem::create($value)->textarea();
		} elseif ($key === 'url' || $key === 'email') {
			// http://www.domain.tld และ email
			return InputItem::create($value)->url();
		} elseif ($key === 'bool' || $key === 'boolean') {
			// true หรือ false เท่านั้น
			return InputItem::create($value)->toBoolean();
		} elseif ($key === 'number') {
			// ตัวเลขเท่านั้น
			return preg_replace('/[^\d]/', '', $value);
		} elseif ($key === 'int') {
			// ตัวเลขและเครื่องหมายลบ
			return (int)$value;
		} elseif ($key === 'double') {
			// ตัวเลขรวมทศนิยม
			return (double)$value;
		} elseif ($key === 'float') {
			// ตัวเลขรวมทศนิยม
			return (float)$value;
		} elseif ($key === 'date') {
			// วันที่
			return preg_replace('/[^\d\s\-:]/', '', $value);
		}
	}

	/**
	 * ฟังก์ชั่น อ่าน ip ของ client
	 *
	 * @return string IP ที่อ่านได้
	 */
	public static function ip()
	{
		if (isset($_SERVER)) {
			if (isset($_SERVER['HTTP_CLIENT_IP'])) {
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_FORWARDED_FOR'];
			} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		} else {
			if (getenv('HTTP_CLIENT_IP')) {
				$ip = getenv('HTTP_CLIENT_IP');
			} elseif (getenv('HTTP_FORWARDED_FOR')) {
				$ip = getenv('HTTP_FORWARDED_FOR');
			} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip = getenv('HTTP_X_FORWARDED_FOR');
			} else {
				$ip = getenv('REMOTE_ADDR');
			}
		}
		return $ip;
	}

	/**
	 * ฟังก์ชั่น ตรวจสอบ referer
	 *
	 * @return bool คืนค่า true ถ้า referer มาจากเว็บไซต์นี้
	 */
	public static function isReferer()
	{
		$server = empty($_SERVER["HTTP_HOST"]) ? $_SERVER["SERVER_NAME"] : $_SERVER["HTTP_HOST"];
		$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if (preg_match("/$server/ui", $referer)) {
			return true;
		} elseif (preg_match('/^(http(s)?:\/\/)(.*)(\/.*){0,}$/U', WEB_URL, $match)) {
			return preg_match("/$match[3]/ui", $referer);
		} else {
			return false;
		}
	}

	/**
	 * remove slashes (/) ตัวแปร GLOBAL
	 */
	public static function normalizeRequest()
	{
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			if (!empty($_GET)) {
				$_GET = self::stripSlashes($_GET);
			}
			if (!empty($_POST)) {
				$_POST = self::stripSlashes($_POST);
			}
			if (!empty($_REQUEST)) {
				$_REQUEST = self::stripSlashes($_REQUEST);
			}
		}
	}

	/**
	 * remove slashes (/)
	 *
	 * @param array|string $data
	 * @return array|string
	 */
	private static function stripSlashes(&$data)
	{
		if (is_array($data)) {
			if (sizeof($data) == 0) {
				return $data;
			} else {
				$keys = array_map('stripslashes', array_keys($data));
				$data = array_combine($keys, array_values($data));
				return array_map(array(__CLASS__, 'stripSlashes'), $data);
			}
		} else {
			return stripslashes($data);
		}
	}
}