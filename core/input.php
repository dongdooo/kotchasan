<?php
/**
 * @filesource core/input.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับจัดการตัวแปรต่างๆของ Server
 * เช่น $_GET $_POST $_SESSION $_COOKIE $_SERVER $_REQUEST
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 *
 * @setup $_GET['test'] = 'test';
 */
class Input
{

	/**
	 * อ่านค่าจากตัวแปรของ Server เช่น $_GET $_POST $_SESSION $_COOKIE $_SERVER $_REQUEST
	 * แปลงผลลัพท์ตามชนิดของตัวแปรตามที่กำหนดโดย $default เช่น
	 * $default = 0 หรือ เลขจำนวนเต็ม ผลลัพท์จะถูกแปลงเป็น int
	 * $default = 0.0 หรือตัวเลขมีจุดทศนิยม จำนวนเงิน ผลลัพท์จะถูกแปลงเป็น double
	 * $default = true หรือ false ผลลัพท์จะถูกแปลงเป็น true หรือ false เท่านั้น
	 *
	 * @param mixed $var ตัวแปร array หรือชื่อตัวแปรของ Server เช่น GET POST REQUEST SESSION COOKIE SERVER
	 * @param string $name (option) ชื่อตัวแปร
	 * @param mixed $default (option) ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @assert ($_GET, 'test') [==] "test"
	 * @assert ('POST,GET', 'test,test') [==] "test"
	 * @return mixed ค่าตัวแปร $className[$name] ถ้าไม่พบคืนค่า $default
	 */
	public static function get($var, $name, $default = '')
	{
		if (is_array($var)) {
			$result = isset($var[$name]) ? $var[$name] : null;
		} elseif (is_string($var) && preg_match_all('/(GET|POST|REQUEST|SESSION|COOKIE|SERVER)/', $var, $keys)) {
			$result = self::getVar($keys[0], explode(',', $name));
		} else {
			$result = null;
		}
		if ($result === null) {
			return $default;
		} else {
			if (is_float($default)) {
				// จำนวนเงิน เช่น 0.0
				$result = (double)$result;
			} elseif (is_int($default)) {
				// เลขจำนวนเต็ม เช่น 0
				$result = (int)$result;
			} elseif (is_bool($default)) {
				// true, false
				$result = (boolean)$result;
			}
			return $result;
		}
	}

	/**
	 * ฟังก์ชั่นอ่านค่าจากตัวแปร $_GET $_POST $_SESSION $_COOKIE $_SERVER $_REQUEST
	 *
	 * @param array $vars แอแเรย์ของ GET POST REQUEST SESSION COOKIE และ SERVER
	 * @param array $keys ค่าคีย์ที่ต้องการ สัมพันธ์กับ $vars
	 * @return mixed คืนค่าตัวแปรจาก $vars[$keys] ตัวแรกที่พบ ถ้าไม่พบเลยคืนค่า null
	 */
	private static function getVar($vars, $keys)
	{
		$result = null;
		foreach ($vars as $i => $var) {
			if (isset($keys[$i])) {
				$key = $keys[$i];
			}
			if ($var == 'GET') {
				$result = isset($_GET[$key]) ? $_GET[$key] : null;
			} elseif ($var == 'POST') {
				$result = isset($_POST[$key]) ? $_POST[$key] : null;
			} elseif ($var == 'SESSION') {
				$result = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
			} elseif ($var == 'COOKIE') {
				$result = isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
			} elseif ($var == 'SERVER') {
				$result = isset($_SERVER[$key]) ? $_SERVER[$key] : null;
			} else {
				$result = isset($_POST[$key]) ? $_POST[$key] : isset($_GET[$key]) ? isset($_GET[$key]) : null;
			}
			if ($result !== null) {
				break;
			}
		}
		return $result;
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
	 * remove slashes (/) ตัวแปรที่มาจากการ submit
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
			if (!empty($_COOKIE)) {
				$_COOKIE = self::stripSlashes($_COOKIE);
			}
		}
	}
	/**
	 * ฟังก์ชั่น ตรวจสอบสถานะที่กำหนด
	 *
	 * @param object $cfg ตัวแปรแอเรย์ที่มีคีย์ที่ต้องการตรวจสอบเช่น $config
	 * @param string $key คีย์ของ $cfg ที่ต้องการตรวจสอบ
	 * @return boolean คืนค่า true ถ้าสมาชิกที่ login มีสถานะที่กำหนดอยู่ใน $cfg[$key]
	 */

	/**
	 *
	 * @param type $login
	 * @param type $cfg
	 * @param type $key
	 * @return boolean
	 */
	public static function canConfig($login, $cfg, $key)
	{
		if (isset($login['status'])) {
			if ($login['status'] == 1) {
				return true;
			} elseif (isset($cfg->$key)) {
				if (is_array($cfg->$key)) {
					return in_array($login['status'], $cfg->$key);
				} else {
					return in_array($login['status'], explode(',', $cfg->$key));
				}
			}
		}
		return false;
	}

	/**
	 * remove slashes (/)
	 *
	 * @param mixed $data
	 * @return mixed
	 */
	public static function stripSlashes(&$data)
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

	/**
	 * ฟังก์ชั่น ตรวจสอบ referer
	 *
	 * @return boolean คืนค่า true ถ้า referer มาจากเว็บไซต์นี้
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
	 * รับ input จาก text เป็นข้อความอย่างเดียว tag จะถูกแปลงเป็นอักขระ
	 * เช่น < แปลงเป์น &lt; และลบช่องว่างหัวท้าย
	 *
	 * @param array|string $key แอเรย์ เช่น $_GET, $_POST หรือตัวแปร string
	 * @param string $value (option) ถ้ากำหนดมาจะรับค่าจาก $key[$value]
	 * @param string $default (option) ถ้าไม่มีการกำหนดค่ามาจะส่งกลับค่านี้
	 * @return string
	 */
	public static function text($key, $value = '', $default = '')
	{
		if (is_string($key)) {
			$text = $key;
		} elseif (isset($key[$value])) {
			$text = $key[$value];
		} else {
			$text = $default;
		}
		return \String::htmlspecialchars(trim($text));
	}

	/**
	 *  ฟังก์ชั่นตรวจสอบข้อความใช้เป็น tags หรือ keywords
	 *  ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 *
	 * @param array|string $key แอเรย์ เช่น $_GET, $_POST หรือตัวแปร string
	 * @param string $value (option) ถ้ากำหนดมาจะรับค่าจาก $key[$value]
	 * @param int $len (option) ตัดสตริงค์ตามความยาวที่กำหนด 0 หมายถึงไม่ตัด
	 * @return string
	 */
	public static function keywords($key, $value = '', $len = 0)
	{
		if (is_string($key)) {
			$text = $key;
		} elseif (isset($key[$value])) {
			$text = $key[$value];
		} else {
			return '';
		}
		$text = trim(preg_replace('/[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]{1,}/isu', ' ', strip_tags($text)));
		return \String::cut($text, $len);
	}

	/**
	 * ฟังก์ชั่นตรวจสอบข้อความใช้เป็น description
	 * สำหรับตัด tag หรือเอา BBCode ออกจากเนื้อหาที่เป็น HTML ให้เหลือแต่ข้อความล้วน
	 * ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 *
	 * @param array|string $key แอเรย์ เช่น $_GET, $_POST หรือตัวแปร string
	 * @param string $value (option) ถ้ากำหนดมาจะรับค่าจาก $key[$value]
	 * @param int $len (option) ตัดสตริงค์ตามความยาวที่กำหนด 0 หมายถึงไม่ตัด
	 * @return string
	 */
	public static function description($key, $value = '', $len = 0)
	{
		if (is_string($key)) {
			$text = $key;
		} elseif (isset($key[$value])) {
			$text = $key[$value];
		} else {
			return '';
		}
		$patt = array(
			/* style */
			'@<style[^>]*?>.*?</style>@siu' => '',
			/* comment */
			'@<![\s\S]*?--[ \t\n\r]*>@u' => '',
			/* tag */
			'@<[\/\!]*?[^<>]*?>@iu' => '',
			/* keywords */
			'/{(WIDGET|LNG)_[a-zA-Z0-9_]+}/su' => '',
			/* BBCode (code) */
			'/(\[code(.+)?\]|\[\/code\]|\[ex(.+)?\])/ui' => '',
			/* BBCode ทั่วไป [b],[i] */
			'/\[([a-z]+)([\s=].*)?\](.*?)\[\/\\1\]/ui' => '\\3',
			/* ตัวอักษรที่ไม่ต้องการ */
			'/(&amp;|&quot;|&nbsp;|[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]){1,}/isu' => ' '
		);
		$text = trim(preg_replace(array_keys($patt), array_values($patt), $text));
		return \String::cut($text, $len);
	}

	/**
	 * รับ input จาก text แปลง ' และ & เป็น HTML และตัดช่องว่างหัวท้ายออก
	 *
	 * @param array $key เช่น $_GET, $_POST
	 * @param string $value (option) ถ้ากำหนดมาจะรับค่าจาก $key[$value]
	 * @param string $default (option) ถ้าไม่มีการกำหนดค่ามาจะส่งกลับค่านี้
	 * @return string
	 */
	public static function quote($key, $value = '', $default = '')
	{
		if (is_string($key)) {
			$text = $key;
		} elseif (isset($key[$value])) {
			$text = $key[$value];
		} else {
			$text = $default;
		}
		return str_replace(array("'"), array('&#39;'), trim($text));
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
			if (preg_match('/^(text|topic|detail|textarea|email|url|bool|number|int|float|double|date)_([a-zA-Z0-9_]+)/', $key, $match)) {
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
			return \String::htmlspecialchars(trim($value));
		} elseif ($key === 'topic') {
			// topic
			return \String::topic($value);
		} elseif ($key === 'detail') {
			// ckeditor
			return \String::detail($value);
		} elseif ($key === 'textarea') {
			// textarea
			return \String::textarea($value);
		} elseif ($key === 'url' || $key === 'email') {
			// http://www.domain.tld และ email
			return \String::htmlspecialchars(trim($value), false);
		} elseif ($key === 'bool') {
			// true หรือ false เท่านั้น
			return empty($value) ? 0 : 1;
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
}