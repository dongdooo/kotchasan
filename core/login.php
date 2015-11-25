<?php
/**
 * @filesource core/login.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * คลาสสำหรับตรวจสอบการ Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Model
{
	/**
	 * ข้อความจาก Login Class
	 *
	 * @var string
	 */
	public static $login_message;
	/**
	 * ชื่อ Input ที่ต้องการให้ active
	 * login_email หรือ login_password
	 *
	 * @var string
	 */
	public static $login_input;
	/**
	 * ข้อความใน Input login_email
	 *
	 * @var string
	 */
	public static $text_email;
	/**
	 * ข้อความใน Input login_password
	 *
	 * @var string
	 */
	public static $text_password;

	/**
	 * ตรวจสอบการ login เมื่อมีการเรียกใช้ class new Login
	 * action=logout ออกจากระบบ
	 * มาจากการ submit ตรวจสอบการ login
	 * ถ้าไม่มีทั้งสองส่วนด้านบน จะตรวจสอบการ login จาก session และ cookie ตามลำดับ
	 *
	 * @return \Login
	 */
	public static function create()
	{
		$obj = new static;
		// ค่าที่ส่งมา
		$save = Input::filter($_POST);
		// ตรวจสอบการ login
		if (Input::get($_GET, 'error') === 'EMAIL_EXISIS') {
			// facebook login error มี email อยู่แล้ว
			self::$login_message = Language::get('This email is already registered');
		} elseif (empty($save) && Input::get($_GET, 'action') === 'logout') {
			// logout ลบ session และ cookie
			unset($_SESSION['login']);
			$time = time();
			setCookie('login_email', '', $time, '/');
			setCookie('login_password', '', $time, '/');
			self::$login_message = Language::get('Logout successful');
		} elseif (isset($save['action']) && $save['action'] === 'forgot') {
			// ลืมรหัสผ่าน
			return $obj->forgot();
		} else {
			// ตรวจสอบค่าที่ส่งมา
			if (isset($save['remember'])) {
				$login_remember = $save['remember'];
			} elseif (isset($_COOKIE['login_remember'])) {
				$login_remember = $_COOKIE['login_remember'] == 1 ? 1 : 0;
			} else {
				$login_remember = 0;
			}
			foreach (array('login_email', 'login_password') as $name) {
				$key = str_replace('login_', '', $name);
				if (!isset($save[$key])) {
					foreach (array($_SESSION, $_COOKIE) as $var) {
						if (isset($var[$name])) {
							if ($var == $_COOKIE) {
								$$name = Password::decode($var[$name]);
							} else {
								$$name = trim($var[$name]);
							}
							break;
						}
					}
				} else {
					$$name = $save[$key];
				}
			}
		}
		if (isset($login_email) && isset($login_password)) {
			self::$text_email = $login_email;
			self::$text_password = $login_password;
			// ตรวจสอบการกรอก
			if (empty($login_email)) {
				self::$login_message = Language::get('Please fill out').' '.Language::get('Email');
				self::$login_input = 'text_email';
			} elseif (empty($login_password)) {
				self::$login_message = Language::get('Please fill out').' '.Language::get('Password');
				self::$login_input = 'text_password';
			} else {
				// ตรวจสอบการ login กับฐานข้อมูล
				$login_result = $obj->checkLogin($login_email, $login_password);
				if (is_string($login_result)) {
					// ข้อความผิดพลาด
					self::$login_input = $login_result == 'Incorrect password' ? 'text_password' : 'text_email';
					self::$login_message = Language::get($login_result);
				} else {
					// save login session
					foreach ($login_result as $key => $value) {
						$_SESSION['login'] [$key] = $value;
					}
					$_SESSION['login']['password'] = $login_password;
					// save login cookie
					$time = time() + 2592000;
					if ($login_remember == 1) {
						setcookie('login_email', Password::encode($login_result->email), $time, '/');
						setcookie('login_password', Password::encode($login_password), $time, '/');
						setcookie('login_remember', $login_remember, $time, '/');
					}
					setcookie('login_id', $login_result->id, $time, '/');
				}
			}
		}
		// คืนค่า \Login
		return $obj;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบการ login
	 *
	 * @return array
	 */
	private function checkLogin($user, $password)
	{
		// current session
		$session_id = session_id();
		if (!empty(\Kotchasan::$config->demo_mode) && $user == 'demo' && $password == 'demo') {
			// login เป็น demo
			$login_result = array(
				'id' => 0,
				'email' => 'demo',
				'password' => 'demo',
				'displayname' => 'demo',
				'status' => 0,
				'admin_access' => 1,
				'activatecode' => '',
				'ban_date' => 0,
				'session_id' => $session_id,
				'visited' => 0,
				'fb' => 0
			);
			return (object)$login_result;
		} else {
			// ตรวจสอบการ login กับฐานข้อมูล
			$login_result = false;
			$qs = array();
			$datas = array();
			foreach (\Kotchasan::$config->login_fields AS $field) {
				$qs[] = "`$field`=:$field";
				$datas[":$field"] = $user;
			}
			$sql = "SELECT * FROM `".$this->tableWithPrefix('user')."` WHERE ".implode(' OR ', $qs)." ORDER BY `status` DESC";
			foreach ($this->db->customQuery($sql, true, $datas) AS $item) {
				if ($item['password'] == md5($password.$item['email'])) {
					$login_result = $item;
					break;
				}
			}
			if (!$login_result) {
				// user หรือ password ไม่ถูกต้อง
				return isset($item) ? 'Incorrect password' : 'not a registered user';
			} elseif (!empty($login_result['activatecode'])) {
				// ยังไม่ได้ activate
				return 'No confirmation email, please check your e-mail';
			} elseif (!empty($login_result['ban'])) {
				// ติดแบน
				return 'Members were suspended';
			} else {
				// ตรวจสอบการ login มากกว่า 1 ip
				$ip = Input::ip();
				if (\Kotchasan::$config->member_only_ip && !empty($ip)) {
					$sql = "SELECT * FROM `".$this->tableWithPrefix('useronline')."`";
					$sql .= " WHERE `member_id`='$login_result[id]' AND `ip`!='$ip' AND `ip`!=''";
					$sql .= " ORDER BY `time` DESC LIMIT 1";
					$online = $this->db->customQuery($sql, true);
					if (sizeof($online) == 1 && \Kotchasan::$mktime - $online[0]['time'] < \Kotchasan::$settings->count_gap) {
						// login ต่าง ip กัน
						return 'Members of this system already';
					}
				}
				$userupdate = false;
				// อัปเดทการเยี่ยมชม
				if ($session_id != $login_result['session_id']) {
					$login_result['visited'] ++;
					$userupdate = true;
				}
				// บันทึกลง db
				if ($userupdate) {
					$this->db->update($this->tableWithPrefix('user'), array('id', $login_result['id']), array('session_id' => $session_id, 'visited' => $login_result['visited'], 'lastvisited' => \Kotchasan::$mktime, 'ip' => $ip));
				}
				return (object)$login_result;
			}
		}
	}

	/**
	 * ฟังก์ชั่นส่งอีเมล์ลืมรหัสผ่าน
	 */
	public function forgot()
	{
		// ค่าที่ส่งมา
		$save = \Input::filter($_POST);
		// query user
		$sql = "SELECT `id`,`email`,`activatecode` FROM `".$this->tableWithPrefix('user')."` WHERE (`email`=:email OR `phone1`=:email) AND `fb`='0' LIMIT 1";
		$where = array(
			':email' => $save['email']
		);
		$user = $this->db->customQuery($sql, true, $where);
		if (empty($user)) {
			self::$login_message = Language::get('not a registered user');
			self::$login_input = 'email_email';
			self::$text_email = $save['email'];
		} else {
			$user = $user[0];
			// สุ่มรหัสผ่านใหม่
			$password = \String::rndname(6);
			// ส่งเมล์แจ้งสมาชิก
			$replace = array();
			$replace['/%PASSWORD%/'] = $password;
			$replace['/%EMAIL%/'] = $user['email'];
			if (empty($user['activatecode'])) {
				// send mail
				$err = \Email::send(3, 'member', $replace, $user['email']);
			} else {
				$replace['/%ID%/'] = $user['activatecode'];
				// send mail
				$err = \Email::send(1, 'member', $replace, $user['email']);
			}
			if (empty($err)) {
				// อัปเดทรหัสผ่านใหม่
				$save_password = md5($password.$user['email']);
				$this->db->update($this->tableWithPrefix('user'), array('id', $user['id']), array('password' => $save_password));
				// สำเร็จ
				self::$login_message = Language::get('Your message was sent successfully');
				// ไปหน้า login
				$_REQUEST['action'] = 'login';
			} else {
				// ไม่สามารถส่งอีเมล์ได้
				self::$login_message = $err;
				self::$login_input = 'email_email';
			}
			self::$text_email = $user['email'];
		}
		return $this;
	}

	/**
	 * ฟังก์ชั่นตรวจสอบการ Login
	 *
	 * @return boolean true เป็นสมาชิก, false ยังไม่ได้ login
	 */
	public static function isMember()
	{
		return isset($_SESSION['login']);
	}

	/**
	 * ฟังก์ชั่นตรวจสอบสถานะแอดมิน
	 *
	 * @return boolean true เป็นแอดมิน, false ไม่ใช่แอดมินหรือยังไม่ได้ login
	 */
	public static function isAdmin()
	{
		return isset($_SESSION['login']) && $_SESSION['login']['id'] > 0 && $_SESSION['login']['status'] == 1;
	}

	/**
	 * ตรวจสอบความสามารถในการเข้าระบบแอดมิน
	 *
	 * @return boolean true สามารถเข้าแอดมินได้, false ไม่สามารถเข้าหน้าแอดมินได้
	 */
	public static function adminAccess()
	{
		return isset($_SESSION['login']['admin_access']) && $_SESSION['login']['admin_access'] == 1;
	}
}