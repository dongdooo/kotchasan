<?php
/**
 * @filesource css/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Css\Index;

/**
 * Generate CSS file
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \View
{

	/**
	 * สร้างไฟล์ CSS
	 */
	public function index()
	{
		// cache 1 month
		$expire = 2592000;
		$this->setHeaders(array(
			'Content-type' => 'text/css; charset: UTF-8',
			'Cache-Control' => 'max-age='.$expire.', must-revalidate, public',
			'Expires' => gmdate('D, d M Y H:i:s', time() + $expire).' GMT',
			'Last-Modified' => gmdate('D, d M Y H:i:s', time() - $expire).' GMT'
		));
		// โหลด css หลัก
		$data = preg_replace('/url\(([\'"])?fonts\//isu', "url(\\1".WEB_URL.'skin/fonts/', file_get_contents(ROOT_PATH.'skin/fonts.css'));
		$data .= file_get_contents(ROOT_PATH.'skin/gcss.css');
		// โหลดจาก template
		$template = str_replace(ROOT_PATH, '', \Kotchasan::$template_root);
		// frontend template
		$skin = 'skin/'.\Kotchasan::$config->skin;
		$data2 = file_get_contents(ROOT_PATH.$template.$skin.'/style.css');
		$data2 = preg_replace('/url\(([\'"])?(img|fonts)\//isu', "url(\\1".WEB_URL.$skin.'/\\2/', $data2);
		// compress css
		$data = preg_replace(array('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '/[\s]{0,}([:;,>\{\}])[\s]{0,}/'), array('', '\\1'), $data.$data2);
		// result
		$this->output(preg_replace(array('/[\r\n\t]/s', '/[\s]{2,}/s', '/;}/'), array('', ' ', '}'), $data));
	}
}