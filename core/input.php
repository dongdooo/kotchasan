<?php
/*
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
 */
class Input
{

	/**
	 * อ่านค่าจากตัวแปรของ Server $_GET
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed
	 */
	public static function get($name, $default = '')
	{
		return self::getFromGlobal('GET', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปรของ Server $_POST
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed
	 */
	public static function post($name, $default = '')
	{
		return self::getFromGlobal('POST', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปรของ Server $_POST และ $_GET ตามลำดับ
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed
	 */
	public static function request($name, $default = '')
	{
		return self::getFromGlobal('REQUEST', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปรของ $_SESSION
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed
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
	 * @return mixed
	 */
	public static function cookie($name, $default = '')
	{
		return self::getFromGlobal('COOKIE', $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปรของ Server เช่น $_GET $_POST  $_REQUEST
	 * แปลงผลลัพท์ตามชนิดของตัวแปรตามที่กำหนดโดย $default เช่น
	 * $default = 0 หรือ เลขจำนวนเต็ม ผลลัพท์จะถูกแปลงเป็น int
	 * $default = 0.0 หรือตัวเลขมีจุดทศนิยม จำนวนเงิน ผลลัพท์จะถูกแปลงเป็น double
	 * $default = true หรือ false ผลลัพท์จะถูกแปลงเป็น true หรือ false เท่านั้น
	 *
	 * @param string $var ชื่อตัวแปรของ Server เช่น GET POST REQUEST
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed
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
				$result = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
				break;
			default:
				$result = null;
		}
		if ($result === null) {
			return $default;
		} else {
			if ($default != '') {
				if (is_float($default)) {
					// จำนวนเงิน เช่น 0.0
					$result = (double)$result;
				} elseif (is_int($default)) {
					// เลขจำนวนเต็ม เช่น 0
					$result = (int)$result;
				} elseif (is_bool($default)) {
					// true, false
					$result = $result == 1 ? true : false;
				}
			}
			return $result;
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
			return self::text($value);
		} elseif ($key === 'topic') {
			// topic
			return self::topic($value);
		} elseif ($key === 'detail') {
			// ckeditor
			return self::detail($value);
		} elseif ($key === 'textarea') {
			// textarea
			return self::textarea($value);
		} elseif ($key === 'url' || $key === 'email') {
			// http://www.domain.tld และ email
			return self::htmlspecialchars(trim($value), false);
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
	 * ฟังก์ชั่น ตัดข้อความที่ไม่พึงประสงค์ก่อนบันทึกลง db ที่มาจาก textarea
	 *
	 * @param string $detail ข้อความ
	 * @return string คืนค่าข้อความ
	 */
	public static function textarea($detail)
	{
		return trim(preg_replace(array('/</u', '/>/u', '/\\\/u'), array('&lt;', '&gt;', '&#92;'), nl2br($detail)));
	}

	/**
	 * ฟังก์ชั่นตรวจสอบข้อความใช้เป็น topic
	 * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 *
	 * @param string $topic
	 * @assert (' "ข่าวด่วน" ') [==] "&quot;ข่าวด่วน&quot;"
	 * @return string
	 */
	public static function topic($topic)
	{
		return trim(preg_replace('/[\r\n\t\s]+/', ' ', self::htmlspecialchars($topic)));
	}

	/**
	 * ฟังก์ชั่นรับค่าจาก CKEditor ตัด PHP ออก
	 *
	 * @param string $detail
	 * @assert ("ทด\สอบ/การแทรก&nbsp;&nbsp;<?php echo '555'?>") [==] "ทด\สอบ/การแทรก&nbsp;&nbsp;<?php echo '555'?>"
	 * @return string
	 */
	public static function detail($detail)
	{
		$patt = array(
			'/^(&nbsp;|\s){0,}<br[\s\/]+?>(&nbsp;|\s){0,}$/iu' => '',
			'/<\?(.*?)\?>/su' => '',
			'/\\\/' => '&#92;'
		);
		return preg_replace(array_keys($patt), array_values($patt), $detail);
	}

	/**
	 * ฟังก์ชั่น เข้ารหัส อักขระพิเศษ และ {} ก่อนจะส่งให้กับ textarea หรือ editor ตอนแก้ไข
	 * & " ' < > { } ไม่แปลง รหัส HTML เช่น &amp; &#38;
	 *
	 * @param string $detail
	 * @assert ('&"'."'<>{}&amp;&#38;") [==] "&amp;&quot;&#039;&lt;&gt;&#x007B;&#x007D;&amp;&#38;"
	 * @return string
	 */
	public static function toEditor($detail)
	{
		return preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/{/', '/}/', '/&(amp;([\#a-z0-9]+));/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#x007B;', '&#x007D;', '&\\2;'), $detail);
	}

	/**
	 * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities ใช้แทน htmlspecialchars() ของ PHP
	 *
	 * @param string $string
	 * @param boolean $double_encode (option) default true แปลง รหัส HTML เช่น &amp; เป็น &amp;amp;, false ไม่แปลง
	 * @assert ('&"'."'<>\/&amp;&#38;", true) [==] "&amp;&quot;&#039;&lt;&gt;&#92;/&amp;amp;&amp;#38;"
	 * @assert ('&"'."'<>\/&amp;&#38;", false) [==] "&amp;&quot;&#039;&lt;&gt;&#92;/&amp;&#38;"
	 * @return string
	 */
	public static function htmlspecialchars($string, $double_encode = true)
	{
		$string = preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/\\\/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#92;'), $string);
		if (!$double_encode) {
			$string = preg_replace('/&(amp;([#a-z0-9]+));/', '&\\2;', $string);
		}
		return $string;
	}

	/**
	 * ฟังก์ชั่นแปลง ' เป็น &#39;
	 *
	 * @param string $text
	 * @assert ("What's your name") [==] "What&#39;s your name"
	 * @return string
	 */
	public static function quote($text)
	{
		return str_replace("'", '&#39;', $text);
	}

	/**
	 * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities และลบช่องว่างหัวท้าย
	 * ใช้แปลงค่าที่รับจาก input ที่ไม่ยอมรับ tag
	 *
	 * @param string $text
	 * @assert (' &"'."'<>\/&amp;&#38; ") [==] "&amp;&quot;&#039;&lt;&gt;&#92;/&amp;amp;&amp;#38;"
	 * @return string
	 */
	public static function text($text)
	{
		return self::htmlspecialchars(trim($text));
	}
}