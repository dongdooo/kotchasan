<?php
/*
 * @filesource Kotchasan/Form.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Html;

/**
 * Form class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Form extends \Kotchasan\KBase
{
	/**
	 * ชื่อ tag
	 *
	 * @var string
	 */
	private $tag;
	/**
	 * tag attributes
	 *
	 * @var array
	 */
	private $attributes;
	/**
	 * Javascript
	 *
	 * @var string
	 */
	public $javascript;

	/**
	 * ฟังก์ชั่นสร้าง Form Element
	 *
	 * @param string $tag
	 * @param array $param property ของ Input
	 * id, name, type property ต่างๆของinput
	 * label : ข้อความแสดงใน label ของ input
	 * labelClass : class ของ label
	 * comment : ถ้ากำหนดจะแสดงคำอธิบายของ input
	 * ถ้าไม่กำหนดทั้ง label และ labelClass จะเป็นการสร้าง input อย่างเดียว
	 * @param string $options ตัวเลือก options ของ select
	 * array('name1' => 'value1', 'name2' => 'value2', ....)
	 */
	public function render()
	{
		$prop = array();
		$event = array();
		foreach ($this->attributes as $k => $v) {
			if ($k === 'itemClass' || $k === 'labelClass' || $k === 'label' || $k === 'comment' || $k === 'value' || $k === 'dataPreview' || $k === 'previewSrc' || $k === 'options' || $k === 'optgroup' || $k === 'validator') {
				$$k = $v;
			} elseif ($k === 'result') {
				$prop[$k] = 'data-'.$k.'="'.$v.'"';
			} elseif (is_int($k)) {
				$prop[$v] = $v;
			} elseif ($v === true) {
				$prop[$k] = $k;
			} elseif ($v === false) {
				continue;
			} elseif (preg_match('/^on([a-z]+)/', $k, $match)) {
				$event[$match[1]] = $v;
			} else {
				$prop[$k] = $k.'="'.$v.'"';
				$$k = $v;
			}
		}
		if (isset($id) && empty($name)) {
			$name = $id;
			$prop['name'] = 'name="'.$name.'"';
		}
		if (isset($id)) {
			if (isset($validator)) {
				$js = array();
				$js[] = '"'.$id.'"';
				$js[] = '"'.$validator[0].'"';
				$js[] = $validator[1];
				if (isset($validator[2])) {
					$js[] = '"'.$validator[2].'"';
					$js[] = empty($validator[3]) || $validator[3] === null ? 'null' : '"'.$validator[3].'"';
					$js[] = '"'.Html::$form->attributes['id'].'"';
				}
				$this->javascript[] = 'new GValidator('.implode(', ', $js).');';
				unset($validator);
			}
			foreach ($event as $on => $func) {
				$this->javascript[] = '$G("'.$id.'").addEvent("'.$on.'", '.$func.');';
			}
		}
		if ($this->tag == 'select') {
			unset($prop['type']);
			$value = isset($value) ? $value : null;
			if (isset($options)) {
				$datas = array();
				foreach ($options as $k => $v) {
					$sel = $value == $k ? ' selected' : '';
					$datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
				}
				$value = implode('', $datas);
			} elseif (isset($optgroup)) {
				$datas = array();
				foreach ($optgroup as $group_label => $options) {
					$datas[] = '<optgroup label="'.$group_label.'">';
					foreach ($options as $k => $v) {
						$sel = $value == $k ? ' selected' : '';
						$datas[] = '<option value="'.$k.'"'.$sel.'>'.$v.'</option>';
					}
					$datas[] = '</optgroup>';
				}
				$value = implode('', $datas);
			}
		} elseif (isset($value)) {
			if ($this->tag === 'textarea') {
				$value = str_replace(array('{', '}', '&amp;'), array('&#x007B;', '&#x007D;', '&'), htmlspecialchars($value));
			} else {
				$prop['value'] = 'value="'.$value.'"';
			}
		}
		if (!empty($comment) && empty($prop['title'])) {
			$prop['title'] = 'title="'.$comment.'"';
		}
		if (isset($dataPreview)) {
			$prop['data-preview'] = 'data-preview="'.$dataPreview.'"';
		}
		$prop = implode(' ', $prop);
		if ($this->tag == 'input') {
			$element = '<'.$this->tag.' '.$prop.'>';
		} elseif (isset($value)) {
			$element = '<'.$this->tag.' '.$prop.'>'.$value.'</'.$this->tag.'>';
		} else {
			$element = '<'.$this->tag.' '.$prop.'></'.$this->tag.'>';
		}
		if (empty($itemClass)) {
			$input = empty($comment) ? '' : '<div class="item">';
			if (empty($labelClass) && empty($label)) {
				$input .= $element;
			} elseif (isset($type) && $type === 'checkbox') {
				$input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.'&nbsp;'.$label.'</label>';
			} else {
				$input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.( empty($label) ? '' : $label.'&nbsp;').$element.'</label>';
			}
			if (!empty($comment)) {
				$input .= '<div class="comment"'.(empty($id) ? '' : ' id="result_'.$id.'"').'>'.$comment.'</div></div>';
			}
		} else {
			$input = '<div class="'.$itemClass.'">';
			if (isset($type) && $type === 'checkbox') {
				$input .= '<label'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.'&nbsp;'.$label.'</label>';
			} else {
				if (isset($dataPreview)) {
					$input .= '<div class=usericon><span><img src="'.$previewSrc.'" alt="Image preview" id='.$dataPreview.'></span></div>';
				}
				if (isset($label) && isset($id)) {
					$input .= '<label for="'.$id.'">'.$label.'</label>';
				}
				$input .= '<span'.(empty($labelClass) ? '' : ' class="'.$labelClass.'"').'>'.$element.'</span>';
			}
			if (!empty($comment)) {
				$input .= '<div class="comment"'.(empty($id) ? '' : ' id="result_'.$id.'"').'>'.$comment.'</div>';
			}
			$input .= '</div>';
		}
		return $input;
	}

	/**
	 * สร้าง input, select, textarea สำหรับใส่ลงในฟอร์ม
	 *
	 * @param array $param property ของ Input
	 * id, name, type property ต่างๆของinput
	 * options สำหรับ select เท่านั้น เช่น array('value1'=> 'name1', 'value2'=>'name2', ...)
	 * label ข้อความแสดงใน label ของ input
	 * labelClass class ของ label
	 * comment ถ้ากำหนดจะแสดงคำอธิบายของ input
	 * ถ้าไม่กำหนดทั้ง label และ labelClass จะเป็นการสร้าง input อย่างเดียว
	 */
	public static function text($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'text';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function password($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'password';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function url($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'url';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function email($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'email';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function color($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'text';
		$attributes['class'] = 'color';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function date($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'date';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function number($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'number';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function currency($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'text';
		$attributes['class'] = 'currency';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function button($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'button';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function submit($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'submit';
		if (isset($attributes['name']) && $attributes['name'] == 'submit') {
			unset($attributes['name']);
		}
		if (isset($attributes['id']) && $attributes['id'] == 'submit') {
			unset($attributes['id']);
		}
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function checkbox($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'checkbox';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function radio($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'radio';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function hidden($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'hidden';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function file($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'input';
		$attributes['type'] = 'file';
		$attributes['class'] = 'g-file';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function select($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'select';
		$obj->attributes = $attributes;
		return $obj;
	}

	public static function textarea($attributes = array())
	{
		$obj = new static;
		$obj->tag = 'textarea';
		$obj->attributes = $attributes;
		return $obj;
	}

	/**
	 * ฟังก์ชั่นสร้าง input ชนิด hidden สำหรับใช้ในฟอร์ม
	 * ใช้ประโยชน์ในการสร้าง URL เพื่อส่งกลับไปยังหน้าเดิมหลังจาก submit แล้ว
	 *
	 * @return array
	 */
	public static function get2Input()
	{
		$hiddens = array();
		foreach (self::$request->getQueryParams() AS $key => $value) {
			if (preg_match('/^[_]+(.*)$/', $key, $match)) {
				$hiddens[] = '<input type="hidden" name="_'.$match[1].'" value="'.$value.'">';
			}
		}
		return $hiddens;
	}
}