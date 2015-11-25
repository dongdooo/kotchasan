<?php
// ลบไฟล์
session_start();
// header
header("content-type: text/html; charset=UTF-8");
// ตัวแปรที่จำเป็นสำหรับ Framework ใช้ระบุ root folder
define('APP_PATH', str_replace(array('\\', 'ckeditor/filemanager/browser/default'), array('/', ''), dirname(__FILE__)));
// load Kotchasan
include APP_PATH.'core/load.php';
// inint Kotchasan Framework
Kotchasan::createWebApplication();
if (\Input::isReferer() && \Login::isAdmin()) {
	if (isset($_POST['did'])) {
		\File::removeDirectory(ROOT_PATH.$_POST['did']);
	} elseif (isset($_POST['fid'])) {
		@unlink(ROOT_PATH.$_POST['fid']);
	}
} else {
	echo 'Do not delete!';
}
