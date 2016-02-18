<?php
/*
 * @filesource Kotchasan/Date.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Language;

/**
 * คลาสจัดการเกี่ยวกับวันที่และเวลา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Date
{

	/**
	 * อ่านวันที่
	 *
	 * @param int $mktime เวลารูปแบบ Unix timestamp, ไม่ระบุ เป็นวันนี้
	 * @return int
	 * @assert (mktime(0, 0, 0, 2, 29, 2016)) [==]  29
	 */
	public static function day($mktime = 0)
	{
		return (int)date('j', empty($mktime) ? time() : $mktime);
	}

	/**
	 * อ่านเดือน
	 *
	 * @param int $mktime เวลารูปแบบ Unix timestamp, ไม่ระบุ เป็นเดือนนี้
	 * @return int
	 * @assert (mktime(0, 0, 0, 2, 29, 2016)) [==]  2
	 */
	public static function month($mktime = 0)
	{
		return (int)date('n', empty($mktime) ? time() : $mktime);
	}

	/**
	 * อ่านปี คศ.
	 *
	 * @param int $mktime เวลารูปแบบ Unix timestamp, ไม่ระบุ เป็นปีนี้
	 * @return int
	 * @assert (mktime(0, 0, 0, 2, 29, 2016)) [==]  2016
	 */
	public static function year($mktime = 0)
	{
		return (int)date('Y', empty($mktime) ? time() : $mktime);
	}

	/**
	 * ฟังก์ชั่นแปลงเวลาเป็นวันที่ตามรูปแบบที่กำหนด สามารถคืนค่าวันเดือนปี พศ. ได้ ขึ้นกับไฟล์ภาษา
	 *
	 * @param int|string $time int เวลารูปแบบ Unix timestamp, string เวลารูปแบบ Y-m-d หรือ Y-m-d H:i:s ถ้าไม่ระบุหมายถึงวันนี้
	 * @param string $format รูปแบบของวันที่ที่ต้องการ (ถ้าไม่ระบุจะใช้รูปแบบที่มาจากระบบภาษา DATE_FORMAT)
	 * @return string วันที่และเวลาตามรูปแบบที่กำหนดโดย $format
	 */
	public static function format($time = 0, $format = '')
	{
		if (empty($time)) {
			$time = time();
		} elseif (is_string($time) && preg_match('/([0-9]+){1,4}-([0-9]+){1,2}-([0-9]+){1,2}(\s([0-9]+){1,2}:([0-9]+){1,2}:([0-9]+){1,2})?/', $time, $match)) {
			$time = mktime(empty($match[4]) ? 0 : (int)$match[4], empty($match[5]) ? 0 : (int)$match[5], empty($match[6]) ? 0 : (int)$match[6], (int)$match[2], (int)$match[3], (int)$match[1]);
		}
		$lang = Language::getItems(array(
			'DATE_FORMAT',
			'DATE_SHORT',
			'DATE_LONG',
			'MONTH_SHORT',
			'MONTH_LONG',
			'YEAR_OFFSET'
		));
		if (empty($format)) {
			$format = $lang['DATE_FORMAT'];
		}
		if (preg_match_all('/(.)/u', $format, $match)) {
			$ret = '';
			foreach ($match[0] AS $item) {
				switch ($item) {
					case ' ':
					case ':':
					case '/':
					case '-':
						$ret .= $item;
						break;
					case 'D':
						$ret .= $lang['DATE_SHORT'][date('w', $time)];
						break;
					case 'l':
						$ret .= $lang['DATE_LONG'][date('w', $time)];
						break;
					case 'M':
						$ret .= $lang['MONTH_SHORT'][date('n', $time)];
						break;
					case 'F':
						$ret .= $lang['MONTH_LONG'][date('n', $time)];
						break;
					case 'Y':
						$ret .= (int)date('Y', $time) + $lang['YEAR_OFFSET'];
						break;
					default:
						$ret .= trim($item) == '' ? ' ' : date($item, $time);
						break;
				}
			}
		} else {
			$ret = date($format, $time);
		}
		return $ret;
	}

	/**
	 * ฟังก์ชั่น คำนวนความแตกต่างของวัน (อายุ)
	 *
	 * @param int $start_date วันที่เริ่มต้นหรือวันเกิด (Unix timestamp)
	 * @param int $end_date วันที่สิ้นสุดหรือวันนี้ (Unix timestamp)
	 * @return array คืนค่า ปี เดือน วัน [year, month, day] ที่แตกต่าง
	 * @assert (mktime(0, 0, 0, 2, 1, 2016), mktime(0, 0, 0, 3, 1, 2016)) [==]  array('year' => 0,'month' => 1, 'day' => 0)
	 */
	public static function compare($start_date, $end_date)
	{
		$Year1 = (int)date("Y", $start_date);
		$Month1 = (int)date("m", $start_date);
		$Day1 = (int)date("d", $start_date);
		$Year2 = (int)date("Y", $end_date);
		$Month2 = (int)date("m", $end_date);
		$Day2 = (int)date("d", $end_date);
		// วันแต่ละเดือน
		$months = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		// ปีอธิกสุรทิน
		if (($Year2 % 4) == 0) {
			$months[2] = 29;
		}
		// ปีอธิกสุรทิน
		if ((($Year2 % 100) == 0) & (($Year2 % 400) != 0)) {
			$months[2] = 28;
		}
		// คำนวนจำนวนวันแตกต่าง
		$YearDiff = $Year2 - $Year1;
		if ($Month2 >= $Month1) {
			$MonthDiff = $Month2 - $Month1;
		} else {
			$YearDiff--;
			$MonthDiff = 12 + $Month2 - $Month1;
		}
		if ($Day1 > $months[$Month2]) {
			$Day1 = 0;
		} elseif ($Day1 > $Day2) {
			$Month2 = $Month2 == 1 ? 13 : $Month2;
			$Day2 += $months[$Month2 - 1];
			$MonthDiff--;
		}
		$ret['year'] = $YearDiff;
		$ret['month'] = $MonthDiff;
		$ret['day'] = $Day2 - $Day1;
		return $ret;
	}

	/**
	 * แปลงวันที่ จาก mktime เป็น Y-m-d สามารถบันทึกลงฐานข้อมูลได้ทันที
	 *
	 * @param int $mktime เวลารูปแบบ Unix timestamp, ไม่ระบุ เป็นวันนี้
	 * @return string คืนค่าวันที่รูป Y-m-d
	 * @assert (1453522271) [==]  date('Y-m-d', 1453522271)
	 */
	public static function mktimeToSqlDate($mktime = 0)
	{
		return date('Y-m-d', empty($mktime) ? time() : $mktime);
	}

	/**
	 * แปลงวันที่ จาก mktime เป็น Y-m-d H:i:s สามารถบันทึกลงฐานข้อมูลได้ทันที
	 *
	 * @param int $mktime เวลารูปแบบ Unix timestamp, ไม่ระบุ เป็นวันนี้
	 * @return string คืนค่า วันที่และเวลาของ mysql เช่น Y-m-d H:i:s
	 */
	public static function mktimeToSqlDateTime($mktime = 0)
	{
		return date('Y-m-d H:i:s', empty($mktime) ? time() : $mktime);
	}

	/**
	 * ฟังก์ชั่น แปลงวันที่และเวลาของ sql เป็น mktime
	 *
	 * @param string $date วันที่ในรูปแบบ Y-m-d H:i:s
	 * @return int คืนค่าเวลาในรูป mktime
	 * @assert (\Kotchasan\Date::mktimeToSqlDateTime(1453522271)) [==] 1453522271
	 */
	public static function sqlDateTimeToMktime($date)
	{
		preg_match('/([0-9]+){1,4}-([0-9]+){1,2}-([0-9]+){1,2}(\s([0-9]+){1,2}:([0-9]+){1,2}:([0-9]+){1,2})?/', $date, $match);
		return mktime(empty($match[4]) ? 0 : (int)$match[4], empty($match[5]) ? 0 : (int)$match[5], empty($match[6]) ? 0 : (int)$match[6], (int)$match[2], (int)$match[3], (int)$match[1]);
	}
}