<?php
/*
 * @filesource Kotchasan/Http/Request.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

use \Psr\Http\Message\RequestInterface;
use \Kotchasan\InputItem;
use \Kotchasan\Inputs;
use \Kotchasan\Files;

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
	 * @var Files
	 */
	private $uploadedFiles;
	/**
	 * @var array
	 */
	private $attributes = array();

	/**
	 * คืนค่าจากตัวแปร SERVER
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
	 * คืนค่าจากตัวแปร COOKIE
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
		$clone->cookieParams = $cookies;
		return $clone;
	}

	/**
	 * คืนค่าจากตัวแปร GET
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
	 * คืนค่าจากตัวแปร POST
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
	 * คืนค่าไฟล์อัปโหลด FILES
	 *
	 * @return Files
	 */
	public function getUploadedFiles()
	{
		if ($this->uploadedFiles === null) {
			$this->uploadedFiles = new Files;
			if (isset($_FILES)) {
				foreach ($_FILES as $name => $file) {
					if (is_array($file['name'])) {
						foreach ($file['name'] as $key => $value) {
							$this->uploadedFiles->add($key, $file['tmp_name'][$key], $value, $file['type'][$key], $file['size'][$key], $file['error'][$key]);
						}
					} else {
						$this->uploadedFiles->add($name, $file['tmp_name'], $file['name'], $file['type'], $file['size'], $file['error']);
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
	 * อ่านค่าจากตัวแปร GET
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|Inputs InputItem หรือ Collection ของ InputItem
	 */
	public function get($name, $default = null)
	{
		return $this->createInputItem($this->getQueryParams(), $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร POST
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
	 * อ่านค่าจากตัวแปร POST GET COOKIE ตามลำดับ
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|Inputs InputItem หรือ Collection ของ InputItem
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
	 * อ่านค่าจากตัวแปร SESSION
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|Inputs InputItem หรือ Collection ของ InputItem
	 */
	public function session($name, $default = null)
	{
		return $this->createInputItem($_SESSION, $name, $default);
	}

	/**
	 * กำหนดค่าตัวแปร SESSION
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $value ค่าของตัวแปร
	 * @return self
	 */
	public function setSession($name, $value)
	{
		$_SESSION[$name] = $value;
		return $this;
	}

	/**
	 * อ่านค่าจากตัวแปร COOKIE
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|Inputs InputItem หรือ Collection ของ InputItem
	 */
	public function cookie($name, $default = '')
	{
		$datas = $this->getCookieParams();
		return $this->createInputItem($datas, $name, $default);
	}

	/**
	 * อ่านค่าจากตัวแปร SERVER
	 *
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return self
	 */
	public function server($name, $default = null)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
	}

	/**
	 * ฟังก์ชั่น อ่าน ip ของ client
	 *
	 * @return string|null IP ที่อ่านได้
	 */
	public function getClientIp()
	{
		$server = $this->getServerParams();
		if (isset($server['HTTP_CLIENT_IP'])) {
			return $server['HTTP_CLIENT_IP'];
		} elseif (isset($server['HTTP_FORWARDED_FOR'])) {
			return $server['HTTP_FORWARDED_FOR'];
		} elseif (isset($server['HTTP_X_FORWARDED_FOR'])) {
			$IParray = array_filter(explode(',', $server['HTTP_X_FORWARDED_FOR']));
			return $IParray[0];
		} elseif (isset($server['REMOTE_ADDR'])) {
			return $server['REMOTE_ADDR'];
		}
		return null;
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
	 * remove slashes (/)
	 *
	 * @param array $vars ตัวแปร Global เช่น POST GET
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
	 * @param array $source ตัวแปร GET POST
	 * @param string $name ชื่อตัวแปร
	 * @param mixed $default ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return InputItem|Inputs InputItem หรือ Collection ของ InputItem
	 */
	private function createInputItem($source, $name, $default)
	{
		if (isset($source[$name])) {
			return is_array($source[$name]) ? new Inputs($source[$name]) : new InputItem($source[$name]);
		} else {
			return is_array($default) ? new Inputs($default) : new InputItem($default);
		}
	}
}