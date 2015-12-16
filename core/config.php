<?php
/**
 * @filesource core/config.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Class สำหรับการโหลด config
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
final class Config
{
	/**
	 * @var Singleton สำหรับเรียกใช้ class นี้เพียงครั้งเดียวเท่านั้น
	 */
	private static $instance = null;
	/**
	 * ตั้งค่าเขตเวลาของ Server ให้ตรงกันกับเวลาท้องถิ่น เช่น Asia/Bankok
	 *
	 * @var string
	 */
	public $timezone;
	/**
	 * ภาษาที่รองรับ
	 *
	 * @var array
	 */
	public $languages;
	/**
	 * template ที่กำลังใช้งานอยู่ (ชื่อโฟลเดอร์)
	 *
	 * @var string
	 */
	public $skin;
	/**
	 * รายชื่อฟิลด์จากตารางสมาชิก สำหรับตรวจสอบการ login
	 *
	 * @var array
	 */
	public $login_fields;
	/**
	 * ตั้งค่า การ login ต่อ 1 IP
	 * true ไม่สามารถ login พร้อมกันหลายบัญชีต่อ 1 เครื่องได้
	 *
	 * @var boolean default false
	 */
	public $member_only_ip;
	/**
	 * ถ้ากำหนดเป็น true บัญชี demo จะสามารถเข้าระบบแอดมินได้
	 *
	 * @var boolean default false
	 */
	public $demo_mode;
	/**
	 * ชื่อเว็บไซต์
	 *
	 * @var string
	 */
	public $web_title;
	/**
	 * คำอธิบายเกี่ยวกับเว็บไซต์
	 *
	 * @var string
	 */
	public $web_description;
	/**
	 * กำหนดรูปแบบของ URL ที่สร้างจากระบบ
	 * ตามที่กำหนดโดย \Settings->urls
	 *
	 * @var int
	 */
	public $module_url;
	/**
	 * สถานะสมาชิก
	 * 0 สมาชิกทั่วไป
	 * 1 ผู้ดูแลระบบ
	 *
	 * @var array
	 */
	public $member_status;
	/**
	 * สีของสมาชิกตามสถานะ
	 *
	 * @var array
	 */
	public $color_status;
	/**
	 * กำหนดอายุของแคช
	 *
	 * @var int
	 */
	public $cache_expire;
	/**
	 * ความกว้างสูงสุดของรูปประจำตัวสมาชิก
	 *
	 * @var int
	 */
	public $user_icon_w;
	/**
	 * ความสูงสูงสุดของรูปประจำตัวสมาชิก
	 *
	 * @var int
	 */
	public $user_icon_h;
	/**
	 * ชนิดของรูปถาพที่สามารถอัปโหลดเป็นรูปประจำตัวสมาชิก ได้
	 *
	 * @var array
	 */
	public $user_icon_typies;
	/**
	 * สมาชิกใหม่ต้องยืนยันอีเมล์
	 *
	 * @var boolean
	 */
	public $user_activate;
	/**
	 * ทีอยู่อีเมล์ใช้เป็นผู้ส่งจดหมาย สำหรับจดหมายที่ไม่ต้องการตอบกลับ เช่น no-reply@domain.tld
	 *
	 * @var string
	 */
	public $noreply_email;
	/**
	 * ระบุรหัสภาษาของอีเมล์ที่ส่ง เช่น tis-620
	 *
	 * @var string
	 */
	public $email_charset;
	/**
	 * เลือกโปรแกรมที่ใช้ในการส่งอีเมล์เป็น PHPMailer
	 *
	 * @var boolean
	 */
	public $email_use_phpMailer;
	/**
	 * ชื่อของเมล์เซิร์ฟเวอร์ เช่น localhost หรือ smtp.gmail.com (ต้องการเปลี่ยนค่ากำหนดของอีเมล์ทั้งหมดเป็นค่าเริ่มต้น ให้ลบข้อความในช่องนี้ออกทั้งหมด)
	 *
	 * @var string
	 */
	public $email_Host;
	/**
	 * หมายเลขพอร์ตของเมล์เซิร์ฟเวอร์ (ค่าปกติคือ 25, สำหรับ gmail ใช้ 465, 587 สำหรับ DirectAdmin)
	 *
	 * @var int
	 */
	public $email_Port;
	/**
	 * กำหนดวิธีการตรวจสอบผู้ใช้สำหรับเมล์เซิร์ฟเวอร์
	 * ถ้ากำหนดเป็น true จะต้องระบุUser+Pasword ของ mailserver ด้วย
	 *
	 * @var boolean
	 */
	public $email_SMTPAuth;
	/**
	 * โปรโตคอลการเข้ารหัส SSL สำหรับการส่งอีเมล์ เช่น ssl
	 *
	 * @var string
	 */
	public $email_SMTPSecure;
	/**
	 * ชื่อผู้ใช้ mailserver
	 *
	 * @var string
	 */
	public $email_Username;
	/**
	 * รหัสผ่าน mailserver
	 *
	 * @var string
	 */
	public $email_Password;
	/**
	 * คีย์สำหรับการเข้ารหัสข้อความ
	 *
	 * @var string
	 */
	public $password_key;

	/**
	 * inint class
	 *
	 * @return \Config
	 */
	private function __construct()
	{
		$this->timezone = 'UTC';
		$this->languages = array('th');
		$this->skin = 'default';
		$this->login_fields = array('email', 'phone1');
		$this->member_only_ip = false;
		$this->member_status = array('Member', 'Administrator');
		$this->color_status = array('#336600', '#FF0000');
		$this->demo_mode = false;
		$this->web_title = 'Kotchasan PHP Framework';
		$this->web_description = 'PHP Framework พัฒนาโดยคนไทย';
		$this->cache_expire = 5;
		$this->user_icon_h = 50;
		$this->user_icon_w = 50;
		$this->user_icon_typies = array('jpg', 'gif', 'png');
		$this->user_activate = true;
		$this->noreply_email = 'no-replay@'.$_SERVER['HTTP_HOST'];
		$this->email_charset = 'tis-620';
		$this->email_use_phpMailer = false;
		$this->email_Host = 'localhost';
		$this->email_Port = 25;
		$this->email_SMTPAuth = false;
		$this->email_SMTPSecure = '';
		$this->password_key = '1234567890';
		$this->module_url = 1;
		if (is_file(APP_ROOT.'settings/config.php')) {
			$config = include (APP_ROOT.'settings/config.php');
			foreach ($config as $key => $value) {
				$this->$key = $value;
			}
		}
		if (APP_ROOT != APP_PATH && is_file(APP_PATH.'settings/config.php')) {
			$config = include (APP_PATH.'settings/config.php');
			foreach ($config as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	/**
	 * เรียกใช้งาน Class แบบสามารถเรียกได้ครั้งเดียวเท่านั้น
	 *
	 * @return \static
	 */
	public static function & create()
	{
		if (null === self::$instance) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * อ่านค่าตัวแปร และ แปลงผลลัพท์ตามชนิดของตัวแปรตามที่กำหนดโดย $default เช่น
	 * $default = 0 หรือ เลขจำนวนเต็ม ผลลัพท์จะถูกแปลงเป็น int
	 * $default = 0.0 หรือตัวเลขมีจุดทศนิยม จำนวนเงิน ผลลัพท์จะถูกแปลงเป็น double
	 * $default = true หรือ false ผลลัพท์จะถูกแปลงเป็น true หรือ false เท่านั้น
	 *
	 * @param string $key ชื่อตัวแปร
	 * @param mixed $default (option) ค่าเริ่มต้นหากไม่พบตัวแปร
	 * @return mixed ค่าตัวแปร $key ถ้าไม่พบคืนค่า $default
	 */
	public function get($key, $default = '')
	{
		if (isset($this->{$key})) {
			$result = $this->{$key};
			if (is_float($default)) {
				// จำนวนเงิน เช่น 0.0
				$result = (double)$result;
			} elseif (is_int($default)) {
				// เลขจำนวนเต็ม เช่น 0
				$result = (int)$result;
			} elseif (is_bool($default)) {
				// true, false
				$result = (boolean)$result;
			}
		} else {
			$result = $default;
		}
		return $result;
	}

	/**
	 * โหลดไฟล์ config
	 *
	 * @param string $file ไฟล์ config (fullpath)
	 * @return Object
	 */
	public static function load($file)
	{
		$config = array();
		if (is_file($file)) {
			$config = include ($file);
		}
		return (object)$config;
	}

	/**
	 * บันทึกไฟล์ config ของโปรเจ็ค
	 *
	 * @param array $config
	 * @param string $file ไฟล์ config (fullpath)
	 * @return boolean คืนค่า true ถ้าสำเร็จ
	 */
	public static function save($config, $file)
	{
		$list = array();
		foreach ($config as $key => $value) {
			if (is_array($value)) {
				$save = array();
				foreach ($value as $k => $v) {
					$data = '';
					if (preg_match('/^[0-9]+$/', $k)) {
						$data = $k.' => ';
					} else {
						$data = '\''.$k.'\' => ';
					}
					if (is_string($v)) {
						$data .= '\''.$v.'\'';
					} else {
						$data .= $v;
					}
					$save[] = $data;
				}
				$list[] = '\''.$key."' => array(\n    ".implode(",\n    ", $save)."\n  )";
			} elseif (is_string($value)) {
				$list[] = '\''.$key.'\' => \''.($value).'\'';
			} else {
				$list[] = '\''.$key.'\' => '.$value;
			}
		}
		$f = @fopen($file, 'wb');
		if ($f !== false) {
			fwrite($f, "<"."?php\n/* config.php */\nreturn array(\n  ".implode(",\n  ", $list)."\n);");
			fclose($f);
			return true;
		} else {
			return false;
		}
	}
}