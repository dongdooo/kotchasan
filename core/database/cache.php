<?php
/**
 * @filesource core/database/cache.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Core\Database;

/**
 * Database Cache Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Cache extends \KBase
{
	/**
	 * ไดเร็คทอรี่แคช
	 *
	 * @var string /root/to/dir/cache/
	 */
	private $cache_dir = null;
	/**
	 * อายุของแคช
	 *
	 * @var int
	 */
	private $cache_expire;
	/**
	 * กำหนดการโหลดข้อมูลจากแคชอัตโนมัติ
	 * 0 ไม่ใช้แคช
	 * 1 โหลดและบันทึกแคชอัตโนมัติ
	 * 2 โหลดข้อมูลจากแคชได้ แต่ไม่บันทึกแคชอัตโนมัติ
	 *
	 * @var int
	 */
	public $action = 0;
	/**
	 * ตัวแปรบอกว่ามีการอ่านข้อมูลจาก cache หรือไม่
	 * true อ่านข้อมูลมาจาก cache
	 * false อ่านข้อมูลจาก database
	 *
	 * @var boolean
	 */
	private $used_cache = false;
	/**
	 * ชื่อไฟล์แคช รวม path
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		//  folder cache
		$dir = ROOT_PATH.DATA_FOLDER.'cache/';
		if (\File::makeDirectory($dir)) {
			$this->cache_dir = $dir;
			$this->cache_expire = self::$cfg->get('cache_expire', 5);
			// clear old cache every day
			$d = is_file($dir.'index.php') ? file_get_contents($dir.'index.php') : 0;
			if ($d != date('d')) {
				$this->clear();
				$f = @fopen($dir.'index.php', 'wb');
				if ($f) {
					fwrite($f, date('d'));
					fclose($f);
				} else {
					$message = sprintf(\Language::get('The file or folder %s can not be created or is read-only, please create or adjust the chmod it to 775 or 777.'), 'cache/index.php');
					log_message('Warning', $message, __FILE__, __LINE__);
				}
			}
		} else {
			$message = sprintf(\Language::get('The file or folder %s can not be created or is read-only, please create or adjust the chmod it to 775 or 777.'), 'cache/');
			log_message('Warning', $message, __FILE__, __LINE__);
		}
	}

	/**
	 * อ่านข้อมูลจากแคช
	 *
	 * @param string $key
	 * @param array $values (options)
	 * @return boolean
	 */
	public function get($sql, $values)
	{
		if ($this->cache_dir && !empty($this->cache_expire) && !empty($this->action)) {
			$this->file = $this->cache_dir.md5(\Text::replaceAll($sql, $values)).'.php';
			if (file_exists($this->file) && time() - filemtime($this->file) < $this->cache_expire) {
				$this->used_cache = true;
				return unserialize(preg_replace('/^<\?php\sexit\?>(.*)$/isu', '\\1', file_get_contents($this->file)));
			}
		}
		$this->used_cache = false;
		return false;
	}

	/**
	 * บันทึก cache เมื่อบันทึกแล้วจะปิดการใช้งาน cache อัตโนมัติ
	 * จะใช้คำสั่งนี้เมื่อมีการเรียกใช้แคชด้วยคำสั่ง cacheOn(false) เท่านั้น
	 * query ครั้งต่อไปถ้าจะใช้ cache ต้อง เปิดการใช้งาน cache ก่อนทุกครั้ง
	 *
	 * @param array $datas ข้อมูลที่จะบันทึก
	 */
	public function save($datas)
	{
		if ($this->cache_dir && !empty($this->cache_expire) && !empty($this->action) && is_array($datas)) {
			$f = @fopen($this->file, 'wb');
			if ($f) {
				fwrite($f, '<?php exit?>'.serialize($datas));
				fclose($f);
			} else {
				log_message('Warning', str_replace('%s', 'cache/', \Kotchasan::trans('The directory %s cannot be written')), __FILE__, __LINE__);
			}
		}
		$this->action = 0;
	}

	/**
	 * เปิดการใช้งานแคช
	 * จะมีการตรวจสอบจากแคชก่อนการสอบถามข้อมูล
	 *
	 * @param boolean $auto_save (options) true (default) บันทึกผลลัพท์อัตโนมัติ, false ต้องบันทึกแคชเอง
	 */
	public function cacheOn($auto_save = true)
	{
		$this->action = $auto_save ? 1 : 2;
	}

	/**
	 * ตรวจสอบว่าข้อมูลมาจาก cache หรือไม่
	 *
	 * @return boolean
	 */
	public function usedCache()
	{
		return $this->used_cache;
	}

	/**
	 * เคลียร์แคช
	 *
	 * @return boolean true ถ้าลบเรียบร้อย, หรือ array ของรายการที่ไม่สำเร็จ
	 */
	public function clear()
	{
		$error = array();
		if ($this->cache_dir && !empty($this->cache_expire)) {
			$this->clearCache($this->cache_dir, $error);
		}
		return empty($error) ? true : $error;
	}

	/**
	 * ลบไฟล์ทั้งหมดในไดเร็คทอรี่ (cache)
	 *
	 * @param string $dir
	 * @param array $error เก็บรายชื่อไฟล์ที่ไม่สามารถลบได้
	 */
	private function clearCache($dir, &$error)
	{
		$f = @opendir($dir);
		if ($f) {
			while (false !== ($text = readdir($f))) {
				if ($text != "." && $text != ".." && $text != 'index.php') {
					if (is_dir($dir.$text)) {
						$this->clearCache($dir.$text.'/', $error);
					} elseif (!@unlink($dir.$text)) {
						$error[] = $dir.$text;
					}
				}
			}
			closedir($f);
		}
	}
}