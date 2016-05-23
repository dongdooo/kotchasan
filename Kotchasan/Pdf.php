<?php
/*
 * @filesource Kotchasan/Pdf.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan;

use \Kotchasan\DOMParser;
use \Kotchasan\DOMNode;

/**
 * Pdf Class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Pdf extends \PDF\FPDF
{
	protected $fontSize = 10;
	protected $lineHeight = 5;
	protected $B;
	protected $I;
	protected $U;
	protected $unit;
	protected $link = null;
	protected $lastBlock = true;
	protected $css;
	protected $cssClass;

	/**
	 * Create FPDF ภาษาไทย
	 *
	 * @param string $orientation
	 * @param string $unit
	 * @param string $size
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
	{
		// create FPDF
		parent::__construct($orientation, $unit, $size);
		// ฟ้อนต์ภาษาไทย
		$this->AddFont('loma', '', 'Loma.php');
		$this->AddFont('loma', 'B', 'Loma-Bold.php');
		$this->AddFont('loma', 'I', 'Loma-Oblique.php');
		$this->AddFont('loma', 'BI', 'Loma-BoldOblique.php');
		$this->AddFont('angsana', '', 'angsa.php');
		$this->AddFont('angsana', 'B', 'angsab.php');
		$this->AddFont('angsana', 'I', 'angsai.php');
		$this->AddFont('angsana', 'BI', 'angsaz.php');
		// ฟอ้นต์เริ่มต้น
		$this->SetFont('loma', '', $this->fontSize);
		// ค่าเริ่มต้นตัวแปรต่างๆ
		$this->B = 0;
		$this->I = 0;
		$this->U = 0;
		$this->unit = $unit;
		// default styles
		$this->css = array(
			'H1' => array(
				'SIZE' => $this->fontSize + 10,
				'LINE-HEIGHT' => $this->lineHeight + 2.5
			),
			'H2' => array(
				'SIZE' => $this->fontSize + 8,
				'LINE-HEIGHT' => $this->lineHeight + 2
			),
			'H3' => array(
				'SIZE' => $this->fontSize + 6,
				'LINE-HEIGHT' => $this->lineHeight + 1.5
			),
			'H4' => array(
				'SIZE' => $this->fontSize + 4,
				'LINE-HEIGHT' => $this->lineHeight + 1
			),
			'H5' => array(
				'SIZE' => $this->fontSize + 2,
				'LINE-HEIGHT' => $this->lineHeight + 0.5
			),
			'EM' => array(
				'COLOR' => '#FF5722'
			),
			'I' => array(
				'FONT-STYLE' => 'ITALIC'
			),
			'B' => array(
				'FONT-WEIGHT' => 'BOLD'
			),
			'STRONG' => array(
				'FONT-WEIGHT' => 'BOLD'
			),
			'U' => array(
				'TEXT-DECORATION' => 'UNDERLINE'
			),
			'A' => array(
				'TEXT-DECORATION' => 'UNDERLINE'
			),
			'BLOCKQUOTE' => array(
				'FONT-STYLE' => 'ITALIC'
			),
			'CODE' => array(
				'FONT-STYLE' => 'ITALIC'
			),
			'TABLE' => array(
				'BORDER-COLOR' => '#DDDDDD'
			),
			'TH' => array(
				'TEXT-ALIGN' => 'CENTER',
				'BACKGROUND-COLOR' => '#EEEEEE',
				'COLOR' => '#000000'
			),
			'TD' => array(
				'COLOR' => '#333333'
			)
		);
		// class style
		$this->cssClass = array(
			'COMMENT' => array(
				'SIZE' => $this->fontSize - 1,
				'COLOR' => '#259B24'
			),
			'CENTER' => array(
				'TEXT-ALIGN' => 'CENTER'
			),
			'LEFT' => array(
				'TEXT-ALIGN' => 'LEFT'
			),
			'RIGHT' => array(
				'TEXT-ALIGN' => 'RIGHT'
			),
			'BG2' => array(
				'BACKGROUND-COLOR' => '#F9F9F9'
			)
		);
	}

	/**
	 * กำหนดรูปแบบของ tag
	 *
	 * @param string $tag
	 * @param array $attributes
	 */
	public function SetStyles($tag, $attributes)
	{
		foreach ($attributes as $key => $value) {
			$this->css[strtoupper($tag)][strtoupper($key)] = $value;
		}
	}

	/**
	 * กำหนดรูปแบบของ class
	 *
	 * @param string $className
	 * @param array $attributes
	 */
	public function SetCssClass($className, $attributes)
	{
		foreach ($attributes as $key => $value) {
			$this->cssClass[strtoupper($className)][strtoupper($key)] = $value;
		}
	}

	/**
	 * สร้าง PDF จาก HTML โค้ด
	 * แสดงผลตามรูปแบบที่กำหนดโดย คชสาร
	 *
	 * @param string $html โค้ด HTML4
	 * @param string $charset default cp874 (ภาษาไทย)
	 */
	public function WriteHTML($html, $charset = 'cp874')
	{
		// parse HTML
		$dom = new DOMParser($html, $charset);
		// render
		foreach ($dom->nodes() as $node) {
			$this->render($node);
		}
	}

	/**
	 *
	 * @param DOMNode $node
	 * @return string
	 */
	protected function render($node)
	{
		if ($node->nodeName == '') {
			// โหนดข้อความ
			$lineHeight = empty($node->parentNode->attributes['LINE-HEIGHT']) ? $this->lineHeight : $node->parentNode->attributes['LINE-HEIGHT'];
			if ($node->parentNode && !$node->parentNode->isInlineElement() && sizeof($node->parentNode->childNodes) == 1) {
				// block node
				$align = empty($node->parentNode->attributes['TEXT-ALIGN']) ? '' : substr($node->parentNode->attributes['TEXT-ALIGN'], 0, 1);
				$this->MultiCell(0, $lineHeight, $node->unentities($node->nodeValue), 0, $align);
				$this->lastBlock = true;
			} else {
				// inline node
				if ($node->isInlineElement() && $node->previousSibling && !$node->previousSibling->isInlineElement()) {
					$this->Ln($lineHeight);
				}
				if ($this->link) {
					// link
					$this->Write($lineHeight, $node->unentities($node->nodeValue), $this->link);
				} else {
					// text
					$this->Write($lineHeight, $node->unentities($node->nodeValue));
				}
				$this->lastBlock = false;
			}
		} else {
			// open tag
			if ($node->nodeName == 'BR') {
				// ขึ้นบรรทัดใหม่
				$this->Ln($this->lineHeight);
			} elseif ($node->nodeName == 'IMG') {
				// รูปภาพ
				$this->drawImg($node);
			} elseif ($node->nodeName == 'HR') {
				// เส้นคั่น
				$this->drawHr($node);
			} elseif ($node->nodeName == 'TABLE') {
				// ตาราง
				$this->drawTable($node);
			} else {
				// ขึ้นบรรทัดใหม่
				if (!$this->lastBlock && !$node->isInlineElement()) {
					$this->Ln($this->lineHeight);
				}
				// link
				if (!empty($node->attributes['HREF'])) {
					$this->link = $node->attributes['HREF'];
				}
				// กำหนด CSS
				$this->applyCSS($node);
				// render โหนดลูก
				foreach ($node->childNodes as $child) {
					$this->render($child);
				}
				// ยกเลิก CSS
				$this->restoredCSS($node);
			}
		}
	}

	/**
	 * กำหนดรูปแบบของ tag
	 *
	 * @param DOMNode $node
	 */
	protected function applyCSS($node)
	{
		foreach ($this->css[$node->nodeName] as $key => $value) {
			$node->attributes[$key] = $value;
		}
		if (isset($node->attributes['CLASS'])) {
			foreach (explode(' ', $node->attributes['CLASS']) as $class) {
				foreach ($this->cssClass[strtoupper($class)] as $key => $value) {
					$node->attributes[$key] = $value;
				}
			}
			unset($node->attributes['CLASS']);
		}
		// ขนาดตัวอักษร
		if (isset($node->attributes['SIZE'])) {
			$node->FontSizePt = $this->FontSizePt;
			$this->SetFontSize($node->attributes['SIZE']);
		}
		// ตัวหนา
		if (isset($node->attributes['FONT-WEIGHT']) && $node->attributes['FONT-WEIGHT'] == 'BOLD') {
			$this->SetStyle('B', true);
		}
		// ตัวเอียง
		if (isset($node->attributes['FONT-STYLE']) && $node->attributes['FONT-STYLE'] == 'ITALIC') {
			$this->SetStyle('I', true);
		}
		// ขีดเส้นใต้
		if (isset($node->attributes['TEXT-DECORATION']) && $node->attributes['TEXT-DECORATION'] == 'UNDERLINE') {
			$this->SetStyle('U', true);
		}
		$node->ColorFlag = $this->ColorFlag;
		// สีตัวอักษร
		if (isset($node->attributes['COLOR'])) {
			$node->TextColor = $this->TextColor;
			list($r, $g, $b) = $this->colorToRGb($node->attributes['COLOR']);
			$this->SetTextColor($r, $g, $b);
		}
		// สีพื้น
		if (isset($node->attributes['BACKGROUND-COLOR'])) {
			$node->FillColor = $this->FillColor;
			list($r, $g, $b) = $this->colorToRGb($node->attributes['BACKGROUND-COLOR']);
			$this->SetFillColor($r, $g, $b);
		}
		// สีกรอบ
		if (isset($node->attributes['BORDER-COLOR'])) {
			$node->DrawColor = $this->DrawColor;
			list($r, $g, $b) = $this->colorToRGb($node->attributes['BORDER-COLOR']);
			$this->SetDrawColor($r, $g, $b);
		}
	}

	/**
	 * คืนค่ารูปแบบของ tag
	 *
	 * @param DOMNode $node
	 */
	protected function restoredCSS($node)
	{
		// สีกรอบ
		if (isset($node->attributes['BORDER-COLOR'])) {
			$this->DrawColor = $node->DrawColor;
		}
		// สีพื้น
		if (isset($node->attributes['BACKGROUND-COLOR'])) {
			$this->FillColor = $node->FillColor;
		}
		// สีตัวอักษร
		if (isset($node->attributes['COLOR'])) {
			$this->TextColor = $node->TextColor;
		}
		$this->ColorFlag = $node->ColorFlag;
		// ตัวหนา
		if (isset($node->attributes['FONT-WEIGHT']) && $node->attributes['FONT-WEIGHT'] == 'BOLD') {
			$this->SetStyle('B', false);
		}
		// ตัวเอียง
		if (isset($node->attributes['FONT-STYLE']) && $node->attributes['FONT-STYLE'] == 'ITALIC') {
			$this->SetStyle('I', false);
		}
		// ขีดเส้นใต้
		if (isset($node->attributes['TEXT-DECORATION']) && $node->attributes['TEXT-DECORATION'] == 'UNDERLINE') {
			$this->SetStyle('U', false);
		}
		// ขนาดตัวอักษร
		if (isset($node->attributes['SIZE'])) {
			$this->SetFontSize($node->FontSizePt);
		}
	}

	/**
	 * แสดงผลตัวหนา ตัวเอียง ขีดเส้นใต้
	 *
	 * @param string $style B I หรือ U
	 * @param boolean $enable true เปิดใช้งาน, false ปิดใช้งาน
	 */
	protected function SetStyle($style, $enable)
	{
		$this->$style += ($enable ? 1 : -1);
		$font_style = '';
		foreach (array('B', 'I', 'U') as $s) {
			if ($this->$s > 0) {
				$font_style .= $s;
			}
		}
		$this->SetFont('', $font_style);
	}

	/**
	 * คำนวนขนาด
	 *
	 * @param int|string $size ขนาด เช่น 100%, 20px
	 * @param int $max_size ขนาดที่ 100%
	 * @return int
	 */
	protected function calculateSize($size, $max_size)
	{
		if (preg_match('/^([0-9]+)(px|pt|mm|cm|in|\%)?$/', strtolower($size), $match)) {
			if ($match[2] == '%') {
				return ($max_size * (int)$match[1]) / 100;
			} else {
				return (int)$match[1];
			}
		}
		return (int)$size;
	}

	/**
	 * เส้นคั่น
	 *
	 * @param DOMNode $node
	 */
	protected function drawHr($node)
	{
		// ขึ้นบรรทัดใหม่
		$this->Ln(2);
		// current position
		$x = $this->GetX();
		$y = $this->GetY();
		// client width
		$cw = $this->w - $this->lMargin - $this->rMargin;
		if (empty($node->attributes['WIDTH'])) {
			// width 100%
			$w = $cw;
		} else {
			// width จากที่กำหนดมา
			$w = $this->calculateSize($node->attributes['WIDTH'], $cw);
			if (!empty($node->attributes['ALIGN']) && $cw > $w) {
				switch (strtoupper($node->attributes['ALIGN'])) {
					case 'CENTER':
						$x = ($cw - $w) / 2;
						break;
					case 'RIGHT':
						$x = $cw - $w;
						break;
				}
			}
		}
		if (!empty($node->attributes['COLOR'])) {
			$node->DrawColor = $this->DrawColor;
			list($r, $g, $b) = $this->colorToRGb($node->attributes['COLOR']);
			$this->SetDrawColor($r, $g, $b);
		}
		$lineWidth = $this->LineWidth;
		$this->SetLineWidth(0.4);
		$this->Line($x, $y, $x + $w, $y);
		$this->SetLineWidth($lineWidth);
		if (!empty($node->attributes['COLOR'])) {
			$this->DrawColor = $node->DrawColor;
		}
		// ขึ้นบรรทัดใหม่
		$this->Ln(2);
	}

	/**
	 * แสดงรูปภาพ
	 *
	 * @param DOMNode $node
	 */
	protected function drawImg($node)
	{
		if (isset($node->attributes['SRC'])) {
			list($left, $top, $width, $height) = $this->resizeImage($node);
			if ($node->parentNode->nodeName == 'FIGURE') {
				$this->Image($node->attributes['SRC'], $left, $top, $width, $height);
			} else {
				if ($node->isInlineElement() && $node->previousSibling && !$node->previousSibling->isInlineElement()) {
					// ขึ้นบรรทัดใหม่
					$x = $this->lMargin;
					$y = $this->y + $this->lineHeight;
				} else {
					// get current X and Y
					$x = $this->GetX();
					$y = $this->GetY();
				}

				$this->Image($node->attributes['SRC'], $x, $y);
				$this->SetXY($x + $width, $y);
			}
		}
	}

	/**
	 * คำนวนตำแหน่งและปรับขนาดของรูปภาพ คืนค่าขนาดและตำปหน่งของรูปภาพ
	 * ถ้ารูปภาพมีขนาดใหญ่กว่าพิ้นที่แสดงผลจะปรับขนาด
	 * ถ้ารูปภาพมีขนาดเล็กกว่าพิ้นที่แสดงผล จะแสดงขนาดเดิม ตามตำแหน่งที่กำหนด
	 *
	 * @param DOMNode $node tag IMG
	 * @return array array(left, top, width, height) top เป็น null เสมอ
	 */
	protected function resizeImage($node)
	{
		list($width, $height) = getimagesize($node->attributes['SRC']);
		if ($width < $this->wPt && $height < $this->hPt) {
			$k = 72 / 96 / $this->k;
			$l = null;
			if (isset($node->parentNode->attributes['TEXT-ALIGN'])) {
				switch ($node->parentNode->attributes['TEXT-ALIGN']) {
					case 'CENTER':
						$l = ($this->w - ($width * $k)) / 2;
						break;
					case 'RIGHT':
						$l = ($this->w - ($width * $k));
						break;
				}
			}
			return array($l, null, $width * $k, $height * $k);
		} else {
			$ws = $this->wPt / $width;
			$hs = $this->hPt / $height;
			$scale = min($ws, $hs);
			if ($this->unit == 'pt') {
				$k = 1;
			} elseif ($this->unit == 'mm') {
				$k = 25.4 / 72;
			} elseif ($this->unit == 'cm') {
				$k = 2.54 / 72;
			} elseif ($this->unit == 'in') {
				$k = 1 / 72;
			}
			return array(null, null, ((($scale * $width) - 56.7) * $k), ((($scale * $height) - 56.7) * $k));
		}
	}

	/**
	 * แปลงค่าสี HTML hex เช่น #FF0000 เป็นค่าสี RGB
	 *
	 * @param string $color ค่าสี HTML hex เช่น #FF0000
	 * @return array คืนค่า array($r, $g, $b) เช่น #FF0000 = array(255, 0, 0)
	 */
	protected function colorToRGb($color)
	{
		return array(
			hexdec(substr($color, 1, 2)),
			hexdec(substr($color, 3, 2)),
			hexdec(substr($color, 5, 2))
		);
	}

	/**
	 * แสดงตาราง
	 *
	 * @param DOMNode $table
	 */
	protected function drawTable($table)
	{
		// คำนวณความกว้างของ Cell
		$columnSizes = $this->calculateColumnsWidth($table);
		// CSS ของตาราง
		$this->applyCSS($table);
		// line-height
		$lineHeight = $this->lineHeight + 2;
		// thead, tbody, tfoot
		foreach ($table->childNodes as $table_group) {
			foreach ($table_group->childNodes as $tr) {
				// คำนวณความสูงของแถว
				$h = 0;
				foreach ($tr->childNodes as $col => $td) {
					$td->attributes['CLASS'] = trim($td->attributes['CLASS'].' '.$tr->attributes['CLASS']);
					$h = max($h, $this->NbLines($columnSizes[$col], $td->nodeValue));
				}
				$h = $h * $lineHeight;
				// ตรวจสอบการแบ่งหน้า
				$this->CheckPageBreak($h);
				// แสดงผล
				$y = $this->y;
				foreach ($tr->childNodes as $col => $td) {
					$this->applyCSS($td);
					$align = 'L';
					if (!empty($td->attributes['TEXT-ALIGN'])) {
						$align = strtoupper(substr($td->attributes['TEXT-ALIGN'], 0, 1));
					}
					// current x
					$x = $this->x;
					// bg & border
					$this->Cell($columnSizes[$col], $h, '', 1, 0, '', !empty($td->attributes['BACKGROUND-COLOR']));
					// restore position
					$this->x = $x;
					$this->y = $y;
					// draw text
					$this->MultiCell($columnSizes[$col], $lineHeight, $td->nodeValue, 0, $align);
					// next cell
					$this->x = $x + $columnSizes[$col];
					$this->y = $y;
					// คืนค่า CSS ของ cell กลับเป็นค่าเดิม
					$this->restoredCSS($td);
				}
				$this->SetXY($this->lMargin, $y + $h);
			}
		}
		// คืนค่า CSS ของตารางกลับเป็นค่าเดิม
		$this->restoredCSS($table);
	}

	/**
	 * คำนวนขนาดของคอลัมน์เป็น %
	 *
	 * @param DOMNode $table
	 * @return array
	 */
	protected function calculateColumnsWidth($table)
	{
		// page width
		$cw = $this->w - $this->lMargin - $this->rMargin;
		// คำนวณขนาดของตาราง
		if (!empty($table->attributes['CLASS'])) {
			foreach (explode(' ', $table->attributes['CLASS']) as $class) {
				switch (strtoupper($class)) {
					case 'FULLWIDTH':
						// class=fullwidth
						$table_width = $cw;
						break;
				}
			}
		}
		if (!empty($table->attributes['WIDTH'])) {
			// ความกว้างของตาราง width=xxx
			$table_width = $this->calculateSize($table->attributes['WIDTH'], $cw);
		}
		$columnSizes = array();
		foreach ($table->childNodes as $child) {
			foreach ($child->childNodes as $tr) {
				foreach ($tr->childNodes as $col => $td) {
					// อ่านข้อความใส่ลงในโหนด
					$td->nodeValue = $td->nodeText();
					// คำนวณความกว้างของข้อความ
					$td->textWidth = $this->GetStringWidth($td->nodeValue);
					// ลบโหนดลูกออก
					unset($td->childNodes);
					// ความกว้างของ cell
					$length = isset($table_width) && !empty($td->attributes['WIDTH']) ? $this->calculateSize($td->attributes['WIDTH'], $table_width) : $td->textWidth;
					$columnSizes[$col]['max'] = !isset($columnSizes[$col]['max']) ? $length : ($columnSizes[$col]['max'] < $length ? $length : $columnSizes[$col]['max']);
					$columnSizes[$col]['avg'] = !isset($columnSizes[$col]['avg']) ? $length : $columnSizes[$col]['avg'] + $length;
					$columnSizes[$col]['raw'][] = $length;
				}
			}
		}
		$columnSizes = array_map(function ($columnSize) {
			$columnSize['avg'] = $columnSize['avg'] / sizeof($columnSize['raw']);
			return $columnSize;
		}, $columnSizes);
		foreach ($columnSizes as $key => $columnSize) {
			$colMaxSize = $columnSize['max'];
			$colAvgSize = $columnSize['avg'];
			$stdDeviation = $this->sd($columnSize['raw']);
			$coefficientVariation = $stdDeviation / $colAvgSize;
			$columnSizes[$key]['cv'] = $coefficientVariation;
			$columnSizes[$key]['stdd'] = $stdDeviation;
			$columnSizes[$key]['stdd/max'] = $stdDeviation / $colMaxSize;
			if (($columnSizes[$key]['stdd/max'] < 0.3 || $coefficientVariation == 1) && ($coefficientVariation == 0 || ($coefficientVariation > 0.6 && $coefficientVariation < 1.5))) {
				$columnSizes[$key]['calc'] = $colAvgSize;
			} else {
				if ($coefficientVariation > 1 && $columnSizes[$key]['stdd'] > 4.5 && $columnSizes[$key]['stdd/max'] > 0.2) {
					$tmp = ($colMaxSize - $colAvgSize) / 2;
				} else {
					$tmp = 0;
				}
				$columnSizes[$key]['calc'] = $colAvgSize + ($colMaxSize / $colAvgSize) * 2 / abs(1 - $coefficientVariation);
				$columnSizes[$key]['calc'] = $columnSizes[$key]['calc'] > $colMaxSize ? $colMaxSize - $tmp : $columnSizes[$key]['calc'];
			}
		}
		$totalCalculatedSize = 0;
		foreach ($columnSizes as $columnSize) {
			$totalCalculatedSize += $columnSize['calc'];
		}
		$result = array();
		foreach ($columnSizes as $key => $columnSize) {
			if (empty($table_width)) {
				$result[$key] = 100 / ($totalCalculatedSize / $columnSize['calc']);
			} else {
				$result[$key] = ($columnSize['calc'] * $table_width) / $totalCalculatedSize;
			}
		}
		return $result;
	}

	/**
	 * calculate standard deviation.
	 *
	 * @param $array
	 * @return float
	 */
	protected function sd($array)
	{
		if (sizeof($array) == 1) {
			return 1.0;
		}
		$sd_square = function ($x, $mean) {
			return pow($x - $mean, 2);
		};
		return sqrt(array_sum(array_map($sd_square, $array, array_fill(0, sizeof($array), (array_sum($array) / sizeof($array))))) / (sizeof($array) - 1));
	}

	/**
	 * คำนวณความสูงของเซล
	 *
	 * @param int $w
	 * @param string $txt
	 * @return int
	 */
	protected function NbLines($w, $txt)
	{
		$cw = &$this->CurrentFont['cw'];
		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}
		$wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
		$s = str_replace("\r", '', $txt);
		$nb = strlen($s);
		if ($nb > 0 && $s[$nb - 1] == "\n") {
			$nb--;
		}
		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$nl = 1;
		while ($i < $nb) {
			$c = $s[$i];
			if ($c == "\n") {
				$i++;
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
				continue;
			}
			if ($c == ' ') {
				$sep = $i;
			}
			$l+=$cw[$c];
			if ($l > $wmax) {
				if ($sep == -1) {
					if ($i == $j) {
						$i++;
					}
				} else {
					$i = $sep + 1;
				}
				$sep = -1;
				$j = $i;
				$l = 0;
				$nl++;
			} else {
				$i++;
			}
		}
		return $nl;
	}

	/**
	 * ตรวจสอบความสูงของตาราง ถ้าความสูงของตารางเกินหน้า
	 * จะขึ้นหน้าใหม่
	 *
	 * @param int $h ความสูงของตาราง
	 */
	protected function CheckPageBreak($h)
	{
		if ($this->GetY() + $h > $this->PageBreakTrigger) {
			$this->AddPage($this->CurOrientation);
		}
	}
}