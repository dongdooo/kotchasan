<?php
/*
 * @filesource index/controllers/index.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Index\Index;

use \Kotchasan\Orm\Recordset;
use \Kotchasan\Database;

/**
 * default Controller
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\Controller
{

	public function index()
	{
		$action = $this->request->get('action', 'hello')->toString();
		$this->$action();
	}

	/**
	 * Loading Performance
	 * ทดสอบการโหลดเว็บไซต์แบบน้อยที่สุด
	 */
	public function hello()
	{
		echo 'Hello World!';
	}

	/**
	 * ORM Performance (select only)
	 * ทดสอบการเรียกข้อมูลด้วย ORM
	 */
	public function select()
	{
		$rs = Recordset::create('Index\World\Model');
		$rs->updateAll(array('name' => 'Hello World!'));
		for ($i = 0; $i < 2; $i++) {
			$rnd = mt_rand(1, 10000);
			$result = $rs->find($rnd);
		}
		$result = $rs->find($result->id);
		echo $result->name;
	}

	/**
	 * ORM Performance (select and update)
	 * ทดสอบการเรียกข้อมูลและอัปเดทข้อมูลด้วย ORM
	 */
	public function orm()
	{
		$rs = Recordset::create('Index\World\Model');
		$rs->updateAll(array('name' => ''));
		for ($i = 0; $i < 2; $i++) {
			$rnd = mt_rand(1, 10000);
			$result = $rs->find($rnd);
			$result->name = 'Hello World!';
			$result->save();
		}
		$result = $rs->find($result->id);
		echo $result->name;
	}

	/**
	 * Query Builder Performance
	 * ทดสอบการเรียกข้อมูลและอัปเดทข้อมูลด้วย Query Builder
	 */
	public function querybuilder()
	{
		$db = Database::create();
		$db->createQuery()->update('world')->set(array('name' => ''))->execute();
		$query = $db->createQuery()->from('world');
		for ($i = 0; $i < 2; $i++) {
			$rnd = mt_rand(1, 10000);
			$result = $query->where(array('id', $rnd))->first();
			$db->createQuery()->update('world')->where(array('id', $result->id))->set(array('name' => 'Hello World!'))->execute();
		}
		$result = $query->where(array('id', $result->id))->first();
		echo $result->name;
	}

	/**
	 * SQL Command Performance
	 * ทดสอบการเรียกข้อมูลและอัปเดทข้อมูลโดยใช้คำสั่ง SQL Command
	 */
	public function sql()
	{
		$db = Database::create();
		$db->query("UPDATE `world` SET `name`=''");
		for ($i = 0; $i < 2; $i++) {
			$rnd = mt_rand(1, 10000);
			$result = $db->customQuery("SELECT * FROM  `world` WHERE `id`=".$rnd);
			$db->query("UPDATE `world` SET `name`='Hello World!' WHERE `id`=".$result[0]->id);
		}
		$result = $db->customQuery("SELECT * FROM  `world` WHERE `id`=".$result[0]->id);
		echo $result[0]->name;
	}
}