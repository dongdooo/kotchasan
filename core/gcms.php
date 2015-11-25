<?php
/**
 * @filesource core/gcms.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * GCMS utility class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Gcms
{
	/**
	 * รายการโมดูลที่ใช้งานอยู่
	 *
	 * @var array
	 */
	public static $install_modules;
	/**
	 * รายการโมดูลทั้งหมด
	 *
	 * @var array
	 */
	public static $install_owners;
	/**
	 * Menu
	 *
	 * @var \Controller
	 */
	public static $menu;
	/**
	 * View
	 *
	 * @var \View
	 */
	public static $view;

	/**
	 * ฟังก์ชั่น HTML highlighter
	 * ทำ highlight ข้อความส่วนที่เป็นโค้ด
	 * จัดการแปลง BBCode
	 * แปลงข้อความ http เป็นลิงค์
	 *
	 * @param string $detail ข้อความ
	 * @param boolean $canview true จะแสดงข้อความเตือน 'ยังไม่ได้เข้าระบบ' หากไม่ได้เข้าระบบ สำหรับส่วนที่อยู่ในกรอบ code
	 * @return string คืนค่าข้อความ
	 */
	public static function highlighter($detail, $canview)
	{
		$patt[] = '/\[(\/)?(i|dfn|b|strong|u|em|ins|del|sub|sup|small|big|ul|ol|li)\]/isu';
		$replace[] = '<\\1\\2>';
		$patt[] = '/\[color=([#a-z0-9]+)\]/isu';
		$replace[] = '<span style="color:\\1">';
		$patt[] = '/\[size=([0-9]+)(px|pt|em|\%)\]/isu';
		$replace[] = '<span style="font-size:\\1\\2">';
		$patt[] = '/\[\/(color|size)\]/isu';
		$replace[] = '</span>';
		$patt[] = '/\[url\](.*)\[\/url\]/U';
		$replace[] = '<a href="\\1" target="_blank" rel="nofollow">\\1</a>';
		$patt[] = '/\[url=(ftp|http)(s)?:\/\/(.*)\](.*)\[\/url\]/U';
		$replace[] = '<a href="\\1\\2://\\3" target="_blank" rel="nofollow">\\4</a>';
		$patt[] = '/\[url=(\/)?(.*)\](.*)\[\/url\]/U';
		$replace[] = '<a href="'.WEB_URL.'\\2" target="_blank" rel="nofollow">\\3</a>';
		$patt[] = '/(\[code=([a-z]{1,})\](.*?)\[\/code\])/uis';
		$replace[] = $canview ? '<code class="content-code \\2">\\3[/code]' : '<code class="content-code">'.\Language::get('Can not view this content').'[/code]';
		$patt[] = '/(\[code\](.*?)\[\/code\])/uis';
		$replace[] = $canview ? '<code class="content-code">\\2[/code]' : '<code class="content-code">'.\Language::get('Can not view this content').'[/code]';
		$patt[] = '/\[\/code\]/usi';
		$replace[] = '</code>';
		$patt[] = '/\[\/quote\]/usi';
		$replace[] = '</blockquote>';
		$patt[] = '/\[quote( q=[0-9]+)?\]/usi';
		$replace[] = '<blockquote><b>'.\Language::get('Quote from the question').'</b>';
		$patt[] = '/\[quote r=([0-9]+)\]/usi';
		$replace[] = '<blockquote><b>'.\Language::get('Quote from the answer').' <em>#\\1</em></b>';
		$patt[] = '/\[google\](.*?)\[\/google\]/usi';
		$replace[] = '<a class="googlesearch" href="http://www.google.co.th/search?q=\\1&amp;&meta=lr%3Dlang_th" target="_blank" rel="nofollow">\\1</a>';
		$patt[] = '/([^["]]|\r|\n|\s|\t|^)(https?:\/\/([^\s<>\"\']+))/';
		$replace[] = '\\1<a href="\\2" target="_blank" rel="nofollow">\\2</a>';
		$patt[] = '/\[youtube\]([a-z0-9-_]+)\[\/youtube\]/i';
		$replace[] = '<div class="youtube"><iframe src="//www.youtube.com/embed/\\1?wmode=transparent"></iframe></div>';
		return preg_replace($patt, $replace, $detail);
	}

	/**
	 * ฟังก์ชั่นแทนที่คำหยาบ
	 *
	 * @param string $detail ข้อความ
	 * @return string คืนค่าข้อความที่ แปลงคำหยาบให้เป็น <em>xxx</em>
	 */
	public static function checkRude($detail)
	{
		if (!empty($settings->wordrude)) {
			$detail = preg_replace("/(".implode('|', $settings->wordrude).")/usi", '<em>xxx</em>', $detail);
		}
		return $detail;
	}

	/**
	 * ฟังก์ชั่นแสดงเนื้อหา
	 *
	 * @param string $detail ข้อความ
	 * @param boolean $canview true จะแสดงข้อความเตือน 'ยังไม่ได้เข้าระบบ' หากไม่ได้เข้าระบบ สำหรับส่วนที่อยู่ในกรอบ code
	 * @param boolean $rude (optional) true=ตรวจสอบคำหยาบด้วย (default true)
	 * @param boolean $txt (optional) true=เปลี่ยน tab เป็นช่องว่าง 4 ตัวอักษร (default false)
	 * @return string
	 */
	public static function showDetail($detail, $canview, $rude = true, $txt = false)
	{
		if ($txt) {
			$detail = preg_replace('/[\t]/', '&nbsp;&nbsp;&nbsp;&nbsp;', $detail);
		}
		if ($rude) {
			return self::highlighter(self::checkRude($detail), $canview);
		} else {
			return self::highlighter($detail, $canview);
		}
	}

	/**
	 * ฟังก์ชั่น แสดง ip แบบซ่อนหลักหลัง ถ้าเป็น admin จะแสดงทั้งหมด
	 *
	 * @param string $ip ที่อยู่ IP ที่ต้องการแปลง (IPV4)
	 * @return string ที่อยู่ IP ที่แปลงแล้ว
	 */
	public static function showip($ip)
	{
		if (\Login::isAdmin()) {
			return $ip;
		} else {
			preg_match('/([0-9]+\.[0-9]+\.)([0-9\.]+)/', $ip, $ips);
			return $ips[1].preg_replace('/[0-9]/', 'x', $ips[2]);
		}
	}

	/**
	 * ฟังก์ชั่น highlight ข้อความค้นหา
	 *
	 * @param string $text ข้อความ
	 * @param string $search ข้อความค้นหา แยกแต่ละคำด้วย ,
	 * @return string คืนค่าข้อความ
	 */
	public static function highlightSearch($text, $search)
	{
		foreach (explode(' ', $search) AS $i => $q) {
			if ($q != '') {
				$text = self::doHighlight($text, $q);
			}
		}
		return $text;
	}

	/**
	 * ฟังก์ชั่น ตรวจสอบและทำ serialize สำหรับภาษา โดยรายการที่มีเพียงภาษาเดียว จะกำหนดให้ไม่มีภาษา
	 *
	 * @param array $array ข้อมูลที่ต้องการจะทำ serialize
	 * @return string คืนค่าข้อความที่ทำ serialize แล้ว
	 */
	public static function array2Ser($array)
	{
		$new_array = array();
		$l = sizeof($array);
		if ($l > 0) {
			foreach ($array AS $i => $v) {
				if ($l == 1 && $i == 0) {
					$new_array[''] = $v;
				} else {
					$new_array[$i] = $v;
				}
			}
		}
		return serialize($new_array);
	}

	/**
	 * ฟังก์ชั่น อ่านหมวดหมู่ในรูป serialize ตามภาษาที่เลือก
	 *
	 * @param mixed $datas ข้อความ serialize
	 * @param string $key (optional) ถ้า $datas เป็น array ต้องระบุ $key ด้วย
	 * @return string คืนค่าข้อความ
	 */
	public static function ser2Str($datas, $key = '')
	{
		if (is_array($datas)) {
			$datas = isset($datas[$key]) ? $datas[$key] : '';
		}
		if (!empty($datas)) {
			$datas = @unserialize($datas);
			if ($datas !== false) {
				$lng = \Language::name();
				$datas = isset($datas[$lng]) ? $datas[$lng] : (isset($datas['']) ? $datas[''] : '');
			}
		}
		return $datas;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบข้อความ ใช้เป็น alias name ตัวพิมพ์เล็ก แทนช่องว่างด้วย _
	 *
	 * @param string $text ข้อความ
	 * @return string คืนค่าข้อความ
	 */
	public static function aliasName($text)
	{
		return preg_replace(array('/[_\(\)\-\+\#\r\n\s\"\'<>\.\/\\\?&\{\}]{1,}/isu', '/^(_)?(.*?)(_)?$/'), array('_', '\\2'), strtolower(trim(strip_tags($text))));
	}

	/**
	 * ฟังก์ชั่น ทำ highlight ข้อความ
	 *
	 * @param string $text ข้อความ
	 * @param string $needle ข้อความที่ต้องการทำ highlight
	 * @return string คืนค่าข้อความ ข้อความที่ highlight จะอยู่ภายใต้ tag mark
	 */
	public static function doHighlight($text, $needle)
	{
		$newtext = '';
		$i = -1;
		$len_needle = mb_strlen($needle);
		while (mb_strlen($text) > 0) {
			$i = mb_stripos($text, $needle, $i + 1);
			if ($i == false) {
				$newtext .= $text;
				$text = '';
			} else {
				$a = self::lastIndexOf($text, '>', $i) >= self::lastIndexOf($text, '<', $i);
				$a = $a && (self::lastIndexOf($text, '}', $i) >= self::lastIndexOf($text, '{LNG_', $i));
				$a = $a && (self::lastIndexOf($text, '/script>', $i) >= self::lastIndexOf($text, '<script', $i));
				$a = $a && (self::lastIndexOf($text, '/style>', $i) >= self::lastIndexOf($text, '<style', $i));
				if ($a) {
					$newtext .= mb_substr($text, 0, $i).'<mark>'.mb_substr($text, $i, $len_needle).'</mark>';
					$text = mb_substr($text, $i + $len_needle);
					$i = -1;
				}
			}
		}
		return $newtext;
	}

	/**
	 * ฟังก์ชั่น ค้นหาข้อความย้อนหลัง
	 *
	 * @param string $text ข้อความ
	 * @param string $needle ข้อความค้นหา
	 * @param int $offset ตำแหน่งเริ่มต้นที่ต้องการค้นหา
	 * @return int คืนค่าตำแหน่งของตัวอักษรที่พบ ตัวแรกคือ 0 หากไม่พบคืนค่า -1
	 */
	private static function lastIndexOf($text, $needle, $offset)
	{
		$pos = mb_strripos(mb_substr($text, 0, $offset), $needle);
		return $pos == false ? -1 : $pos;
	}

	/**
	 * ตรวจสอบว่าเป็นหน้าหลัก (โมดูลที่ติดตั้งแรกสุด) หรือไม่
	 *
	 * @param string $module
	 * @return boolean ถ้าเป็นโมดูลแรกที่ติดตั้ง (เป็นหน้าหลัก) คืนค่า true
	 */
	public static function isHome($module)
	{
		if (empty(\Gcms::$install_modules)) {
			return false;
		} else {
			$first = reset(\Gcms::$install_modules);
			return $first->module == $module;
		}
	}
}