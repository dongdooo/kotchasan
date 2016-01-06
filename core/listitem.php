<?php
/*
 * @filesource core/lisitem.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับจัดการแอเรย์
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class ListItem
{
	/**
	 * ข้อมูล
	 *
	 * @var array
	 */
	public $datas;
	/**
	 * ที่อยู่ไฟล์ที่โหลดมา
	 *
	 * @var string
	 */
	private $source;

	/**
	 * กำหนดค่าเริ่มต้นของ Class
	 *
	 * @param array $config
	 */
	public function inint($config)
	{
		$this->datas = $config;
	}

	/**
	 * อ่านจำนวนสมาชิกทั้งหมด
	 *
	 * @return int จำนวนสมาชิกทั้งหมด
	 */
	public function count()
	{
		return sizeof($this->datas);
	}

	/**
	 * อ่านจำนวนรายการทั้งหมด
	 *
	 * @return array คืนค่ารายการทั้งหมด
	 */
	public function items()
	{
		return $this->datas;
	}

	/**
	 * อ่านรายชื่อ keys
	 *
	 * @return array แอเรย์ของรายการ key ทั้งหมด
	 */
	public function keys()
	{
		return array_keys($this->datas);
	}

	/**
	 * อ่านรายการข้อมูลทั้งหมด
	 *
	 * @return array แอเรย์ของข้อมูลทั้งหมด
	 */
	public function values()
	{
		return array_values($this->datas);
	}

	/**
	 * อ่านข้อมูลที่ $index
	 *
	 * @param string $index
	 * @return mixed คืนค่ารายการที่ $index ถ้าไม่พบคืนค่า null
	 */
	public function get($index)
	{
		if (isset($this->datas[$index])) {
			$result = $this->datas[$index];
		} else {
			$result = null;
		}
		return $result;
	}

	/**
	 * เพิ่มรายการใหม่ที่ลำดับสุดท้าย ถ้ามี $index อยู่แล้วจะแทนที่รายการเดิม
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function set($index, $value)
	{
		$this->datas[$index] = $value;
	}

	/**
	 * อ่านข้อมูลรายการแรก
	 *
	 * @return mixed คืนค่าแอเรย์รายการแรก
	 */
	public function firstItem()
	{
		return reset($this->datas);
	}

	/**
	 * อ่านข้อมูลรายการสุดท้าย
	 *
	 * @return mixed คืนค่าแอเรย์รายการสุดท้าย
	 */
	public function lastItem()
	{
		return end($this->datas);
	}

	/**
	 * ลบรายการที่กำหนด
	 *
	 * @param string $index ของรายการที่ต้องการจะลบ
	 * @return boolean คืนค่า true ถ้าสำเร็จ, false ถ้าไม่พบ
	 */
	public function delete($index)
	{
		if (isset($this->datas[$index])) {
			unset($this->datas[$index]);
			return true;
		}
		return false;
	}

	/**
	 * นำเข้าข้อมูลครั้งละหลายรายการ
	 *
	 * @param array $array ข้อมูลที่ต้องการนำเข้า
	 */
	public function assign($array)
	{
		if (isset($this->datas)) {
			$this->datas = array_merge($this->datas, $array);
		} else {
			$this->datas = $array;
		}
	}

	/**
	 * ลบข้อมูลทั้งหมด
	 */
	public function clear()
	{
		unset($this->datas);
	}

	/**
	 * เพิ่มรายการใหม่ต่อจากรายการที่ $index
	 *
	 * @param mixed $index
	 * @param mixed $item รายการใหม่
	 */
	public function insert($index, $item)
	{
		if (is_int($index) && $index == sizeof($this->datas)) {
			$this->datas[] = $item;
		} else {
			$temp = $this->datas;
			$this->datas = array();
			foreach ($temp AS $key => $value) {
				if ($key == $index) {
					$this->datas[$key] = $value;
					$this->datas[$index] = $item;
				} else {
					$this->datas[$key] = $value;
				}
			}
		}
	}

	/**
	 * เพิ่มรายการใหม่ก่อนรายการที่ $index
	 *
	 * @param mixed $index
	 * @param mixed $item รายการใหม่
	 */
	public function insertBefore($index, $item)
	{
		$temp = $this->datas;
		$this->datas = array();
		foreach ($temp AS $key => $value) {
			if ($key == $index) {
				$this->datas[$index] = $item;
				$this->datas[$key] = $value;
			} else {
				$this->datas[$key] = $value;
			}
		}
	}

	/**
	 * ค้นหาข้อมูลในแอเรย์
	 *
	 * @param mixed $value รายการค้นหา
	 * @return mixed คืนค่า key ของรายการที่พบ ถ้าไม่พบคืนค่า false
	 */
	public function indexOf($value)
	{
		return array_search($value, $this->datas);
	}

	/**
	 * โหลดแอเรย์จากไฟล์
	 *
	 * @param string $file ชื่อไฟล์ที่ต้องการโหลดรวม path
	 * @return \Core\ListItem
	 */
	public function loadFromFile($file)
	{
		if (is_file($file)) {
			$config = include $file;
			$this->source = $file;
			$this->assign($config);
		}
		return $this;
	}

	/**
	 * บันทึกเป็นไฟล์
	 *
	 * @return boolean true ถ้าสำเร็จ
	 */
	public function saveToFile()
	{
		if (!isset($this->source) || empty($this->datas)) {
			return false;
		} else {
			$datas = array();
			foreach ($this->datas as $key => $value) {
				if (is_array($value)) {
					$datas[] = (is_int($key) ? $key : "'".strtolower($key)."'")." => array(\n".$this->_arrayToStr(1, $value)."\n\t)";
				} else {
					$datas[] = (is_int($key) ? $key : "'".strtolower($key)."'").' => '.(is_int($value) ? $value : "'".addslashes($value)."'");
				}
			}
			$file = str_replace(ROOT_PATH, '', $this->source);
			$f = @fopen(ROOT_PATH.$file, 'w');
			if ($f === false) {
				return false;
			} else {
				fwrite($f, "<?php\n/* $file */\nreturn array (\n\t".implode(",\n\t", $datas)."\n);");
				fclose($f);
				return true;
			}
		}
	}

	/**
	 * array to string
	 *
	 * @param int $indent
	 * @param array $array
	 * @return string
	 */
	private function _arrayToStr($indent, $array)
	{
		$t = str_repeat("\t", $indent + 1);
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$datas[] = (is_int($key) ? $key : "'$key'")." => array(\n".$this->_arrayToStr($indent + 1, $value)."\n$t)";
			} else {
				$datas[] = (is_int($key) ? $key : "'$key'").' => '.(is_int($value) ? $value : "'".addslashes($value)."'");
			}
		}
		return $t.implode(",\n$t", $datas);
	}
}