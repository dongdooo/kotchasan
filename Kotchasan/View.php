<?php
/*
 * @filesource Kotchasan/View.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Language;
use \Kotchasan\Template;

/**
 * View base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Kotchasan\KBase
{
	/**
	 * ตัวแปรเก็บเนื่อหาของเว็บไซต์
	 *
	 * @var array
	 */
	protected $contents = array();
	/**
	 * meta tag
	 *
	 * @var array
	 */
	protected $metas = array();
	/**
	 * รายการ header
	 *
	 * @var array
	 */
	protected $headers = array();
	/**
	 * ตัวแปรเก็บเนื่อหาของเว็บไซต์ ที่จะแทนที่หลังจาก render แล้ว
	 *
	 * @var array
	 */
	protected $after_contents = array();

	/**
	 * ใส่เนื้อหาลงใน $contens
	 *
	 * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1 => val1, key2 => val2)
	 * @param bool $before true (default) ใส่ก่อน render, false ใส่หลัง render
	 */
	public function setContents($array, $before = true)
	{
		foreach ($array as $key => $value) {
			if ($before) {
				$this->contents[$key] = $value;
			} else {
				$this->after_contents[$key] = $value;
			}
		}
	}

	/**
	 * ใส่ Tag ลงใน Head ของ HTML
	 *
	 * @param array $array
	 */
	public function setMetas($array)
	{
		foreach ($array as $key => $value) {
			$this->metas[$key] = $value;
		}
	}

	/**
	 * กำหนด header ให้กับเอกสาร
	 *
	 * @param array $array
	 */
	public function setHeaders($array)
	{
		foreach ($array as $key => $value) {
			$this->headers[$key] = $value;
		}
	}

	/**
	 * ส่งออกเป็น HTML
	 *
	 * @param string|null $template HTML Template ถ้าไม่กำหนด (null) จะใช้ index.html
	 */
	public function renderHTML($template = null)
	{
		// default for template
		if (!empty($this->metas)) {
			$this->contents['/(<head.*)(<\/head>)/isu'] = '$1'.implode("\n", $this->metas)."\n".'$2';
		}
		$this->contents['/{LNG_([\w\s\.\-\'\(\),%\/:&\#;]+)}/e'] = '\Kotchasan\Language::get(array(1=>"$1"))';
		$this->contents['/{WEBTITLE}/'] = self::$cfg->web_title;
		$this->contents['/{WEBDESCRIPTION}/'] = self::$cfg->web_description;
		$this->contents['/{WEBURL}/'] = WEB_URL;
		$this->contents['/{SKIN}/'] = Template::$src;
		$this->contents['/{LANGUAGE}/'] = Language::name();
		$this->contents['/^[\s\t]+/m'] = '';
		foreach ($this->after_contents as $key => $value) {
			$this->contents[$key] = $value;
		}
		// แทนที่ลงใน Template
		if ($template === null) {
			// ถ้าไม่ได้กำหนดมาใช้ index.html
			$template = Template::load('', '', 'index');
		}
		return Template::pregReplace(array_keys($this->contents), array_values($this->contents), $template);
	}

	/**
	 * ส่งออกเนื้อหา และ header ตามที่กำหนด
	 *
	 * @param string $content เนื้อหา
	 */
	public function output($content)
	{
		// send header
		foreach ($this->headers as $key => $value) {
			header("$key: $value");
		}
		// output content
		echo $content;
	}

	/**
	 * ฟังก์ชั่น แทนที่ query string ด้วยข้อมูลจาก get สำหรับส่งต่อไปยัง URL ถัดไป
	 *
	 * @param array|string $f รับค่าจากตัวแปร $f มาสร้าง query string
	 * array ส่งมาจาก preg_replace
	 * string กำหนดเอง
	 * @return string คืนค่า query string ใหม่ ลบ id=0
	 * @assert (array(2 => 'module=retmodule')) [==] "http://localhost/?module=retmodule&amp;page=1&amp;sort=id"  [[$_SERVER['QUERY_STRING'] = '_module=test&_page=1&_sort=id']]
	 * @assert ('module=retmodule') [==] "http://localhost/?module=retmodule&amp;page=1&amp;sort=id" [[$_SERVER['QUERY_STRING'] = '_module=test&_page=1&_sort=id']]
	 */
	public static function back($f)
	{
		$uri = self::$request->getUri();
		$query_url = array();
		foreach (explode('&', str_replace('&amp;', '&', $uri->getQuery())) as $item) {
			if (preg_match('/^(_)?(.*)=(.*)$/', $item, $match)) {
				if ($match[1] === '_') {
					$query_url[$match[2]] = $match[3];
				} elseif (!isset($query_url[$match[2]])) {
					$query_url[$match[2]] = $match[3];
				}
			} else {
				$query_url[] = $item;
			}
		}
		if (is_array($f)) {
			$f = isset($f[2]) ? $f[2] : null;
		}
		if (!empty($f)) {
			foreach (explode('&', str_replace('&amp;', '&', $f)) as $item) {
				if (preg_match('/^(.*)=(.*)$/', $item, $match)) {
					$query_url[$match[1]] = $match[2];
				} else {
					$query_url[] = $item;
				}
			}
			$temp = $query_url;
			$query_url = array();
			foreach ($temp as $key => $value) {
				if (!(empty($value) || ($key == 'action' && ($value == 'login' || $value == 'logout')))) {
					$query_url[$key] = $value;
				}
			}
		}
		return (string)$uri->withQuery($uri->paramsToQuery($query_url, true));
	}
}