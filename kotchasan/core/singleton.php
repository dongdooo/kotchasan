<?php
/*
 * @filesource singleton.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Singleton base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
abstract class Singleton
{
	/**
	 * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
	 */
	private static $instance = null;

	final private function __construct()
	{
		// inintial class
		static::inint();
	}

	private function __clone()
	{
		// do nothing
	}

	private function __wakeup()
	{
		// do nothing
	}

	/**
	 * เรียกใช้งาน Class แบบสามารถเรียกได้ครั้งเดียวเท่านั้น
	 *
	 * @return \static
	 */
	public static function &getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * method เรียกเมื่อมีการโหลด Class
	 */
	abstract protected function inint();
}