<?php
/**
 * @filesource index/views/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;
/*
 * default View
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */

class View extends \View
{

	public function render()
	{
		echo '<body style="height:100%;width:100%;position:relative;line-height:1;font-family:Tahoma,Loma,Arial;padding:0;margin:0;"><div style="text-align:center;position:absolute;top:50%;width:100%;margin-top:-3em;"><h1>Kotchasan</h1><br>PHP Framework</div></body>';
	}
}