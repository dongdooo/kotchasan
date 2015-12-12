<?php
/**
 * @filesource core/email.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Email function
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Email extends Model
{

	/**
	 * ฟังก์ชั่นส่งเมล์จากแม่แบบจดหมาย
	 *
	 * @param int $id ID ของจดหมายที่ต้องการส่ง
	 * @param string $module ชื่อโมดูลของจดหมายที่ต้องการส่ง
	 * @param array $datas ข้อมูลที่จะถูกแทนที่ลงในจดหมาย ในรูป 'ตัวแปร'=>'ข้อความ'
	 * @param string $to ที่อยู่อีเมล์ผู้รับ  คั่นแต่ละรายชื่อด้วย ,
	 * @return string สำเร็จคืนค่าว่าง ไม่สำเร็จ คืนค่าข้อความผิดพลาด
	 */
	public static function send($id, $module, $datas, $to)
	{
		$model = new static;
		$sql = "SELECT `from_email`,`copy_to`,`subject`,`detail` FROM `".$model->tableWithPrefix('emailtemplate')."`";
		$sql .= " WHERE `module`=:module AND `email_id`=:email_id AND `language` IN (:language,'th')";
		$sql .= " LIMIT 1";
		$where = array(
			':module' => $module,
			':email_id' => (int)$id,
			':language' => \Language::name()
		);
		$email = $model->db->customQuery($sql, true, $where, $model->cache);
		if (empty($email)) {
			return \Language::get('email template not found');
		} else {
			$email = $email[0];
			// ผู้ส่ง
			$from = empty($email['from_email']) ? self::$cfg->noreply_email : $email['from_email'];
			// ข้อความในอีเมล์
			$replace = array(
				'/%WEBTITLE%/' => strip_tags(self::$cfg->web_title),
				'/%WEBURL%/' => WEB_URL,
				'/%ADMINEMAIL%/' => $from,
				'/%TIME%/' => \Date::format()
			);
			$replace = \Arraytool::replace($replace, $datas);
			\Arraytool::extract($replace, $keys, $values);
			$msg = preg_replace($keys, $values, $email['detail']);
			$subject = preg_replace($keys, $values, $email['subject']);
			$to = explode(',', $to);
			if (!empty($email['copy_to'])) {
				$to[] = $email['copy_to'];
			}
			// ส่งอีเมล์
			return self::custom(implode(',', $to), $from, $subject, $msg);
		}
	}

	/**
	 * ฟังก์ชั่นส่งเมล์แบบกำหนดรายละเอียดเอง
	 *
	 * @param string $mailto ที่อยู่อีเมล์ผู้รับ  คั่นแต่ละรายชื่อด้วย ,
	 * @param string $replyto ที่อยู่อีเมล์สำหรับการตอบกลับจดหมาย ถ้าระบุเป็นค่าว่างจะใช้ที่อยู่อีเมล์จาก noreply_email
	 * @param string $subject หัวข้อจดหมาย
	 * @param string $msg รายละเอียดของจดหมาย (รองรับ HTML)
	 * @return string สำเร็จคืนค่าว่าง ไม่สำเร็จ คืนค่าข้อความผิดพลาด
	 */
	public static function custom($mailto, $replyto, $subject, $msg)
	{
		$charset = empty(self::$cfg->email_charset) ? 'utf-8' : strtolower(self::$cfg->email_charset);
		if (empty($replyto)) {
			$replyto = array(self::$cfg->noreply_email, strip_tags(self::$cfg->web_title));
		} elseif (preg_match('/^(.*)<(.*?)>$/', $replyto, $match)) {
			$replyto = array($match[1], (empty($match[2]) ? $match[1] : $match[2]));
		} else {
			$replyto = array($replyto, $replyto);
		}
		if ($charset !== 'utf-8') {
			$subject = iconv('utf-8', $charset, $subject);
			$msg = iconv('utf-8', $charset, $msg);
			$replyto[1] = iconv('utf-8', $charset, $replyto[1]);
		}
		$messages = array();
		if (empty(self::$cfg->email_use_phpMailer)) {
			// ส่งอีเมล์ด้วยฟังก์ชั่นของ PHP
			foreach (explode(',', $mailto) as $email) {
				$headers = "MIME-Version: 1.0\r\n";
				$headers .= "Content-type: text/html; charset=$charset\r\n";
				$headers .= "Content-Transfer-Encoding: quoted-printable\r\n";
				$headers .= "To: $email\r\n";
				$headers .= "From: $replyto[1]\r\n";
				$headers .= "Reply-to: $replyto[0]\r\n";
				$headers .= "X-Mailer: PHP mailer\r\n";
				if (!@mail($email, $subject, $msg, $headers)) {
					$messages = array(\Language::get('Unable to send mail'));
				}
			}
		} else {
			// ส่งอีเมล์ด้วย PHPMailer
			include_once ROOT_PATH.'core/class.phpmailer.php';
			// Create a new PHPMailer instance
			$mail = new PHPMailer;
			// Tell PHPMailer to use SMTP
			$mail->isSMTP();
			// charset
			$mail->CharSet = $charset;
			// use html
			$mail->IsHTML();
			$mail->SMTPAuth = empty(self::$cfg->email_SMTPAuth) ? false : true;
			if ($mail->SMTPAuth) {
				$mail->Username = self::$cfg->email_Username;
				$mail->Password = self::$cfg->email_Password;
				$mail->SMTPSecure = self::$cfg->email_SMTPSecure;
			}
			if (!empty(self::$cfg->email_Host)) {
				$mail->Host = self::$cfg->email_Host;
			}
			if (!empty(self::$cfg->email_Port)) {
				$mail->Port = self::$cfg->email_Port;
			}
			$mail->AddReplyTo($replyto[0], $replyto[1]);
			$mail->SetFrom(self::$cfg->noreply_email, strip_tags(self::$cfg->web_title));
			// subject
			$mail->Subject = $subject;
			// message
			$mail->MsgHTML(preg_replace('/(<br([\s\/]{0,})>)/', "$1\r\n", $msg));
			$mail->AltBody = strip_tags($msg);

			foreach (explode(',', $mailto) as $email) {
				if (preg_match('/^(.*)<(.*)>$/', $email, $match)) {
					if ($mail->ValidateAddress($match[1])) {
						$mail->AddAddress($match[1], $match[2]);
					}
				} else {
					if ($mail->ValidateAddress($email)) {
						$mail->AddAddress($email, $email);
					}
				}
				$err = $mail->send();
				if (!$err) {
					$messages[$mail->ErrorInfo] = $mail->ErrorInfo;
				}
				$mail->clearAddresses();
			}
		}
		return empty($messages) ? '' : implode("\n", $messages);
	}
}