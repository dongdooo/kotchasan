<?php
/**
 * @filesource projects/orm/index.php.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
// ตัวแปรที่จำเป็นสำหรับ Framework ใช้ระบุ root folder
define('APP_PATH', dirname(__FILE__).'/');
// load GCMS
include APP_PATH.'../../core/load.php';
// inint GCMS Framework
Kotchasan::createWebApplication()->run();
