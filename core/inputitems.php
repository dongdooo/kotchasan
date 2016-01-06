<?php
/*
 * @filesource core/inputitems.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * รายการ input รูปแบบ Array
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class InputItems implements Iterator
{
	/**
	 * ตัวแปรเก็บ properties ของคลาส
	 *
	 * @var Object
	 */
	private $datas = array();

	/**
	 * Class Constructer
	 *
	 * @param array $items รายการ input
	 */
	public function __construct(array $items = array())
	{
		foreach ($items as $key => $value) {
			$this->datas[$key] = InputItem::create($value);
		}
	}

	/**
	 * อ่าน Input ที่ต้องการ
	 *
	 * @param string|int $key รายการที่ต้องการ
	 * @return InputItem
	 */
	public function get($key)
	{
		return $this->datas[$key];
	}

	/**
	 * inherited from Iterator
	 */
	public function rewind()
	{
		reset($this->datas);
	}

	public function current()
	{
		$var = current($this->datas);
		return $var;
	}

	public function key()
	{
		$var = key($this->datas);
		return $var;
	}

	public function next()
	{
		$var = next($this->datas);
		return $var;
	}

	public function valid()
	{
		$key = key($this->datas);
		$var = ($key !== NULL && $key !== FALSE);
		return $var;
	}
}