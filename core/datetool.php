<?php
/**
 * @filesource core/datetool.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสจัดการเกี่ยวกับวันที่และเวลา
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Datetool
{

	/**
	 * ฟังก์ชั่น แปลงเวลา (mktime) เป็นวันที่ตามรูปแบบที่กำหนด สามารถคืนค่าวันเดือนปี พศ. ได้ ขึ้นกับไฟล์ภาษา
	 *
	 * @param int $mktime เวลาในรูป mktime
	 * @param string $format (optional) รูปแบบของวันที่ที่ต้องการ (default date_format)
	 * @return string วันที่และเวลาตามรูปแบบที่กำหนดโดย $format
	 */
	public static function format($mktime, $format = '')
	{
		if (empty($format)) {
			$format = \Language::get('date_format');
		}
		$date_short = \Language::get('date_short');
		$date_long = \Language::get('date_long');
		$month_short = \Language::get('month_short');
		$month_long = \Language::get('month_long');
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
						$ret .= $date_short[date('w', $mktime)];
						break;
					case 'l':
						$ret .= $date_long[date('w', $mktime)];
						break;
					case 'M':
						$ret .= $month_short[date('n', $mktime)];
						break;
					case 'F':
						$ret .= $month_long[date('n', $mktime)];
						break;
					case 'Y':
						$ret .= (int)date('Y', $mktime) + \Language::get('year_offset');
						break;
					default:
						$ret .= date($item, $mktime);
				}
			}
		} else {
			$ret = date($format, $mktime);
		}
		return $ret;
	}

	/**
	 * ฟังก์ชั่น คำนวนความแตกต่างของวัน (อายุ)
	 *
	 * @param int $start_date วันที่เริ่มต้นหรือวันเกิด (mktime)
	 * @param int $end_date วันที่สิ้นสุดหรือวันนี้ (mktime)
	 * @return array คืนค่า ปี เดือน วัน [year, month, day] ที่แตกต่าง
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
	 * @param int $mktime วันที่ในรูป mktime
	 * @return string คืนค่าวันที่รูป Y-m-d
	 */
	public static function mktimeToSqlDate($mktime)
	{
		return date('Y-m-d', $mktime);
	}

	/**
	 * แปลงวันที่ จาก mktime เป็น Y-m-d H:i:s สามารถบันทึกลงฐานข้อมูลได้ทันที
	 *
	 * @param int $mktime วันที่ในรูป mktime
	 * @return string คืนค่า วันที่และเวลาของ mysql เช่น Y-m-d H:i:s
	 */
	public static function mktimeToSqlDateTime($mktime)
	{
		return date('Y-m-d H:i:s', $mktime);
	}

	/**
	 * แปลงวันที่ในรูป Y-m-d เป็นวันที่และเวลา เช่น 1 มค. 2555 00:00:00.
	 *
	 * @param string $date วันที่ในรูป Y-m-d หรือ Y-m-d h:i:s
	 * @param bool $short (optional) true=เดือนแบบสั้น, false=เดือนแบบยาว (default true)
	 * @param bool $time (optional) true=คืนค่าเวลาด้วยถ้ามี, false=ไม่ต้องคืนค่าเวลา (default true)
	 * @return string คืนค่า วันที่และเวลา
	 */
	public static function sqlDateToDate($date, $short = true, $time = true)
	{
		if (preg_match('/([0-9]+){0,4}-([0-9]+){0,2}-([0-9]+){0,2}(\s([0-9]+){0,2}:([0-9]+){0,2}:([0-9]+){0,2})?/', $date, $match)) {
			$match[1] = (int)$match[1];
			$match[2] = (int)$match[2];
			if ($match[1] == 0 || $match[2] == 0) {
				return '';
			} else {
				$month = $short ? \Language::get('month_short') : \Language::get('month_long');
				return $match[3].' '.$month[$match[2]].' '.((int)$match[1] + (int)\Language::get('year_offset') ).($time && isset($match[4]) ? $match[4] : '');
			}
		} else {
			return '';
		}
	}

	/**
	 * ฟังก์ชั่น แปลงวันที่และเวลาของ sql เป็น mktime
	 *
	 * @param string $date วันที่ในรูปแบบ Y-m-d H:i:s
	 * @return int คืนค่าเวลาในรูป mktime
	 */
	public static function sqlDateTimeToMktime($date)
	{
		preg_match('/([0-9]+){1,4}-([0-9]+){1,2}-([0-9]+){1,2}(\s([0-9]+){1,2}:([0-9]+){1,2}:([0-9]+){1,2})?/', $date, $match);
		return mktime(empty($match[4]) ? 0 : (int)$match[4], empty($match[5]) ? 0 : (int)$match[5], empty($match[6]) ? 0 : (int)$match[6], (int)$match[2], (int)$match[3], (int)$match[1]);
	}
}