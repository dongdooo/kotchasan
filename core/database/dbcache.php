<?php
/*
 * @filesource core/database/cache.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Database;

use Core\Cache\FileCache as Cache;
use \Core\Cache\CacheItem as Item;

/**
 * Database Cache Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class DbCache
{
	/**
	 * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
	 */
	private static $instance = null;
	/**
	 * คลาส Cache
	 *
	 * @var Cache
	 */
	private $db_cache;
	/**
	 * กำหนดการโหลดข้อมูลจากแคชอัตโนมัติ
	 * 0 ไม่ใช้แคช
	 * 1 โหลดและบันทึกแคชอัตโนมัติ
	 * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ
	 *
	 * @var int
	 */
	private $action = 0;
	/**
	 * ข้อมูลแคช
	 *
	 * @var Item
	 */
	private $item;

	/**
	 * Class constructor
	 */
	private function __construct()
	{
		$this->db_cache = new Cache();
	}

	/**
	 * Create Class
	 *
	 * @return \static
	 */
	public static function create()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * อ่านข้อมูลจากแคช
	 *
	 * @param string $key
	 * @param array $values (options)
	 * @return mixed คืนค่าข้อมูลหรือ false ถ้าไม่มีแคช
	 */
	public function get($sql, $values)
	{
		$this->item = $this->db_cache->getItem(\KString::replace($sql, $values));
		return $this->item->isHit() ? $this->item->get() : false;
	}

	/**
	 * บันทึก cache เมื่อบันทึกแล้วจะปิดการใช้งาน cache อัตโนมัติ
	 * จะใช้คำสั่งนี้เมื่อมีการเรียกใช้แคชด้วยคำสั่ง cacheOn(false) เท่านั้น
	 * query ครั้งต่อไปถ้าจะใช้ cache ต้อง เปิดการใช้งาน cache ก่อนทุกครั้ง
	 *
	 * @param array $datas ข้อมูลที่จะบันทึก
	 * @return bool สำเร็จคืนค่า true ไม่สำเร็จคืนค่า false
	 */
	public function save($datas)
	{
		$this->action = 0;
		if ($this->item instanceof Item) {
			$this->item->set($datas);
			return $this->db_cache->save($this->item);
		}
		return false;
	}

	/**
	 * เปิดการใช้งานแคช
	 * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล
	 *
	 * @param bool $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
	 */
	public function cacheOn($auto_save = true)
	{
		$this->action = $auto_save ? 1 : 2;
	}

	/**
	 * ตรวจสอบว่าข้อมูลมาจาก cache หรือไม่
	 *
	 * @return bool
	 */
	public function usedCache()
	{
		return $this->item->isHit();
	}

	/**
	 * เคลียร์แคช
	 *
	 * @return bool true ถ้าลบเรียบร้อย, หรือ array ของรายการที่ไม่สำเร็จ
	 */
	public function clear()
	{
		$this->db_cache->clear();
	}

	/**
	 * อ่านสถานะของแคช
	 * 0 ไม่ใช้แคช
	 * 1 โหลดและบันทึกแคชอัตโนมัติ
	 * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ
	 *
	 * @return int
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * กำหนดสถานะของแคช
	 * 0 ไม่ใช้แคช
	 * 1 โหลดและบันทึกแคชอัตโนมัติ
	 * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ
	 *
	 * @param type $value
	 * @return \static
	 */
	public function setAction($value)
	{
		$this->action = $value;
		return $this;
	}
}