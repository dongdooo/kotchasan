<?php
/**
 * @filesource core/password.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Password Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Password
{

	/**
	 * ฟังก์ชั่น เข้ารหัสข้อความ
	 *
	 * @param string $string ข้อความที่ต้องการเข้ารหัส
	 * @return string ข้อความที่เข้ารหัสแล้ว
	 */
	public static function encode($string)
	{
		$key = sha1((string)\Kotchasan::$config->password_key);
		$str_len = strlen($string);
		$key_len = strlen($key);
		$j = 0;
		$hash = '';
		for ($i = 0; $i < $str_len; $i++) {
			$ordStr = ord(substr($string, $i, 1));
			$j = $j == $key_len ? 0 : $j;
			$ordKey = ord(substr($key, $j, 1));
			$j++;
			$hash .= strrev(base_convert(dechex($ordStr + $ordKey), 16, 36));
		}
		return $hash;
	}

	/**
	 * ฟังก์ชั่น ถอดรหัสข้อความ
	 *
	 * @param string $string ข้อความที่เข้ารหัสจาก gcms::encode()
	 * @assert (\Password::encode("ทดสอบภาษาไทย")) [==] "ทดสอบภาษาไทย"
	 * @assert (\Password::encode(1234)) [==] 1234
	 * @return string ข้อความที่ถอดรหัสแล้ว
	 */
	public static function decode($string)
	{
		$key = sha1((string)\Kotchasan::$config->password_key);
		$str_len = strlen($string);
		$key_len = strlen($key);
		$j = 0;
		$hash = '';
		for ($i = 0; $i < $str_len; $i+=2) {
			$ordStr = hexdec(base_convert(strrev(substr($string, $i, 2)), 36, 16));
			$j = $j == $key_len ? 0 : $j;
			$ordKey = ord(substr($key, $j, 1));
			$j++;
			$hash .= chr($ordStr - $ordKey);
		}
		return $hash;
	}
}