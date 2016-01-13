<?php
/*
 * @filesource Kotchasan/File.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

/**
 * คลาสสำหรับจัดการไฟล์และไดเร็คทอรี่
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class File
{

	/**
	 * อ่านนามสกุลของไฟล์เช่น config.php คืนค่า php
	 *
	 * @param string $path ไฟล์
	 * @return string คืนค่า ext ของไฟล์ ตัวอักษรตัวพิมพ์เล็ก
	 */
	public static function ext($path)
	{
		$exts = explode('.', strtolower($path));
		return end($exts);
	}

	/**
	 * อ่านรายชื่อไฟล์ภายใต้ไดเร็คทอรี่รวมไดเร็คทอรี่ย่อย
	 *
	 * @param string $dir ไดเร็คทอรี่ มี / ปิดท้ายด้วย
	 * @param string $result
	 * @param array $filter (option) ไฟล์ฟิลเตอร์ ตัวพิมพ์เล็ก เช่น array('jpg','gif') แอเรย์ว่างหมายถึงทุกนามสกุล
	 */
	public static function listFiles($dir, &$result, $filter = array())
	{
		$f = opendir($dir);
		while (false !== ($text = readdir($f))) {
			if ($text !== '.' && $text !== '..') {
				if (is_dir($dir.$text)) {
					self::listFiles($dir.$text.'/', $result, $filter);
				} elseif (empty($filter) || in_array(self::ext($text), $filter)) {
					$result[] = $dir.$text;
				}
			}
		}
		closedir($f);
	}

	/**
	 * สำเนาไดเร็คทอรี่
	 *
	 * @param string $dir ไดเร็คทอรี่ต้นทาง มี / ปิดท้ายด้วย
	 * @param string $todir ไดเร็คทอรี่ปลายทาง มี / ปิดท้ายด้วย
	 */
	public static function copyDirectory($dir, $todir)
	{
		$f = opendir($dir);
		while (false !== ($text = readdir($f))) {
			if ($text !== '.' && $text !== '..') {
				if (is_dir($dir.$text)) {
					self::copyDirectory($dir.$text.'/', $todir.$text.'/');
				} else {
					copy($dir.$text, $todir.$text);
				}
			}
		}
		closedir($f);
	}

	/**
	 * สร้างและตรวจสอบไดเร็คทอรี่ ให้เขียนได้
	 *
	 * @param string $dir
	 * @param int $mode (optional) default 0755
	 * @return boolean
	 */
	public static function makeDirectory($dir, $mode = 0755)
	{
		if (!is_dir($dir)) {
			$pdir = dirname($dir);
			if (!is_writeable($pdir)) {
				$chmod = @fileperms($pdir);
				@chmod($pdir, 0757);
			} else {
				$chmod = 0;
			}
			$f = @mkdir($dir, $mode);
			if (!empty($chmod)) {
				@chmod($pdir, $chmod);
			}
			return $f;
		} else {
			return @chmod($dir, $mode);
		}
	}

	/**
	 * ลบไดเรคทอรี่และไฟล์ หรือ ไดเร็คทอรี่ในนั้นทั้งหมด
	 *
	 * @param string $dir ไดเรคทอรี่ที่ต้องการลบ
	 */
	public static function removeDirectory($dir)
	{
		if (is_dir($dir)) {
			$f = opendir($dir);
			while (false !== ($text = readdir($f))) {
				if ($text != '.' && $text != '..') {
					if (is_dir($dir.$text)) {
						self::removeDirectory($dir.$text.'/');
					} else {
						@unlink($dir.$text);
					}
				}
			}
			closedir($f);
			@rmdir($dir);
		}
	}
}