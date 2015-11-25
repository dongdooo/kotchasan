<?php
/**
 * @filesource index/views/usericon.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Usericon;

/**
 * คลาสสำหรับการสร้างหน้าเพจของ GCMS
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \View
{

	public function index()
	{
		$picture = ROOT_PATH.'skin/img/noicon.jpg';
		// ตรวจสอบรูป
		$info = getImageSize($picture);
		if (empty($info['error'])) {
			header("Content-type: $info[mime]");
			echo file_get_contents($picture);
		}
	}
}