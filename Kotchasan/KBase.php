<?php
/*
 * @filesource Kotchasan/KBase.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Http\Server;
use \Kotchasan\Config;

/**
 * Kotchasan Base Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class KBase
{
	/**
	 * Server Class
	 *
	 * @var Server
	 */
	static protected $server;
	/**
	 * Config Class
	 *
	 * @var Config
	 */
	static protected $cfg;
}