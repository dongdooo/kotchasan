<?php
/**
 * @filesource core/view.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * View base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends KBase
{
	/**
	 * meta tag
	 *
	 * @var array
	 */
	private $meta;
	/**
	 * Controller ที่เรียก View นี้
	 *
	 * @var \Controller
	 */
	public $controller;
	/**
	 * ลิสต์รายการ breadcrumb
	 *
	 * @var array
	 */
	private $breadcrumb;
	/**
	 * template ของ breadcrumb
	 *
	 * @var type
	 */
	private $breadcrumb_template;

	/**
	 * Class constructor
	 */
	public function __construct($controller)
	{
		$this->controller = $controller;
		$this->breadcrumb_template = \Template::load('', '', 'breadcrumb');
		$this->meta = array();
		$this->breadcrumb = array();
	}

	/**
	 * เพิ่ม breadcrumb
	 *
	 * @param string $url ลิงค์
	 * @param string $tooltip ทูลทิป
	 * @param string $menu ข้อความแสดงใน breadcrumb
	 * @param string $class (option) คลาสสำหรับลิงค์นี้
	 */
	public function addBreadcrumb($url, $menu, $tooltip, $class = '')
	{
		$patt = array('/{CLASS}/', '/{URL}/', '/{TOOLTIP}/', '/{MENU}/');
		$this->breadcrumb[] = preg_replace($patt, array($class, $url, $tooltip, htmlspecialchars_decode($menu)), $this->breadcrumb_template);
	}

	/**
	 * ฟังก์ชั่นกำหนดค่าตัวแปรของ template
	 *
	 * @param array $array ชื่อที่ปรากฏใน template รูปแบบ array(key1=>val1,key2=>val2)
	 * @param int $option FORMAT_TEXT = คีย์แบบข้อความ, FORMAT_PCRE = คีย์แบบ PCRE
	 */
	public function add($array, $option = FORMAT_TEXT)
	{
		$contents = &$this->controller->contents;
		foreach ($array as $key => $value) {
			if ($option === FORMAT_TEXT) {
				$contents['/{'.$key.'}/'] = $value;
			} else {
				$contents[$key] = $value;
			}
		}
	}

	/**
	 * ฟังก์ชั่นใส่ Tag ลงใน Head ของ HTML
	 *
	 * @param array $array
	 */
	public function addHead($array)
	{
		foreach ($array as $value) {
			$this->meta[] = $value;
		}
	}

	/**
	 * ouput เป็น HTML
	 */
	public function renderHTML()
	{
		// contents
		$contents = $this->controller->contents;
		// default for template
		if (!empty($this->meta)) {
			$contents['/(<head.*)(<\/head>)/isu'] = '$1'.implode("\n", $this->meta)."\n".'$2';
		}
		$contents['/{WIDGET_([A-Z]+)(([\s_])(.*))?}/e'] = '\Kotchasan::getWidgets(array(1=>"$1",3=>"$3",4=>"$4"))';
		$contents['/{LNG_([\w\s\.\-\'\(\),%\/:&\#;]+)}/e'] = '\Language::get(array(1=>"$1"))';
		$contents['/{BREADCRUMB}/'] = empty($this->breadcrumb) ? '' : implode('', $this->breadcrumb);
		$contents['/{BACKURL(\?([a-zA-Z0-9=&\-_@\.]+))?}/e'] = '\Url::back';
		$contents['/{WEBTITLE}/'] = self::$cfg->web_title;
		$contents['/{WEBDESCRIPTION}/'] = self::$cfg->web_description;
		$contents['/{LANGUAGE}/'] = \Language::name();
		$contents['/{WEBURL}/'] = WEB_URL;
		$contents['/{SKIN}/'] = \Template::$src;
		$contents['/{ELAPSED}/'] = sprintf('%.3f', microtime(true) - BEGIN_TIME);
		$contents['/{USAGE}/'] = memory_get_peak_usage() / 1024;
		$contents['/{QURIES}/'] = Core\Database\Driver::$query_count;
		$contents['/{VERSION}/'] = VERSION;
		$contents['/^[\s\t]+/m'] = '';
		// แทนที่ลงใน index.html
		echo \Text::pregReplace(array_keys($contents), array_values($contents), \Template::load('', '', 'index'));
	}

	/**
	 * ส่งออกเนื้อหา และ header ตามที่กำหนด
	 *
	 * @param string $content เนื้อหา
	 */
	public function output($content)
	{
		// send header
		foreach ($this->controller->headers as $key => $value) {
			header("$key: $value");
		}
		// output content
		echo $content;
	}

	/**
	 * กำหนด header ให้กับเอกสาร
	 *
	 * @param array $headers
	 */
	public function setHeaders($headers)
	{
		$this->controller->headers = $headers;
	}
}