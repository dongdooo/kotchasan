<?php
/**
 * @filesource core/ckeditor.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * used Html class
 */
use \Html AS Html;

/**
 * CKEditor
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class CKEditor extends Html
{

	/**
	 * สร้างโค้ด HTML สำหรับแสดง CKEditor
	 *
	 * @return string
	 */
	public function render()
	{
		$content = array('item' => '', 'label' => '', 'tag' => '', 'itemClass' => '');
		$prop = array();
		$innerHTML = '';
		if (isset($this->attributes['id']) && !isset($this->attributes['name'])) {
			$this->attributes['name'] = $this->attributes['id'];
		}
		if (isset($this->attributes['name']) && !isset($this->attributes['id'])) {
			$this->attributes['id'] = $this->attributes['name'];
		}
		foreach ($this->attributes as $key => $value) {
			if ($key === 'itemClass') {
				$content['item'] = '<div class="'.$value.'">';
				$content['itemClass'] = '</div>';
			} elseif ($key === 'id') {
				$for = ' for="'.$value.'"';
				$prop[] = ' id="'.$value.'"';
			} elseif ($key === 'name') {
				$prop[] = ' name="'.$value.'"';
			} elseif ($key === 'value') {
				$innerHTML = \String::detail_to_text($value);
			} elseif ($key !== 'label') {
				$attributes[$key] = $value;
			}
		}
		if (isset($this->attributes['label'])) {
			$content['label'] = '<label'.$for.'>'.$this->attributes['label'].'</label>';
		}
		$content['tag'] = '<div><'.$this->tag.implode('', $prop).'>'.$innerHTML.'</'.$this->tag.'></div>';
		$_SESSION['CKEDITOR'] = $_SESSION['login']['id'];
		if (isset($this->attributes['id'])) {
			$script = array();
			$script[] = 'CKEDITOR.replace("'.$this->attributes['id'].'", {';
			foreach ($attributes as $key => $value) {
				$script[] = $key.':'.(is_int($value) ? $value : '"'.$value.'"').',';
			}
			$script[] = '});';
			self::$form->javascript[] = implode("\n", $script);
		}
		return implode('', $content);
	}
}
