<?php
/*
 * @filesource http/server.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Http;

use \Psr\Http\Message\UriInterface;
use \Core\Http\Uri;

/**
 * Class สำหรับจัดการตัวแปร Global ต่างๆ
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Server extends Uri
{
	/**
	 *
	 * @var string
	 */
	private $basePath;
	private $docRoot;
	/**
	 * โดเมนของ Server รวม path
	 * เช่น http://domain.tld/folder
	 *
	 * @var string
	 */
	private $webUri;

	public function __construct()
	{
		// Scheme
		$this->scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
		// Host
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			$this->host = trim(current(explode(',', $_SERVER['HTTP_X_FORWARDED_HOST'])));
		} elseif (empty($_SERVER['HTTP_HOST'])) {
			$this->host = $_SERVER['SERVER_NAME'];
		} else {
			$this->host = $_SERVER['HTTP_HOST'];
		}
		// Path
		$this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		// Query string
		$this->query = $_SERVER['QUERY_STRING'];
		// Port
		$pos = strpos($this->host, ':');
		if ($pos !== false) {
			$this->port = (int)substr($this->host, $pos + 1);
			$this->host = strstr($this->host, ':', true);
		} else {
			$this->port = isset($_SERVER['SERVER_PORT']) ? (int)$_SERVER['SERVER_PORT'] : 80;
		}
		if ($this->port == 80 || $this->port == 443) {
			$this->port = null;
		}
		// Username and Password
		$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : '';
		$pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
		$this->userInfo = $user.($pass === '' ? '' : ':'.$pass);
		// ไดเร็คทอรี่รากของเว็บไซต์
		$doc_root = rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$script_filename = rtrim(dirname($_SERVER['SCRIPT_FILENAME']), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
		$this->basePath = str_replace(array($doc_root, '\\'), array('', '/'), $script_filename);
		$this->docRoot = str_replace('\\', '/', $doc_root);
		// Website Uri
		$this->webUri = $this->scheme === '' ? $this->scheme : $this->scheme.'://';
		$this->webUri = rtrim($this->webUri.$this->getAuthority().'/'.$this->basePath, '/').'/';
	}

	/**
	 *
	 * อ่านค่าโดเมนของ Server รวม path
	 *
	 * @return string เช่น http://domain.tld/folder
	 */
	public function getWebUri()
	{
		return $this->webUri;
	}

	/**
	 * ไดเร็คทอรี่ที่ติดตั้งเว็บไซต์
	 *
	 * @return string เช่น เว็บไซต์คือ http://localhost/kotchasan/ จะได้เป็น kotchasan/
	 */
	public function getBasePath()
	{
		return $this->basePath;
	}
}