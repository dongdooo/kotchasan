<?php
/*
 * @filesource Kotchasan/Http/Request.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

use \Psr\Http\Message\RequestInterface;
use \Kotchasan\Http\Stream;
use \Kotchasan\Http\UploadedFile;
use \Kotchasan\InputItem;
use \Kotchasan\InputItems;

/**
 * คลาสสำหรับจัดการตัวแปรต่างๆจาก Server
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Request extends AbstractRequest implements RequestInterface
{
	/**
	 * @var array
	 */
	private $serverParams;
	/**
	 * @var array
	 */
	private $cookieParams;
	/**
	 * @var array
	 */
	private $queryParams;
	/**
	 * @var array
	 */
	private $parsedBody;
	/**
	 * @var array
	 */
	private $uploadedFiles;
	/**
	 * @var array
	 */
	private $attributes = array();

	/**
	 * คืนค่าจากตัวแปร $_SERVER
	 *
	 * @return array
	 */
	public function getServerParams()
	{
		if ($this->serverParams === null) {
			$this->serverParams = $_SERVER;
		}
		return $this->serverParams;
	}

	/**
	 * คืนค่าจากตัวแปร $_COOKIE
	 *
	 * @return array
	 */
	public function getCookieParams()
	{
		if ($this->cookieParams === null) {
			$this->cookieParams = $_COOKIE;
		}
		return $this->cookieParams;
	}

	/**
	 * กำหนดค่า cookieParams
	 *
	 * @param array $cookies
	 */
	public function withCookieParams(array $cookies)
	{
		$clone = clone $this;
		$clone->cookieParams[$name] = $value;
		return $clone;
	}

	/**
	 * คืนค่าจากตัวแปร $_GET
	 *
	 * @return null|array|object
	 */
	public function getQueryParams()
	{
		if ($this->queryParams === null) {
			$this->queryParams = $this->normalize($_GET);
		}
		return $this->queryParams;
	}

	/**
	 * กำหนดค่า queryParams
	 *
	 * @param array $query
	 * @return self
	 */
	public function withQueryParams(array $query)
	{
		$clone = clone $this;
		$clone->queryParams = $query;
		return $clone;
	}

	/**
	 * คืนค่าจากตัวแปร $_POST
	 *
	 * @return null|array|object
	 */
	public function getParsedBody()
	{
		if ($this->parsedBody === null) {
			$this->parsedBody = $this->normalize($_POST);
		}
		return $this->parsedBody;
	}

	/**
	 * กำหนดค่า parsedBody
	 *
	 * @param null|array|object $data
	 */
	public function withParsedBody($data)
	{
		$clone = clone $this;
		$clone->parsedBody = $data;
		return $clone;
	}

	/**
	 * คืนค่าไฟล์อัปโหลด $_FILES
	 *
	 * @return array แอเรย์ของ UploadedFileInterface
	 */
	public function getUploadedFiles()
	{
		if ($this->uploadedFiles === null) {
			$this->uploadedFiles = array();
			if (isset($_FILES)) {
				foreach ($_FILES as $name => $file) {
					if (is_array($file['name'])) {
						foreach ($file['name'] as $key => $value) {
							$this->uploadedFiles[$name][$key] = new UploadedFile($file['tmp_name'][$key], $value, $file['type'][$key], $file['size'][$key], $file['error'][$key]);
						}
					} else {
						$this->uploadedFiles[$name] = new UploadedFile($file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
					}
				}
			}
		}
		return $this->uploadedFiles;
	}

	/**
	 * กำหนดค่า uploadedFiles
	 *
	 * @param array $uploadedFiles
	 * @return self
	 */
	public function withUploadedFiles(array $uploadedFiles)
	{
		$clone = clone $this;
		$clone->uploadedFiles = $uploadedFiles;
		return $clone;
	}

	/**
	 * คืนค่า attributes ทั้งหมด
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * อ่านค่า attributes ที่ต้องการ
	 *
	 * @param string $name ชื่อของ attributes
	 * @param mixed $default คืนค่า $default ถ้าไม่พบ
	 * @return mixed
	 */
	public function getAttribute($name, $default = null)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
	}

	/**
	 * กำหนดค่า attributes
	 *
	 * @param string $name ชื่อของ attributes
	 * @param mixed $value ค่าของ attribute
	 * @return self
	 */
	public function withAttribute($name, $value)
	{
		$clone = clone $this;
		$clone->attributes[$name] = $value;
		return $clone;
	}

	/**
	 * ลบ attributes
	 *
	 * @param string $name ชื่อของ attributes
	 * @return self
	 */
	public function withoutAttribute($name)
	{
		$clone = clone $this;
		unset($clone->attributes[$name]);
		return $clone;
	}

	/**
	 * อ่านค่าจากตัวแปร $_GET
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems InputItem หรือ Collection ของ InputItem
	 */
	public function get($name, $default = null)
	{
		return $this->createInputItem($this->getQueryParams(), $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_POST
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|array InputItem หรือ แอเรย์ของ InputItem
	 */
	public function post($name, $default = null)
	{
		return $this->createInputItem($this->getParsedBody(), $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_POST $_GET $_COOKIE ตามลำดับ
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems InputItem หรือ Collection ของ InputItem
	 */
	public function request($name, $default = null)
	{
		$datas = $this->getParsedBody();
		if (!isset($datas[$name])) {
			$datas = $this->getQueryParams();
			if (!isset($datas[$name])) {
				$datas = $this->getCookieParams();
			}
		}
		return $this->createInputItem($datas, $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_SESSION
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems InputItem หรือ Collection ของ InputItem
	 */
	public function session($name, $default = null)
	{
		return $this->createInputItem($_SESSION, $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร $_COOKIE
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems InputItem หรือ Collection ของ InputItem
	 */
	public function cookie($name, $default = '')
	{
		$datas = $this->getCookieParams();
		return $this->createInputItem($datas, $name, $default);
	}

	/**
	 * ฟังก์ชั่น อ่าน ip ของ client
	 *
	 * @return string IP ที่อ่านได้
	 */
	public function getClientIp()
	{
		$server = $this->getServerParams();
		if (isset($server['HTTP_CLIENT_IP'])) {
			return $server['HTTP_CLIENT_IP'];
		} elseif (isset($server['HTTP_FORWARDED_FOR'])) {
			return $server['HTTP_FORWARDED_FOR'];
		} elseif (isset($server['HTTP_X_FORWARDED_FOR'])) {
			return $server['HTTP_X_FORWARDED_FOR'];
		} else {
			return $server['REMOTE_ADDR'];
		}
	}

	/**
	 * ฟังก์ชั่นเริ่มต้นใช้งาน session
	 */
	public function inintSession()
	{
		$sessid = $this->get('sessid')->toString();
		if (!empty($sessid) && preg_match('/[a-zA-Z0-9]{20,}/', $sessid)) {
			session_id($sessid);
		}
		session_start();
		if (!ob_get_status()) {
			if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
				// เปิดใช้งานการบีบอัดหน้าเว็บไซต์
				ob_start('ob_gzhandler');
			} else {
				ob_start();
			}
		}
		return true;
	}

	/**
	 * ฟังก์ชั่น ตรวจสอบ referer
	 *
	 * @return bool คืนค่า true ถ้า referer มาจากเว็บไซต์นี้
	 */
	public function isReferer()
	{
		$server = $this->getServerParams();
		$host = empty($server["HTTP_HOST"]) ? $server["SERVER_NAME"] : $server["HTTP_HOST"];
		$referer = isset($server['HTTP_REFERER']) ? $server['HTTP_REFERER'] : '';
		if (preg_match("/$host/ui", $referer)) {
			return true;
		} elseif (preg_match('/^(http(s)?:\/\/)(.*)(\/.*){0,}$/U', WEB_URL, $match)) {
			return preg_match("/$match[3]/ui", $referer);
		} else {
			return false;
		}
	}

	/**
	 * รับค่าจาก input เช่น $_GET หรือ $_POST
	 * มีการฟิลเตอร์ข้อมูลตามชื่อของ input
	 *
	 * @param array $array $_GET หรือ $_POST
	 * @return array
	 */
	public function filter($array)
	{
		$result = array();
		foreach ($array as $key => $value) {
			if (preg_match('/^(text|topic|detail|textarea|email|url|bool|boolean|number|int|float|double|date)_([a-zA-Z0-9_]+)/', $key, $match)) {
				if (is_array($value)) {
					foreach ($value as $k => $v) {
						$result[$match[2]][$k] = $this->filterByType($match[1], $v);
					}
				} else {
					$result[$match[2]] = $this->filterByType($match[1], $value);
				}
			} elseif (preg_match('/^[^_][a-z0-9_]+$/', $key)) {
				// อื่นๆ
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * remove slashes (/)
	 *
	 * @param array $vars ตัวแปร Global เช่น $_POST $_GET
	 */
	private function normalize($vars)
	{
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
			return $this->stripSlashes($vars);
		}
		return $vars;
	}

	/**
	 * ฟังก์ชั่น remove slashes (/)
	 *
	 * @param array $datas
	 * @return array
	 */
	private function stripSlashes($datas)
	{
		if (is_array($datas)) {
			foreach ($datas as $key => $value) {
				$datas[$key] = $this->stripSlashes($value);
			}
			return $datas;
		}
		return stripslashes($datas);
	}

	/**
	 * อ่านค่าจาก $source
	 *
	 * @param array $source ตัวแปร $_GET $_POST
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|InputItems InputItem หรือ Collection ของ InputItem
	 */
	private function createInputItem($source, $name, $default)
	{
		if (isset($source[$name])) {
			return is_array($source[$name]) ? new InputItems($source[$name]) : new InputItem($source[$name]);
		} else {
			return new InputItem($default);
		}
	}

	/**
	 * ตรวจสอบตัวแปรตามที่กำหนดโดย $key
	 *
	 * @param string $key ประเภทของฟังก์ชั่นที่ต้องการใช้ทดสอบ
	 * @param mixed $value ตัวแปรที่ต้องการทดสอบ
	 * @return mixed คืนค่าข้อมูลตามชนิดของ $key
	 */
	private function filterByType($key, $value)
	{
		if ($key === 'text') {
			// input text
			return InputItem::create($value)->text();
		} elseif ($key === 'topic') {
			// topic
			return InputItem::create($value)->topic();
		} elseif ($key === 'detail') {
			// ckeditor
			return InputItem::create($value)->detail();
		} elseif ($key === 'textarea') {
			// textarea
			return InputItem::create($value)->textarea();
		} elseif ($key === 'url' || $key === 'email') {
			// http://www.domain.tld และ email
			return InputItem::create($value)->url();
		} elseif ($key === 'bool' || $key === 'boolean') {
			// true หรือ false เท่านั้น
			return InputItem::create($value)->toBoolean();
		} elseif ($key === 'number') {
			// ตัวเลขเท่านั้น
			return preg_replace('/[^\d]/', '', $value);
		} elseif ($key === 'int') {
			// ตัวเลขและเครื่องหมายลบ
			return (int)$value;
		} elseif ($key === 'double') {
			// ตัวเลขรวมทศนิยม
			return (double)$value;
		} elseif ($key === 'float') {
			// ตัวเลขรวมทศนิยม
			return (float)$value;
		} elseif ($key === 'date') {
			// วันที่
			return preg_replace('/[^\d\s\-:]/', '', $value);
		}
		return null;
	}
}