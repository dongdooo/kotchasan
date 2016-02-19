<?php
/*
 * @filesource Kotchasan/InputItem.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

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
	 * @param mixed $value default null
	 */
	public function __construct($value = null)
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
		return is_array($this->value) ? $this->value : array($this->value);
	}

	/**
	 * คืนค่าเป็น boolean
	 *
	 * @return bool
	 * @assert create(true)->toBoolean() [==] 1
	 * @assert create(false)->toBoolean() [==] 0
	 * @assert create(1)->toBoolean() [==] 1
	 * @assert create(0)->toBoolean() [==] 0
	 * @assert create(null)->toBoolean() [==] 0
	 */
	public function toBoolean()
	{
		return empty($this->value) ? 0 : 1;
	}

	/**
	 * คืนค่าเป็น double
	 *
	 * @return double
	 * @assert create(0.454)->toDouble() [==] 0.454
	 * @assert create(0.545)->toDouble() [==] 0.545
	 */
	public function toDouble()
	{
		return (double)$this->value;
	}

	/**
	 * คืนค่าเป็น float
	 *
	 * @return float
	 * @assert create(0.454)->toFloat() [==] 0.454
	 * @assert create(0.545)->toFloat() [==] 0.545
	 */
	public function toFloat()
	{
		return (float)$this->value;
	}

	/**
	 * คืนค่าเป็น integer
	 *
	 * @return int
	 * @assert create(0.454)->toInt() [==] 0
	 * @assert create(2.945)->toInt() [==] 2
	 */
	public function toInt()
	{
		return (int)$this->value;
	}

	/**
	 * คืนค่าเป็น Object
	 *
	 * @return object
	 * @assert create('test')->toObject() [==] (object)'test'
	 */
	public function toObject()
	{
		return (object)$this->value;
	}

	/**
	 * คืนค่าเป็น String
	 *
	 * @return string|null คืนค่าเป็น string หรือ null
	 * @assert create('ทดสอบ')->toString() [==] 'ทดสอบ'
	 * @assert create('1')->toString() [==] '1'
	 * @assert create(1)->toString() [==] '1'
	 * @assert create(null)->toString() [==] null
	 */
	public function toString()
	{
		return $this->value === null ? null : (string)$this->value;
	}

	/**
	 * แปลง tag และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 * เช่นหัวข้อของบทความ
	 *
	 * @return string
	 * @assert create(' ทด\/สอบ'."\r\n\t".'<?php echo \'555\'?> ')->topic() [==] 'ทด&#92;/สอบ &lt;?php echo &#039;555&#039;?&gt;'
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
	 * @assert create(" http://www.kotchasan.com?a=1&b=2&amp;c=3 ")->url() [==] 'http://www.kotchasan.com?a=1&amp;b=2&amp;c=3'
	 */
	public function url()
	{
		return trim($this->htmlspecialchars(false));
	}

	/**
	 * ฟังก์ชั่น แปลง & " ' < > \ เป็น HTML entities และลบช่องว่างหัวท้าย
	 * ใช้แปลงค่าที่รับจาก input ที่ไม่ยอมรับ tag
	 *
	 * @return string
	 * @assert create(" ทด\/สอบ<?php echo '555'?> ")->text() [==] 'ทด&#92;/สอบ&lt;?php echo &#039;555&#039;?&gt;'
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
	 * @assert create("ทด\/สอบ\n<?php echo '555'?>")->textarea() [==] "ทด&#92;/สอบ&lt;br /&gt;\n&lt;?php echo '555'?&gt;"
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
	 * @assert create('ทด\/สอบ<?php echo "555"?>')->description() [==] 'ทด สอบ'
	 * @assert create('ทด\/สอบ<style>body {color: red}</style>')->description() [==] 'ทด สอบ'
	 * @assert create('ทด\/สอบ<b>ตัวหนา</b>')->description() [==] 'ทด สอบตัวหนา'
	 * @assert create('ทด\/สอบ{LNG_Language name}')->description() [==] 'ทด สอบ'
	 * @assert create('ทด\/สอบ[code]ตัวหนา[/code]')->description() [==] 'ทด สอบ'
	 * @assert create('ทด\/สอบ[b]ตัวหนา[/b]')->description() [==] 'ทด สอบตัวหนา'
	 * @assert create('ท&amp;ด&quot;\&nbsp;/__ส-อ+บ')->description() [==] 'ท ด ส อ บ'
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
			'/{(WIDGET|LNG)_[\w\s\.\-\'\(\),%\/:&\#;]+}/su' => '',
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
	 * ลบ PHP tag และแปลง \ เป็น $#92; ใช้รับข้อมูลจาก editor
	 * เช่นเนื้อหาของบทความ
	 *
	 * @return string
	 * @assert create('ทด\/สอบ<?php echo "555"?>')->detail() [==] 'ทด&#92;/สอบ'
	 */
	public function detail()
	{
		return preg_replace(array('/<\?(.*?)\?>/su', '/\\\/'), array('', '&#92;'), $this->value);
	}

	/**
	 * ลบ tags และ ลบช่องว่างไม่เกิน 1 ช่อง ไม่ขึ้นบรรทัดใหม่
	 * ใช้เป็น tags หรือ keywords
	 *
	 * @param int $len ความยาวของ keywords 0 หมายถึงคืนค่าทั้งหมด
	 * @return string
	 * @assert create("<b>ทด</b>   \r\nสอบ")->keywords() [==] 'ทด สอบ'
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
	 * @assert create("ทด'สอบ")->quote() [==] "ทด&#39;สอบ"
	 */
	public function quote()
	{
		return str_replace("'", '&#39;', $this->value);
	}

	/**
	 * ฟังก์ชั่นลบอักขระที่ไม่ต้องการออก
	 *
	 * @param string $format Regular Expression อักขระที่ยอมรับ เช่น \d\s\-:
	 * @return string
	 */
	public function filter($format)
	{
		return trim(preg_replace('/[^'.$format.']/', '', $this->value));
	}

	/**
	 * วันที่และเวลา
	 *
	 * @return string
	 * @assert create('2016-01-01 20:20:20')->date() [==] '2016-01-01 20:20:20'
	 */
	public function date()
	{
		return $this->filter('\d\s\-:');
	}

	/**
	 * ค่าสี
	 *
	 * @return string
	 * @assert create('#000')->color() [==] '#000'
	 * @assert create('red')->color() [==] 'red'
	 */
	public function color()
	{
		return $this->filter('\#a-zA-Z0-9');
	}

	/**
	 * ตัวเลข
	 *
	 * @return string
	 * @assert create(12345)->number() [==] '12345'
	 * @assert create(0.12345)->number() [==] '012345'
	 */
	public function number()
	{
		return $this->filter('\d');
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