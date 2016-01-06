<?php
/**
 * @filesource index/controllers/login.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Login;

use \Login as Login;

/**
 * Login Form
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Controller
{

	/**
	 * แสดงผล
	 */
	public function render()
	{
		// โหมดตัวอย่าง
		if (empty(Login::$text_email) && empty(Login::$text_password) && !empty(self::$cfg->demo_mode)) {
			Login::$text_email = 'demo';
			Login::$text_password = 'demo';
		}
		// form login
		$form = \Html::create('form', array(
			'id' => 'login_frm',
			'class' => 'login',
			'autocomplete' => 'off',
			'ajax' => false
		));
		// h1
		$form->add('h1', array(
			'class' => 'icon-customer',
			'innerHTML' => \Language::get('Administrator area')
		));
		// message
		if (isset(Login::$login_message)) {
			$form->add('p', array(
				'class' => empty(Login::$login_input) ? 'message' : 'error',
				'innerHTML' => Login::$login_message
			));
			if (isset(Login::$login_input)) {
				$a = array();
				$a[] = 'var input = $E("'.Login::$login_input.'");';
				$a[] = 'input.focus();';
				$a[] = 'input.select();';
				$form->script(implode("\n", $a));
			}
		}
		// fieldset
		$fieldset = $form->add('fieldset');
		// text (email or phone)
		$fieldset->add('text', array(
			'id' => 'text_email',
			'labelClass' => 'g-input icon-email',
			'placeholder' => \Language::get('Email'),
			'accesskey' => 'e',
			'maxlength' => 255,
			'value' => isset(Login::$text_email) ? Login::$text_email : '',
		));
		// password
		$fieldset->add('password', array(
			'id' => 'text_password',
			'labelClass' => 'g-input icon-password',
			'placeholder' => \Language::get('Password'),
			'value' => isset(Login::$text_password) ? Login::$text_password : ''
		));
		// input-groups (div สำหรับจัดกลุ่ม input)
		$group = $fieldset->add('groups');
		// a
		$group->add('a', array(
			'href' => \Url::next(array('action' => 'forgot')),
			'class' => 'td',
			'title' => \Language::get('Request new password'),
			'innerHTML' => ''.\Language::get('Forgot').' ?'
		));
		// checkbox
		$group->add('checkbox', array(
			'id' => 'bool_remember',
			'checked' => \Input::cookie('login_remember')->toBoolean(),
			'value' => 1,
			'label' => \Language::get('Remember me'),
			'labelClass' => 'td right'
		));
		// submit
		$fieldset->add('submit', array(
			'class' => 'button ok large wide',
			'value' => \Language::get('Sign In')
		));
		// คืนค่า form
		return $form->render();
	}

	/**
	 * title bar
	 */
	public function title()
	{
		return \Language::get('Administrator area');
	}
}