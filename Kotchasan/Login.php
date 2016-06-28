<?php
/*
 * @filesource Kotchasan/Login.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\LoginInterface;
use \Kotchasan\Password;
use \Kotchasan\Language;
use \Kotchasan\Http\Request;
use \Kotchasan\Text;

/**
 * คลาสสำหรับตรวจสอบการ Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\KBase implements LoginInterface
{
	/**
	 * ข้อความจาก Login Class
	 *
	 * @var string
	 */
	public static $login_message;
	/**
	 * ชื่อ Input ที่ต้องการให้ active
	 * login_username หรือ login_password
	 *
	 * @var string
	 */
	public static $login_input;
	/**
	 * ข้อความใน Input login_username
	 *
	 * @var string
	 */
	public static $text_username;
	/**
	 * ข้อความใน Input login_password
	 *
	 * @var string
	 */
	public static $text_password;
	/**
	 * ตัวแปรบอกว่ามาจากการ submit
	 * true มาจากการ submit
	 * false default
	 *
	 * @var bool
	 */
	private $from_submit = false;

	/**
	 * ตรวจสอบการ login เมื่อมีการเรียกใช้ class new Login
	 * action=logout ออกจากระบบ
	 * มาจากการ submit ตรวจสอบการ login
	 * ถ้าไม่มีทั้งสองส่วนด้านบน จะตรวจสอบการ login จาก session และ cookie ตามลำดับ
	 *
	 * @return \static
	 */
	public static function create()
	{
		// create class
		$login = new static;
		// true ถ้ามาจากการ submit
		$login->from_submit = self::$request->post('login_password')->exists();
		// การเข้ารหัส
		$pw = new Password(self::$cfg->password_key);
		// ชื่อฟิลด์สำหรับการรับค่าเป็นรายการแรกของ login_fields
		$field_name = reset(self::$cfg->login_fields);
		// อ่านข้อมูลจากฟอร์ม login ฟิลด์ login_username
		self::$text_username = self::$request->post('login_username', null)->toString();
		if (self::$text_username === null) {
			if (isset($_SESSION['login']) && isset($_SESSION['login'][$field_name])) {
				// from session
				self::$text_username = $_SESSION['login'][$field_name];
			} else {
				// from cookie
				$datas = self::$request->getCookieParams();
				self::$text_username = isset($datas['login_'.$field_name]) ? $pw->decode($datas['login_'.$field_name]) : null;
			}
		} else {
			$login->from_submit = true;
		}
		self::$text_username = Text::username(self::$text_username);
		// ค่าที่ส่งมา
		self::$text_password = $login->get('password', $pw);
		$login_remember = $login->get('remember', $pw) == 1 ? 1 : 0;
		// ตรวจสอบการ login
		if (self::$request->get('action')->toString() === 'logout' && !$login->from_submit) {
			// logout ลบ session และ cookie
			unset($_SESSION['login']);
			$time = time();
			setCookie('login_'.$field_name, '', $time, '/');
			setCookie('login_password', '', $time, '/');
			self::$login_message = Language::get('Logout successful');
		} elseif (self::$request->post('action')->toString() === 'forgot') {
			// ตรวจสอบอีเมล์สำหรับการขอรหัสผ่านใหม่
			return $login->forgot(self::$request);
		} else {
			// ตรวจสอบค่าที่ส่งมา
			if (self::$text_username == '') {
				if ($login->from_submit) {
					self::$login_message = Language::get('Please fill out this form');
					self::$login_input = 'login_username';
				}
			} elseif (self::$text_password == '') {
				if ($login->from_submit) {
					self::$login_message = Language::get('Please fill out this form');
					self::$login_input = 'login_password';
				}
			} else {
				// ตรวจสอบการ login กับฐานข้อมูล
				$login_result = $login->checkLogin(self::$text_username, self::$text_password);
				if (is_string($login_result)) {
					// ข้อความผิดพลาด
					self::$login_input = self::$login_input == 'password' ? 'login_password' : 'login_username';
					self::$login_message = Language::get($login_result);
					// logout ลบ session และ cookie
					unset($_SESSION['login']);
					$time = time();
					setCookie('login_'.$field_name, '', $time, '/');
					setCookie('login_password', '', $time, '/');
				} else {
					// save login session
					$login_result['password'] = self::$text_password;
					$_SESSION['login'] = $login_result;
					// save login cookie
					$time = time() + 2592000;
					if ($login_remember == 1) {
						setcookie('login_'.$field_name, $pw->encode(self::$text_username), $time, '/');
						setcookie('login_password', $pw->encode(self::$text_password), $time, '/');
						setcookie('login_remember', $login_remember, $time, '/');
					}
					setcookie('login_id', $login_result['id'], $time, '/');
				}
			}
			return $login;
		}
	}

	/**
	 * อ่านข้อมูลจาก POST, SESSION และ COOKIE ตามลำดับ
	 * เจออันไหนก่อนใช้อันนั้น
	 *
	 * @param string $name
	 * @param Password $pw
	 * @return string|null คืนค่าข้อความ ไม่พบคืนค่า null
	 */
	protected function get($name, Password $pw)
	{
		$datas = self::$request->getParsedBody();
		if (isset($datas['login_'.$name])) {
			$this->from_submit = true;
			return (string)$datas['login_'.$name];
		} elseif (isset($_SESSION['login']) && isset($_SESSION['login'][$name])) {
			return (string)$_SESSION['login'][$name];
		}
		$datas = self::$request->getCookieParams();
		return isset($datas['login_'.$name]) ? $pw->decode($datas['login_'.$name]) : null;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบการ login
	 *
	 * @param string $username
	 * @param string $password
	 * @return string|array เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด
	 */
	public function checkLogin($username, $password)
	{
		$field_name = reset(self::$cfg->login_fields);
		if ($username == self::$cfg->get($field_name) && $password == self::$cfg->get('password')) {
			return array(
				'id' => 1,
				$field_name => $username,
				'password' => $password,
				'status' => 1
			);
		}
		return 'not a registered user';
	}

	/**
	 * ฟังก์ชั่นส่งอีเมล์ลืมรหัสผ่าน
	 */
	public function forgot(Request $request)
	{
		return $this;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบการเข้าระบบ
	 *
	 * @return array|null คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นสมาชิกและเข้าระบบแล้ว ไม่ใช่คืนค่า null
	 */
	public static function isMember()
	{
		return isset($_SESSION['login']) ? $_SESSION['login'] : null;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบสถานะแอดมิน
	 *
	 * @return array|null คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นผู้ดูแลระบบและเข้าระบบแล้ว ไม่ใช่คืนค่า null
	 */
	public static function isAdmin()
	{
		$login = self::isMember();
		return isset($login['status']) && $login['status'] == 1 ? $login : null;
	}
}