<?php
/**
 * @filesource core/htmltable.php
 * @link http://www.kotchasan.com/
 * @copyright 2015 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

/**
 * HTML table
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Htmltable
{
	/**
	 * แอเรย์เก็บข้อมูลส่วน thead
	 *
	 * @var array
	 */
	private $thead;
	/**
	 * แอเรย์ของ Tablerow เก็บแถวของตาราง (tbody)
	 *
	 * @var array
	 */
	private $tbody;
	/**
	 * แอเรย์ของ Tablerow เก็บแถวของตาราง (tfoot)
	 *
	 * @var array
	 */
	private $tfoot;
	/**
	 * caption ของ ตาราง
	 *
	 * @var string
	 */
	private $caption;
	/**
	 * แอเรย์เก็บ property ของตาราง
	 *
	 * @var array
	 */
	private $properties;

	public static function create($properties = array())
	{
		$obj = new static;
		$obj->tbody = array();
		$obj->tfoot = array();
		$obj->thead = array();
		$obj->properties = $properties;
		return $obj;
	}

	public function addCaption($text)
	{
		$this->caption = $text;
	}

	public function addHeader($headers)
	{
		$this->thead[] = $headers;
	}

	public function addRow(\Tablerow $row)
	{
		$this->tbody[] = $row;
	}

	public function addFooter(\Tablerow $row)
	{
		$this->tfoot[] = $row;
	}

	public function render()
	{
		$prop = array();
		foreach ($this->properties as $k => $v) {
			$prop[] = $k.'="'.$v.'"';
		}
		$table = array("\n<table".(empty($prop) ? '' : ' '.implode(' ', $prop)).'>');
		if (!empty($this->caption)) {
			$table[] = '<caption>'.$this->caption.'</caption>';
		}
		// thead
		if (!empty($this->thead)) {
			$thead = array();
			foreach ($this->thead as $r => $rows) {
				$tr = array();
				foreach ($rows as $c => $th) {
					$prop = array('id' => 'id="c'.$c.'"', 'scope' => 'scope="col"');
					foreach ($th as $key => $value) {
						if ($key != 'text') {
							$prop[$key] = $key.'="'.$value.'"';
						}
					}
					$tr[] = '<th '.implode(' ', $prop).'>'.(isset($th['text']) ? $th['text'] : '').'</th>';
				}
				if (!empty($tr)) {
					$thead[] = "<tr>\n".implode("\n", $tr)."\n</tr>";
				}
			}
			if (!empty($thead)) {
				$table[] = "<thead>\n".implode("\n", $thead)."\n</thead>";
			}
		}
		// tfoot
		if (!empty($this->tfoot)) {
			$rows = array();
			foreach ($this->tfoot as $tr) {
				$rows[] = $tr->render();
			}
			if (!empty($rows)) {
				$table[] = "<tfoot>\n".implode("\n", $rows)."\n</tfoot>";
			}
		}
		// tbody
		if (!empty($this->tbody)) {
			$rows = array();
			foreach ($this->tbody as $tr) {
				$rows[] = $tr->render();
			}
			if (!empty($rows)) {
				$table[] = "<tbody>\n".implode("\n", $rows)."\n</tbody>";
			}
		}
		$table[] = "</table>\n";
		return implode("\n", $table);
	}
}

class Tablerow
{
	private $id;
	private $tds;

	public static function create($id)
	{
		$obj = new static;
		$obj->id = $id;
		$obj->tds = array();
		return $obj;
	}

	public function addCell($td)
	{
		$this->tds[] = $td;
	}

	public function render()
	{
		$row = array('<tr>');
		foreach ($this->tds as $c => $td) {
			$prop = array();
			$tag = 'td';
			foreach ($td as $key => $value) {
				if ($key == 'scope') {
					$tag = 'th';
					$prop['scope'] = 'scope="'.$value.'"';
					$prop['id'] = 'id="r'.$this->id.'"';
				} elseif ($key != 'text') {
					$prop[$key] = $key.'="'.$value.'"';
				}
			}
			$prop['headers'] = $tag == 'th' ? 'headers="c'.$c.'"' : 'headers="r'.$this->id.' c'.$c.'"';
			$tr[] = '<'.$tag.' '.implode(' ', $prop).'>'.(isset($th['text']) ? $th['text'] : '').'</'.$tag.'>';
			$row[] = '<'.$tag.' '.implode(' ', $prop).'>'.(empty($td['text']) ? '' : $td['text']).'</'.$tag.'>';
		}
		$row[] = '</tr>';
		return implode("\n", $row);
	}
}