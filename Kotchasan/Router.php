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
class Router extends \Kotchasan\Container
{
	/**
	 * กฏของ Router สำหรับการแยกหน้าเว็บไซต์
	 *
	 * @var array
	 */
	private $rules = array(
		// index.php/Module/Method/Page/Function
		'/^index\.php\/([a-zA-Z]+)\/(Model|Controller|View)(\/([a-zA-Z0-9_]+))?(\/([a-zA-Z0-9_]+))?/i' => array('module', 'method', '', 'page', '', 'function'),
		// index/model/page/function
		'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)\/([a-z0-9_]+)/i' => array('module', 'method', 'page', 'function'),
		// index/model/page
		'/([a-z]+)\/(model|controller|view)\/([a-z0-9_]+)/i' => array('module', 'method', 'page'),
		// module/action/cat/id
		'/^([a-z]+)\/([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'action', 'cat', 'id'),
		// module/action/cat
		'/^([a-z]+)\/([a-z]+)\/([0-9]+)$/' => array('module', 'action', 'cat'),
		// module/cat/id
		'/^([a-z]+)\/([0-9]+)\/([0-9]+)$/' => array('module', 'cat', 'id'),
		// module/cat
		'/^([a-z]+)\/([0-9]+)$/' => array('module', 'cat'),
		// module/document
		'/^([a-z]+)\/(.*)?$/' => array('module', 'document'),
		// module, module.php
		'/^([a-z0-9_]+)(\.php)?$/' => array('module'),
		// document
		'/^(.*)$/' => array('document')
	);

	/**
	 * inint Router
	 *
	 * @param string $controller Controller รับค่าจาก Router
	 * @return self
	 * @throws \InvalidArgumentException หากไม่พบ Controller
	 */
	public function inint($controller)
	{
		// ตรวจสอบโมดูล
		$modules = $this->parseRoutes($this->request->getUri()->getPath(), $this->request->getQueryParams());
		if (isset($modules['module']) && isset($modules['method']) && isset($modules['page'])) {
			// controller จาก URL
			$controller = ucwords($modules['module']).'\\'.ucwords($modules['page']).'\\'.ucwords($modules['method']);
			$function = empty($modules['function']) ? 'index' : $modules['function'];
		} else {
			// ใช้ controller เริ่มต้น
			$function = empty($modules['function']) ? 'index' : $modules['function'];
		}
		if (method_exists($controller, $function)) {
			// เรียก Controller
			$obj = new $controller($this->request->withQueryParams($modules));
			if (method_exists($obj, $function)) {
				$obj->$function();
			}
		} else {
			throw new \InvalidArgumentException('Controller or method not found.');
		}
		return $this;
	}

	/**
	 * แยก path คืนค่าเป็น query string
	 *
	 * @param string path เช่น /a/b/c.html
	 * @param array $modules query string
	 * @return array
	 * @param array $modules คืนค่า query string ที่ตัวแปรนี้
	 * @assert ('/index.php/css/view', array()) [==] array( 'method' => 'view', 'module' => 'css')
	 * @assert ('/index.php/css/view/index', array()) [==] array( 'method' => 'view', 'page' => 'index', 'module' => 'css')
	 * @assert ('/index.php/css/view/index/inint', array()) [==] array( 'method' => 'view', 'page' => 'index', 'module' => 'css', 'function' => 'inint')
	 * @assert ('/index/model/updateprofile.php', array()) [==] array( 'method' => 'model', 'page' => 'updateprofile', 'module' => 'index')
	 * @assert ('/css/view/index.php', array()) [==] array('module' => 'css', 'method' => 'view', 'page' => 'index')
	 * @assert ('/module/action/1/2', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/action/1/2.html', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/action/1.html', array()) [==] array('module' => 'module', 'action' => 'action', 'cat' => 1)
	 * @assert ('/module/1/2.html', array()) [==] array('module' => 'module', 'cat' => 1, 'id' => 2)
	 * @assert ('/module/1.html', array()) [==] array('module' => 'module', 'cat' => 1)
	 * @assert ('/module/ทดสอบ.html', array()) [==] array('document' => 'ทดสอบ', 'module' => 'module')
	 * @assert ('/module.html', array()) [==] array('module' => 'module')
	 * @assert ('/ทดสอบ.html', array()) [==] array('document' => 'ทดสอบ')
	 * @assert ('/ทดสอบ.html', array('module' => 'test')) [==] array('document' => 'ทดสอบ', 'module' => 'test')
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
		if (!empty($my_path) && !preg_match('/^index\.php$/i', $my_path)) {
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