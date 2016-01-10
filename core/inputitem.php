<?php
/*
 * @filesource core/inputitem.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * Input Object
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class InputItem
{
	/**
	 * ตัวแปรเก็บค่าของ Object
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Class Constructer
	 *
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * สร้าง Object
	 *
	 * @param mixed $value
	 * @return \static
	 */
	public static function create($value)
	{
		return new static($value);
	}

	/**
	 * คืนค่าเป็น array
	 *
	 * @return array
	 */
	public function toArray()
	{
		return (array)$this->value;
	}

	/**
	 * คืนค่าเป็น boolean
	 *
	 * @return bool
	 */
	public function toBoolean()
	{
		return empty($this->value) ? 0 : 1;
	}

	/**
	 * คืนค่าเป็น double
	 *
	 * @return double
	 */
	public function toDouble()
	{
		return (double)$this->value;
	}

	/**
	 * คืนค่าเป็น float
	 *
	 * @return float
	 */
	public function toFloat()
	{
		return (float)$this->value;
	}

	/**
	 * คืนค่าเป็น integer
	 *
	 * @return int
	 */
	public function toInt()
	{
		return (int)$this->value;
	}

	/**
	 * คืนค่า Object เป็น String
	 *
	 * @return string
	 */
	public function toString()
	{
		return (string)$this->value;
	}

	/**
	 * คืนค่าเป็น Object
	 *
	 * @return object
	 */
	public function toObject()
	{
		return (object)$this->value;
	}

	/**
	 * ลบ PHP tag และแปลง \ เป็น $#92; ใช้รับข้อมูลจาก editor
	 * เช่นเนื้อหาของบทความ
	 *
	 * @return string
	 */
	public function detail()
	{
		return preg_replace(array('/<\?(.*?)\?>/su', '/\\\/'), array('', '&#92;'), $this->value);
	}

	/**
	 * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities และลบช่องว่างหัวท้าย
	 * ใช้แปลงค่าที่รับจาก input ที่ไม่ยอมรับ tag
	 *
	 * @return string
	 */
	public function text()
	{
		return trim($this->htmlspecialchars());
	}

	/**
	 * แปลง < > \ เป็น HTML entities และแปลง \n เป็น <br>
	 * ใช้รับข้อมูลที่มาจาก textarea
	 *
	 * @return string
	 */
	public function textarea()
	{
		return trim(preg_replace(array('/</u', '/>/u', '/\\\/u'), array('&lt;', '&gt;', '&#92;'), nl2br($this->value)));
	}

	/**
	 * ลบ tag, BBCode ออก ให้เหลือแต่ข้อความล้วน
	 * ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 * ใช้เป็น description
	 *
	 *
	 * @param int $len ความยาวของ description 0 หมายถึงคืนค่าทั้งหมด
	 * @return string
	 */
	public function description($len = 0)
	{
		$patt = array(
			/* style */
			'@<style[^>]*?>.*?</style>@siu' => '',
			/* comment */
			'@<![\s\S]*?--[ \t\n\r]*>@u' => '',
			/* tag */
			'@<[\/\!]*?[^<>]*?>@iu' => '',
			/* keywords */
			'/{(WIDGET|LNG)_[a-zA-Z0-9_]+}/su' => '',
			/* BBCode (code) */
			'/(\[code(.+)?\]|\[\/code\]|\[ex(.+)?\])/ui' => '',
			/* BBCode ทั่วไป [b],[i] */
			'/\[([a-z]+)([\s=].*)?\](.*?)\[\/\\1\]/ui' => '\\3',
			/* ตัวอักษรที่ไม่ต้องการ */
			'/(&amp;|&quot;|&nbsp;|[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]){1,}/isu' => ' '
		);
		$text = trim(preg_replace(array_keys($patt), array_values($patt), $this->value));
		return $this->cut($text, $len);
	}

	/**
	 * ลบ tags และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 * ใช้เป็น tags หรือ keywords
	 *
	 * @param int $len ความยาวของ keywords 0 หมายถึงคืนค่าทั้งหมด
	 * @return string
	 */
	public function keywords($len = 0)
	{
		$text = trim(preg_replace('/[_\(\)\-\+\r\n\s\"\'<>\.\/\\\?&\{\}]{1,}/isu', ' ', strip_tags($this->value)));
		return $this->cut($text, $len);
	}

	/**
	 * ฟังก์ชั่นแปลง ' เป็น &#39;
	 *
	 * @return string
	 */
	public function quote()
	{
		return str_replace("'", '&#39;', $this->value);
	}

	/**
	 * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 * เช่นหัวข้อของบทความ
	 *
	 * @return string
	 */
	public function topic()
	{
		return trim(preg_replace('/[\r\n\t\s]+/', ' ', $this->htmlspecialchars()));
	}

	/**
	 * แปลง tag และลบช่องว่างหัวท้าย ไม่แปลง &amp;
	 * สำหรับ URL หรือ email
	 *
	 * @return string
	 */
	public function url()
	{
		return trim($this->htmlspecialchars(false));
	}

	/**
	 * ตัดสตริงค์
	 *
	 * @param string $str
	 * @param int $len ความยาวที่ต้องการ
	 * @return string
	 */
	private function cut($str, $len)
	{
		if (!empty($len) && !empty($str)) {
			$str = mb_substr($str, 0, (int)$len);
		}
		return $str;
	}

	/**
	 * แปลง & " ' < > \ เป็น HTML entities ใช้แทน htmlspecialchars() ของ PHP
	 *
	 * @param bool $double_encode true (default) แปลง รหัส HTML เช่น &amp; เป็น &amp;amp;, false ไม่แปลง
	 * @return \static
	 */
	private function htmlspecialchars($double_encode = true)
	{
		$str = preg_replace(array('/&/', '/"/', "/'/", '/</', '/>/', '/\\\/'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;', '&#92;'), $this->value);
		if (!$double_encode) {
			$str = preg_replace('/&(amp;([#a-z0-9]+));/', '&\\2;', $str);
		}
		return $str;
	}
}