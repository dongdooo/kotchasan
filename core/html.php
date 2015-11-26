<?php
/**
 * @filesource core/html.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 *
 */
use \Form AS From;

/**
 * html
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Html
{
	/**
	 * ชื่อ tag
	 *
	 * @var string
	 */
	protected $tag;
	/**
	 * attrribute ของ tag
	 *
	 * @var array
	 */
	public $attributes;
	/**
	 * แอเรย์ของข้อมูลภายใน tag
	 *
	 * @var array
	 */
	protected $rows;
	/**
	 * Javascript
	 *
	 * @var array
	 */
	protected $javascript;
	/**
	 * ตัวแปรเก็บ form object
	 *
	 * @var \Html
	 */
	public static $form;

	/**
	 * class Constructor
	 */
	public function __construct($tag, $attributes = array())
	{
		$this->tag = strtolower($tag);
		$this->attributes = $attributes;
		$this->rows = array();
		$this->javascript = array();
	}

	/**
	 * creat new Element
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @return \Html
	 */
	public static function create($tag, $attributes = array())
	{
		if (method_exists(__CLASS__, $tag)) {
			$obj = self::$tag($attributes);
		} elseif (method_exists('Form', $tag)) {
			$obj = From::$tag($attributes);
		} else {
			$obj = new static($tag, $attributes);
		}
		return $obj;
	}

	/**
	 * แทรก tag ลงใน element เหมือนการใช้งาน innerHTML
	 *
	 * @param string $tag
	 * @param array $attributes
	 * @return \Html
	 */
	public function add($tag, $attributes = array())
	{
		if ($tag == 'groups' || $tag == 'groups-table') {
			if (isset($attributes['label'])) {
				$item = self::fieldset(array(
					'class' => 'item',
					'title' => $attributes['label']
				));
			} else {
				$item = new static('div', array('class' => 'item'));
			}
			$this->rows[] = $item;
			$obj = $item->add('div', array('class' => 'input-'.$tag));
			$rows = array();
			$comment = array();
			if (empty($attributes['id'])) {
				$id = '';
				$name = '';
			} else {
				$id = ' id='.$attributes['id'];
				$name = ' name='.$attributes['id'].'[]';
				$comment['id'] = 'result_'.$attributes['id'];
			}
			foreach ($attributes as $key => $value) {
				if ($key == 'comment') {
					$comment['class'] = 'comment';
					$comment['innerHTML'] = $value;
				} elseif ($key == 'checkbox' || $key == 'radio') {
					foreach ($value as $v => $text) {
						$chk = isset($attributes['value']) && in_array($v, $attributes['value']) ? ' checked' : '';
						$rows[] = '<label>'.$text.'&nbsp;<input type='.$key.$id.$name.$chk.' value="'.$v.'"></label>';
						$id = '';
					}
				}
			}
			if (!empty($rows)) {
				$obj->appendChild(implode('&nbsp; ', $rows));
			}
			if (isset($attributes['comment'])) {
				$item->add('div', $comment);
			}
		} else {
			$obj = self::create($tag, $attributes);
			$this->rows[] = $obj;
		}
		return $obj;
	}

	/**
	 * create Fieldset element
	 *
	 * @param array $attributes
	 * @return \Html
	 */
	public static function fieldset($attributes = array())
	{
		$prop = array();
		$span = array();
		foreach ($attributes as $key => $value) {
			if ($key == 'title') {
				$span['innerHTML'] = $value;
			} elseif ($key == 'titleClass') {
				$span['class'] = $value;
			} else {
				$prop[$key] = $value;
			}
		}
		$obj = new static('fieldset', $prop);
		if (isset($span['innerHTML'])) {
			$legend = $obj->add('legend');
			$legend->add('span', $span);
		}
		return $obj;
	}

	/**
	 * create Form element
	 *
	 * @param array $attributes
	 * @return \Html
	 */
	public static function form($attributes = array())
	{
		$ajax = false;
		$prop = array('method' => 'post');
		foreach ($attributes as $key => $value) {
			if (in_array($key, array('ajax', 'action', 'onsubmit', 'confirmsubmit', 'elements', 'script'))) {
				$$key = $value;
			} else {
				$prop[$key] = $value;
			}
		}
		if (isset($prop['id'])) {
			$script = 'new GForm("'.$prop['id'].'"';
			if (isset($action)) {
				if ($ajax) {
					$script .= ', "'.$action.'"';
					if (isset($confirmsubmit)) {
						$script .= ',null ,false , function(){return '.$confirmsubmit.'}';
					}
				} else {
					$prop['action'] = $action;
				}
			}
			$script .=')';
			if (isset($onsubmit)) {
				$script .= '.onsubmit('.$onsubmit.')';
			}
			$script .=';';
		}
		self::$form = new static('form', $prop);
		self::$form->rows = Form::get2Input();
		if (isset($script)) {
			self::$form->javascript[] = $script;
		}
		return self::$form;
	}

	/**
	 * กำหนด Javascript
	 *
	 * @param string $script
	 */
	public function script($script)
	{
		if (isset(self::$form)) {
			self::$form->javascript[] = $script;
		} else {
			$this->javascript[] = $script;
		}
	}

	/**
	 * สร้าง element และแทรก HTML ลงใน tag ให้ผลลัพท์เป็น string เลย
	 *
	 * @param string $html
	 * @return string
	 */
	public function innerHtml($html)
	{
		return '<'.$this->tag.$this->renderAttributes().'>'.$html.'</'.$this->tag.'>';
	}

	/**
	 * แทรก HTML ลงใน element ที่ตำแหน่งท้ายสุด
	 *
	 * @param string $html
	 */
	public function appendChild($html)
	{
		$this->rows[] = $html;
	}

	/**
	 * สร้างโค้ด HTML
	 *
	 * @return string
	 */
	public function render()
	{
		$result = '<'.$this->tag.$this->renderAttributes().'>'.(isset($this->attributes['innerHTML']) ? $this->attributes['innerHTML'] : '');
		foreach ($this->rows as $row) {
			if (is_string($row)) {
				$result .= $row;
			} else {
				$result .= $row->render();
				if (!empty($row->javascript)) {
					foreach ($row->javascript as $script) {
						self::$form->javascript[] = $script;
					}
				}
			}
		}
		$result .= '</'.$this->tag.'>';
		if ($this->tag == 'form' && !empty(self::$form->javascript)) {
			$result .= "\n".preg_replace('/^[\s\t]+/m', '', "<script>\n".implode("\n", self::$form->javascript)."\n</script>");
		} elseif (!empty($this->javascript)) {
			$result .= "\n".preg_replace('/^[\s\t]+/m', '', "<script>\n".implode("\n", $this->javascript)."\n</script>");
		}
		return $result;
	}

	/**
	 * สร้าง Attributes ของ tag
	 *
	 * @return string
	 */
	protected function renderAttributes()
	{
		$attr = array();
		foreach ($this->attributes as $key => $value) {
			if ($key != 'innerHTML') {
				if (is_int($key)) {
					$attr[] = $value;
				} else {
					$attr[] = $key.'="'.$value.'"';
				}
			}
		}
		return sizeof($attr) == 0 ? '' : ' '.implode(' ', $attr);
	}
}