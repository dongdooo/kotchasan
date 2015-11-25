<?php
/**
 * @filesource core/validator.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับตรวจสอบความถูกต้องของตัวแปรต่างๆ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Validator extends KBase
{

	/**
	 * ตรวจสอบความถูกของอีเมล์
	 *
	 * @param string $email
	 * @assert ('admin@localhost.com') [==] true
	 * @assert ('admin@localhost') [==] true
	 * @assert ('ทดสอบ@localhost') [==] false
	 * @assert ('admin@ไทย') [==] true
	 * @return boolean คืนค่า true ถ้ารูปแบบอีเมล์ถูกต้อง
	 */
	public static function email($email)
	{
		if (function_exists('idn_to_ascii') && preg_match('/(.*)@(.*)/', $email, $match)) {
			// โดเมนภาษาไทย
			$email = $match[1].'@'.idn_to_ascii($match[2]);
		}
		if (preg_match('/^[a-zA-Z0-9_\-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD', $email)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * ฟังก์ชั่นตรวจสอบไฟล์อัปโหลดว่าเป็นรูปภาพหรือไม่
	 *
	 * @param array $excepts ชนิดของไฟล์ที่ยอมรับเช่น array('jpg', 'gif', 'png')
	 * @param array $file_upload รับค่ามาจาก $_FILES
	 * @return array|boolean คืนค่าแอเรย์ [width, height, mime] ของรูปภาพ หรือ  false ถ้าไม่ใช่รูปภาพ
	 */
	public static function isImage($excepts, $file_upload)
	{
		// ext
		$imageinfo = explode('.', $file_upload['name']);
		$imageinfo = array('ext' => strtolower(end($imageinfo)));
		if (in_array($imageinfo['ext'], $excepts)) {
			// Exif
			$info = getImageSize($file_upload['tmp_name']);
			if ($info[0] == 0 || $info[1] == 0 || !Mime::check($excepts, $info['mime'])) {
				return false;
			} else {
				$imageinfo['width'] = $info[0];
				$imageinfo['height'] = $info[1];
				$imageinfo['mime'] = $info['mime'];
				return $imageinfo;
			}
		} else {
			return false;
		}
	}
}