<?php
/**
 * @filesource core/text.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับจัดการข้อความ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Text
{

	/**
	 * ฟังก์ชั่น สุ่มตัวอักษร
	 *
	 * @param int $count จำนวนหลักที่ต้องการ
	 * @param string $chars (optional) ตัวอักษรที่ใช้ในการสุ่ม default abcdefghjkmnpqrstuvwxyz
	 * @return string คืนค่าข้อความ
	 */
	public static function rndname($count, $chars = 'abcdefghjkmnpqrstuvwxyz')
	{
		srand((double)microtime() * 10000000);
		$ret = "";
		$num = strlen($chars);
		for ($i = 0; $i < $count; $i++) {
			$ret .= $chars[rand() % $num];
		}
		return $ret;
	}

	/**
	 * ฟังก์ชั่น ตัดสตริงค์ตามความยาวที่กำหนด
	 * หากข้อความที่นำมาตัดยาวกว่าที่กำหนด จะตัดข้อความที่เกินออก และเติม .. ข้างท้าย
	 *
	 * @param string $text ข้อความที่ต้องการตัด
	 * @param int $len ความยาวของข้อความที่ต้องการ  (จำนวนตัวอักษรรวมจุด)
	 * @return string คืนค่าข้อความ
	 */
	public static function cut($text, $len)
	{
		if (!empty($len)) {
			$len = (int)$len;
			$text = (mb_strlen($text) <= $len || $len < 3) ? $text : mb_substr($text, 0, $len - 2).'..';
		}
		return $text;
	}

	/**
	 * ฟังก์ชั่น preg_replace ของ gcms
	 *
	 * @param array $patt คีย์ใน template
	 * @param array $replace ข้อความที่จะถูกแทนที่ลงในคีย์
	 * @param string $skin template
	 * @return string คืนค่า HTML template
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
	 * @return string
	 */
	public static function topic($topic)
	{
		return trim(preg_replace('/[\r\n\t\s]+/', ' ', \Text::htmlspecialchars($topic)));
	}

	/**
	 * แปลงข้อความสำหรับปุ่ม Quote ของเว็บบอร์ด (Ajax)
	 *
	 * @param string $detail
	 * @assert ('&lt;, &gt;, &#92;, &nbsp;') [==] "<, >, \\,  "
	 * @return string
	 */
	public static function quoteText($detail)
	{
		return str_replace(array('&lt;', '&gt;', '&#92;', '&nbsp;'), array('<', '>', '\\', ' '), $detail);
	}

	/**
	 * ฟังก์ชั่นรับค่าจาก CKEditor ตัด PHP ออก
	 *
	 * @param string $detail
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
	public static function detail_to_text($detail)
	{
		return preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/{/', '/}/', '/&(amp;([\#a-z0-9]+));/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#x007B;', '&#x007D;', '&\\2;'), $detail);
	}

	/**
	 * ฟังก์ชั่น ใช้แทน htmlspecialchars() ของ PHP
	 * & " ' < > \
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
	 * ฟังก์ชั่น แปลงขนาดของไฟล์จาก byte เป็น kb mb
	 *
	 * @param int $bytes ขนาดของไฟล์ เป็น byte
	 * @param int $precision (optional) จำนวนหลักหลังจุดทศนิยม (default 2)
	 * @return string คืนค่าขนาดของไฟล์เป็น KB MB
	 */
	public static function formatFileSize($bytes, $precision = 2)
	{
		$units = array('Bytes', 'KB', 'MB', 'GB', 'TB');
		if ($bytes <= 0) {
			return '0 Byte';
		} else {
			$bytes = max($bytes, 0);
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
			$pow = min($pow, count($units) - 1);
			$bytes /= pow(1024, $pow);
			return round($bytes, $precision).' '.$units[$pow];
		}
	}

	/**
	 * ฟังก์ชั่น unserialize ถ้าไม่สำเร็จจะคืนค่า array ว่าง
	 *
	 * @param mixed $datas ข้อความ serialize
	 * @param string $key (optional) ถ้า $datas เป็น array ต้องระบุ $key ด้วย
	 * @return array คืนค่าแอเรย์ที่ได้จากการทำ serialize
	 */
	public static function unserialize($datas, $key = '')
	{
		if (is_array($datas)) {
			if (isset($datas[$key])) {
				$result = trim($datas[$key]);
			} else {
				return array();
			}
		} else {
			$result = trim($datas);
		}
		if (!empty($result)) {
			$result = @unserialize($result);
		}
		return is_array($result) ? $result : array();
	}

	/**
	 * ฟังก์ชั่น ลบช่องว่าง และ ตัวอักษรขึ้นบรรทัดใหม่ ที่ติดกันเกินกว่า 1 ตัว
	 *
	 * @param string $text  ข้อความ
	 * @return string คืนค่าข้อความ
	 */
	public static function oneLine($text)
	{
		return trim(preg_replace('/[\r\n\t\s]+/', ' ', $text));
	}

	/**
	 * แทนที่ข้อความด้วยข้อมูลจากแอเรย์ รองรับข้อมูลรูปแบบแอเรย์ย่อยๆ
	 *
	 * @param string $text
	 * @param array $array array($key1 => $value1, $key2 => $value2, array($key3 => $value3, $key4 => $value4))
	 * @return string
	 */
	public static function replaceAll($text, $array)
	{
		if (!empty($array)) {
			$keys = array();
			$values = array();
			\Arraytool::extract($array, $keys, $values);
			$text = str_replace($keys, $values, $text);
		}
		return $text;
	}
}