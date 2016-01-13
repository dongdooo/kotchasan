<?php
/*
 * @filesource url.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Class สำหรับจัดการ URL
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Url
{

	/**
	 * ฟังก์ชั่น แทนที่ query string ด้วยข้อมูลจาก get สำหรับส่งต่อไปยัง URL ถัดไป
	 *
	 * @param array
	 * @assert (array('module' => 'mymodule', 'id' => 1)) [==] "?_module=test&amp;_page=1&amp;_sort=id&amp;module=mymodule&amp;id=1" [[$_GET = array('module' => 'test', 'page' => 1, 'sort' => 'id')]]
	 * @return string คืนค่า query string ใหม่ ลบ id=0
	 */
	public static function replace($urls)
	{
		$qs = array();
		foreach ($_GET AS $key => $value) {
			$qs['_'.$key] = '_'.$key.'='.rawurlencode($value);
		}
		foreach ($urls AS $key => $value) {
			if (is_int($key)) {
				$qs[$value] = $value;
			} else {
				$qs[$key] = $key.'='.$value;
			}
		}
		return sizeof($qs) > 0 ? '?'.implode('&amp;', $qs) : '';
	}

	/**
	 * ฟังก์ชั่น แทนที่ query string ด้วยข้อมูลจาก get สำหรับส่งต่อไปยัง URL ถัดไป
	 *
	 * @param array|string $f รับค่าจากตัวแปร $f มาสร้าง query string
	 * array ส่งมาจาก preg_replace
	 * string กำหนดเอง
	 * @assert (array(2 => 'module=retmodule')) [==] "?module=retmodule&amp;page=1&amp;sort=id"  [[$_GET = array('_module' => 'test', '_page' => 1, '_sort' => 'id')]]
	 * @assert ('module=retmodule') [==] "?module=retmodule&amp;page=1&amp;sort=id" [[$_GET = array('_module' => 'test', '_page' => 1, '_sort' => 'id')]]
	 * @return string คืนค่า query string ใหม่ ลบ id=0
	 */
	public static function back($f)
	{
		$qs = array();
		foreach ($_GET AS $key => $value) {
			if (preg_match('/^_{1,}(.*)$/', $key, $match)) {
				$key = $match[1];
			}
			$qs[$key] = $key.'='.rawurlencode($value);
		}
		$f = is_array($f) ? $f[2] : $f;
		if (!empty($f)) {
			foreach (explode('&', str_replace('&amp;', '&', $f)) AS $item) {
				if (preg_match('/^(.*)=(.*)$/', $item, $match)) {
					$qs[$match[1]] = isset($match[2]) ? $match[1].'='.$match[2] : $match[1];
				}
			}
		}
		return preg_replace('/&amp;id=0/', '', (sizeof($qs) > 0 ? '?'.implode('&amp;', $qs) : ''));
	}

	/**
	 * แปลง $_POST เป็น query string สำหรับการส่งกลับไปหน้าเดิม ที่มาจากการโพสต์ด้วยฟอร์ม
	 *
	 * @param string $url URL ที่ต้องการส่งกลับ
	 * @param array $querys query string ที่ต้องการส่งกลับไปด้วย
	 * @assert ('index.php', array('id'=>1)) [==] "index.php?id=1&module=test&page=1&sort=id"  [[$_POST = array('_module' => 'test', '_page' => 1, '_sort' => 'id')]]
	 * @assert ('index.php', array('page'=>2, 'module'=>'mymodule')) [==] "index.php?page=2&module=mymodule&sort=id"  [[$_POST = array('_module' => 'test', '_page' => 1, '_sort' => 'id')]]
	 * @return string URL+query string
	 */
	public static function postBack($url, $querys)
	{
		foreach ($_POST as $key => $value) {
			if (preg_match('/^_{1,}(.*)$/', $key, $match)) {
				$key = $match[1];
				if (!isset($querys[$key])) {
					$querys[$key] = $value;
				}
			}
		}
		$qs = array();
		foreach ($querys as $key => $value) {
			if (!($key == 'id' && $value == 0)) {
				$qs[$key] = $key.'='.rawurlencode($value);
			}
		}
		return $url.(empty($qs) ? '' : '?'.implode('&', $qs));
	}

	/**
	 * ฟังก์ชั่นแสดงผลตัวแบ่งหน้า
	 *
	 * @param int $totalpage จำนวนหน้าทั้งหมด
	 * @param int $page หน้าปัจจุบัน
	 * @param string $url URL ของหน้าอื่นๆ จะแทนที่เลขหน้าที่ตัวแปร %d
	 * @param int $maxlink (optional) จำนวนตัวเลือกแบ่งหน้าสูงสุด ค่าปกติ 9
	 * @return string
	 */
	public static function pagination($totalpage, $page, $url, $maxlink = 9)
	{
		if ($totalpage > $maxlink) {
			$start = $page - floor($maxlink / 2);
			if ($start < 1) {
				$start = 1;
			} elseif ($start + $maxlink > $totalpage) {
				$start = $totalpage - $maxlink + 1;
			}
		} else {
			$start = 1;
		}
		$url = '<a href="'.$url.'" title="'.\Language::get('go to page').' :page">:page</a>';
		$splitpage = ($start > 2) ? str_replace(':page', 1, $url) : '';
		for ($i = $start; $i <= $totalpage && $maxlink > 0; $i++) {
			$splitpage .= ($i == $page) ? '<strong>'.$i.'</strong>' : str_replace(':page', $i, $url);
			$maxlink--;
		}
		$splitpage .= ($i < $totalpage) ? str_replace(':page', $totalpage, $url) : '';
		return empty($splitpage) ? '<strong>1</strong>' : $splitpage;
	}

	/**
	 * ฟังก์ชั่นสร้าง URL สำหรับส่งต่อไปยังหน้าถัดไป
	 *
	 * @param array $query_str array(key1=>value1, key2=>value2, ...)
	 * @assert (array('action'=> 'one', 'visited')) [==] "?action=one&amp;visited"
	 * @return string
	 */
	public static function next($query_str)
	{
		$qs = array();
		foreach ($_GET as $key => $value) {
			$qs[$key] = "$key=$value";
		}
		foreach ($query_str as $key => $value) {
			$qs[$key] = is_int($key) ? $value : "$key=$value";
		}
		return sizeof($qs) > 0 ? '?'.implode('&amp;', $qs) : '';
	}
}