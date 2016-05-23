<?php
/*
 * @filesource Kotchasan/Grid.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

/**
 * Grid System
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Grid extends \Kotchasan\Template
{

	public function __construct()
	{
		$this->cols = 1;
	}

	/**
	 * กำหนดจำนวนกอลัมน์ของกริด
	 *
	 * @param int $cols จำนวนคอลัมน์ มากกว่า 0
	 * @return \static
	 */
	public function setCols($cols)
	{
		$this->cols = max(1, (int)$cols);
		return $this;
	}

	/**
	 * คืนค่าจำนวนคอลัมน์ของกริด
	 *
	 * @return int
	 */
	public function getCols()
	{
		return $this->cols;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบว่ามีการ add ข้อมูลมาหรือเปล่า
	 *
	 * @return boolean คืนค่า true ถ้ามีการเรียกใช้คำสั่ง add มาก่อนหน้า, หรือ false ถ้าไม่ใช่
	 */
	public function hasItem()
	{
		return empty($this->items) ? false : true;
	}
}