<?php
/**
 * @filesource js/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Js\Index;

/**
 * Generate JS file
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \View
{

	/**
	 * สร้างไฟล์ js
	 */
	public function index()
	{
		// cache 1 month
		$expire = 2592000;
		$this->setHeaders(array(
			'Content-type' => 'text/javascript; charset: UTF-8',
			'Cache-Control' => 'max-age='.$expire.', must-revalidate, public',
			'Expires' => gmdate('D, d M Y H:i:s', time() + $expire).' GMT',
			'Last-Modified' => gmdate('D, d M Y H:i:s', time() - $expire).' GMT'
		));
		// default js
		$js = array();
		$js[] = file_get_contents(ROOT_PATH.'js/gajax.js');
		$js[] = file_get_contents(ROOT_PATH.'js/gddmenu.js');
		$js[] = file_get_contents(ROOT_PATH.'js/table.js');
		$js[] = file_get_contents(ROOT_PATH.'js/common.js');
		$lng = \Language::name();
		$data_folder = \Language::languageFolder();
		if (is_file($data_folder.$lng.'.js')) {
			$js[] = file_get_contents($data_folder.$lng.'.js');
		}
		$js[] = 'var WEB_URL = "'.WEB_URL.'";';
		$js[] = 'Date.monthNames = ["'.implode('", "', \Language::get('month_short')).'"];';
		$js[] = 'Date.longMonthNames = ["'.implode('", "', \Language::get('month_long')).'"];';
		$js[] = 'Date.longDayNames = ["'.implode('", "', \Language::get('date_long')).'"];';
		$js[] = 'Date.dayNames = ["'.implode('", "', \Language::get('date_short')).'"];';
		$js[] = 'Date.yearOffset = '.(int)\Language::get('year_offset').';';
		// compress javascript
		$patt = array('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#u', '#[\r\t]#', '#\n//.*\n#', '#;//.*\n#', '#[\n]#', '#[\s]{2,}#');
		$replace = array('', '', '', ";\n", '', ' ');
		$this->output(preg_replace($patt, $replace, implode("\n", $js)));
	}
}