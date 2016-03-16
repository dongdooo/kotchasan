<?php
/*
 * @filesource Kotchasan/Router.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

/**
 * Router class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Router extends \Kotchasan\KBase
{
	/**
	 * กฏของ Router สำหรับการแยกหน้าเว็บไซต์
	 *
	 * @var array
	 */
	private $rules = array(
		// index.php/module/type/folder/page/method
		'/^[a-z0-9]+\.php\/([a-z]+)\/(model|controller|view)(\/([\/a-z0-9_]+)\/([a-z0-9_]+))?$/i' => array('module', 'type', '', 'page', 'method'),
		// module/type/page/method
		'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)\/([a-z0-9_]+)/i' => array('module', 'type', 'page', 'method'),
		// index/model/page
		'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)/i' => array('module', 'type', 'page'),
		// module/action/cat/id
		'/^([a-z]+)\/([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'action', 'cat', 'id'),
		// module/action/cat
		'/^([a-z]+)\/([a-z]+)\/([0-9]+)$/' => array('module', 'action', 'cat'),
		// module/cat/id
		'/^([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'cat', 'id'),
		// module/cat module/document, module/cat/document
		'/^([a-z]+)(\/([0-9]+))?(\/(.*))?$/' => array('module', '', 'cat', '', 'document'),
		// module, module.php
		'/^([a-z0-9_]+)(\.php)?$/' => array('module'),
		// document
		'/^(.*)$/' => array('document')
	);

	/**
	 * inint Router
	 *
	 * @param string $className คลาสที่จะรับค่าจาก Router
	 * @return self
	 * @throws \InvalidArgumentException หากไม่พบคลาสเป้าหมาย
	 */
	public function inint($className)
	{
		// ตรวจสอบโมดูล
		$modules = $this->parseRoutes(self::$request->getUri()->getPath(), self::$request->getQueryParams());
		if (isset($modules['module']) && isset($modules['type']) && isset($modules['page'])) {
			// คลาสจาก URL
			$className = ucwords(implode('\\', array(
				$modules['module'],
				str_replace('/', '\\', $modules['page']),
				$modules['type']
				)), '\\');
			$method = empty($modules['method']) ? 'index' : $modules['method'];
		} else {
			// ไม่ระบุเมธอดมา เรียกเมธอด index
			$method = empty($modules['method']) ? 'index' : $modules['method'];
		}
		if (method_exists($className, $method)) {
			// สร้างคลาส
			$obj = new $className;
			// เรียกเมธอด
			if (method_exists($obj, $method)) {
				$obj->$method(self::$request->withQueryParams($modules));
			}
		} else {
			throw new \InvalidArgumentException('Method '.$method.' not found in '.$className.'.');
		}
		return $this;
	}

	/**
	 * แยก path คืนค่าเป็น query string
	 *
	 * @param string path เช่น /a/b/c.html
	 * @param array $modules query string
	 * @return array
	 * 
	 * @param array $modules คืนค่า query string ที่ตัวแปรนี้
	 * @assert ('/index.php/css/view', array()) [==] array( 'type' => 'view', 'module' => 'css')
	 * @assert ('/print.php/css/view/index', array()) [==] array( 'type' => 'view', 'page' => 'index', 'module' => 'css')
	 * @assert ('/xhr.php/css/view/index/inint', array()) [==] array( 'type' => 'view', 'page' => 'index', 'module' => 'css', 'method' => 'inint')
	 * @assert ('/index/model/updateprofile.php', array()) [==] array( 'type' => 'model', 'page' => 'updateprofile', 'module' => 'index')
	 * @assert ('/index.php/document/model/admin/settings/save') [==] array('module' => 'document', 'type' => 'model', 'page' => 'admin/settings', 'method' => 'save')
	 * @assert ('/css/view/index.php', array()) [==] array('module' => 'css', 'type' => 'view', 'page' => 'index')
	 * @assert ('/module/action/1/2', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/action/1/2.html', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/action/1.html', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1)
	 * @assert ('/module/1/2.html', array()) [==] array('module' => 'module', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/1.html', array()) [==] array('module' => 'module', 'cat' => 1)
	 * @assert ('/module/ทดสอบ.html', array()) [==] array('document' => 'ทดสอบ', 'module' => 'module')
	 * @assert ('/module.html', array()) [==] array('module' => 'module')
	 * @assert ('/ทดสอบ.html', array()) [==] array('document' => 'ทดสอบ')
	 * @assert ('/ทดสอบ.html', array('module' => 'test')) [==] array('document' => 'ทดสอบ', 'module' => 'test')
	 * @assert ('/docs/1/ทดสอบ.html', array('module' => 'test')) [==] array('document' => 'ทดสอบ', 'module' => 'docs', 'cat' => 1)
	 * @assert ('/docs/1/ทดสอบ.html', array()) [==] array('document' => 'ทดสอบ', 'module' => 'docs', 'cat' => 1)
	 * @assert ('/index.php', array('action' => 'one')) [==] array('action' => 'one')
	 * @assert ('/admin_index.php', array('action' => 'one')) [==] array('action' => 'one', 'module' => 'admin_index')
	 */
	public function parseRoutes($path, $modules)
	{
		$base_path = preg_quote(BASE_PATH, '/');
		// แยกเอาฉพาะ path ที่ส่งมา ไม่รวม path ของ application และ นามสกุล
		if (preg_match('/^'.$base_path.'(.*)(\.html?|\/)$/u', $path, $match)) {
			$my_path = $match[1];
		} elseif (preg_match('/^'.$base_path.'(.*)$/u', $path, $match)) {
			$my_path = $match[1];
		}
		if (!empty($my_path) && !preg_match('/^[a-z0-9]+\.php$/i', $my_path)) {
			$my_path = rawurldecode($my_path);
			foreach ($this->rules AS $patt => $items) {
				if (preg_match($patt, $my_path, $match)) {
					foreach ($items AS $i => $key) {
						if (!empty($key) && isset($match[$i + 1])) {
							$value = $match[$i + 1];
							if (!empty($value)) {
								$modules[$key] = $value;
							}
						}
					}
					break;
				}
			}
		}
		return $modules;
	}
}