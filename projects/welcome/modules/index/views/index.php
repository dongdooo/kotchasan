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
		echo '<html style="height:100%;width:100%"><head>';
		echo '<meta charset=utf-8>';
		echo '<link href="https://fonts.googleapis.com/css?family=Itim&subset=thai,latin" rel="stylesheet" type="text/css">';
		echo '<meta name=viewport content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
		echo '<style>';
		echo '.warper{display:inline-block;text-align:center;height:50%;}';
		echo '.warper::before{content:"";display:inline-block;height:100%;vertical-align:middle;width:0px;}';
		echo '</style>';
		echo '</head><body style="height:100%;width:100%;margin:0;font-family:Itim, Tahoma, Loma;color:#666;">';
		echo '<div class=warper style="display:block"><div class="warper"><div>';
		echo '<img src="'.WEB_URL.'../../skin/img/kotchasan.png" style="width:100px" alt="Kotchasan PHP Framework">';
		echo '<h1 style="line-height:1.8;margin:0;text-shadow:3px 3px 0 rgba(0,0,0,0.1);font-weight:normal;">คชสาร (Kotchasan)</h1>';
		echo 'Siam PHP Framework';
		echo '</div></div></body></html>';
	}
}