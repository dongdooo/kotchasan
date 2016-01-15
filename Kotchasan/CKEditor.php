<?php
/*
 * @filesource Kotchasan/CKEditor.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\Html;
use \Kotchasan\Text;

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
				$innerHTML = Text::toEditor($value);
			} elseif ($key !== 'label' && $key !== 'upload') {
				$attributes[$key] = $value;
			}
		}
		if (isset($this->attributes['label'])) {
			$content['label'] = '<label'.$for.'>'.$this->attributes['label'].'</label>';
		}
		$content['tag'] = '<div><'.$this->tag.implode('', $prop).'>'.$innerHTML.'</'.$this->tag.'></div>';
		$_SESSION['CKEDITOR'] = $_SESSION['login']->id;
		if (isset($this->attributes['id'])) {
			$script = array();
			foreach ($attributes as $key => $value) {
				$script[] = $key.':'.(is_int($value) ? $value : '"'.$value.'"');
			}
			if (isset($this->attributes['upload']) && $this->attributes['upload'] == true) {
				if (is_dir(ROOT_PATH.'ckfinder')) {
					$script[] = 'filebrowserBrowseUrl:"'.WEB_URL.'ckfinder/ckfinder.html"';
					$script[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'ckfinder/ckfinder.html?Type=Images"';
					$script[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'ckfinder/ckfinder.html?Type=Flash"';
					$script[] = 'filebrowserUploadUrl:"'.WEB_URL.'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files"';
					$script[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images"';
					$script[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash"';
				} else {
					$connector = urlencode(WEB_URL.'ckeditor/filemanager/connectors/php/connector.php');
					$script[] = 'filebrowserBrowseUrl:"'.WEB_URL.'ckeditor/filemanager/browser/default/browser.html?Connector='.$connector.'"';
					$script[] = 'filebrowserImageBrowseUrl:"'.WEB_URL.'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector='.$connector.'"';
					$script[] = 'filebrowserFlashBrowseUrl:"'.WEB_URL.'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector='.$connector.'"';
					$script[] = 'filebrowserUploadUrl:"'.WEB_URL.'ckeditor/filemanager/connectors/php/upload.php"';
					$script[] = 'filebrowserImageUploadUrl:"'.WEB_URL.'ckeditor/filemanager/connectors/php/upload.php?Type=Image"';
					$script[] = 'filebrowserFlashUploadUrl:"'.WEB_URL.'ckeditor/filemanager/connectors/php/upload.phpType=Flash"';
				}
			}
			self::$form->javascript[] = "CKEDITOR.replace(\"".$this->attributes['id']."\", {\n".implode(",\n", $script)."\n});";
		}
		return implode('', $content);
	}
}