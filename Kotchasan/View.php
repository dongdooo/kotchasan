<?php
/*
 * @filesource Kotchasan/View.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Controller;
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
	 * Controller ที่เรียก View นี้
	 *
	 * @var Controller
	 */
	protected $controller;
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
	 * Class constructor
	 *
	 * @param \Controller $controller
	 */
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * ใส่เนื้อหาลงใน $contens
	 *
	 * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1=>val1,key2=>val2)
	 */
	public function setContents($array)
	{
		foreach ($array as $key => $value) {
			$this->contents[$key] = $value;
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
	 * ouput เป็น HTML
	 */
	public function renderHTML()
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
		// แทนที่ลงใน index.html
		echo Template::pregReplace(array_keys($this->contents), array_values($this->contents), Template::load('', '', 'index'));
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
}