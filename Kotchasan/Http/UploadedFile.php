<?php
/*
 * @filesource Kotchasan/Http/UploadedFile.php
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */

namespace Kotchasan\Http;

use \Psr\Http\Message\UploadedFileInterface;
use \Kotchasan\Http\Stream;
use \Kotchasan\Image;
use \Kotchasan\Language;

/**
 * Class สำหรับจัดการไฟล์อัปโหลด
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class UploadedFile implements UploadedFileInterface
{
	/**
	 * ไฟล์อัปโหลด รวมพาธ
	 *
	 * @var string
	 */
	private $tmp_name;
	/**
	 * ชื่อไฟล์ที่อัปโหลด
	 *
	 * @var string
	 */
	private $name;
	/**
	 * MIME Type
	 *
	 * @var string
	 */
	private $mime;
	/**
	 * ขนาดไฟล์อัปโหลด
	 *
	 * @var int
	 */
	private $size;
	/**
	 * ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX
	 *
	 * @var int
	 */
	private $error;
	/**
	 * นามสกุลของไฟล์อัปโหลด
	 *
	 * @var string
	 */
	private $ext;
	/**
	 * file stream
	 *
	 * @var Stream
	 */
	private $stream;
	/**
	 * ใช้สำหรับบอกว่ามีการย้ายไฟล์ไปแล้ว
	 *
	 * @var bool
	 */
	private $isMoved = false;
	/**
	 *  Indicates if the upload is from a SAPI environment.
	 *
	 * @var bool
	 */
	private $sapi = false;

	/**
	 * ไฟล์อัปโหลด
	 *
	 * @param string $path ไฟล์อัปโหลด รวมพาธ
	 * @param string $originalName ชื่อไฟล์ที่อัปโหลด
	 * @param string $mimeType MIME Type
	 * @param int $size ขนาดไฟล์อัปโหลด
	 * @param int $error ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX
	 * @param bool $sapi Indicates if the upload is in a SAPI environment.
	 */
	public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null, $sapi = false)
	{
		$this->tmp_name = $path;
		$this->name = $originalName;
		$this->mime = $mimeType;
		$this->size = $size;
		$this->error = $error;
		$this->sapi = $sapi;
	}

	/**
	 * ส่งออกไฟล์อัปโหลดเป็น Stream
	 *
	 * @return StreamInterface
	 * @throws \RuntimeException ถ้าไม่พบไฟล์
	 */
	public function getStream()
	{
		if (!is_file($this->tmp_name)) {
			throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
		}
		if ($this->stream === null) {
			$this->stream = new Stream($this->tmp_name);
		}
		return $this->stream;
	}

	/**
	 * ย้ายไฟล์อัปโหลดไปยังที่อยู่ใหม่
	 *
	 * @param string $targetPath ที่อยู่ปลายทางที่ต้องการย้าย
	 * @return bool true ถ้าอัปโหลดเรียบร้อย
	 * @throws \InvalidArgumentException ข้อผิดพลาดหากที่อยู่ปลายทางไม่สามารถเขียนได้
	 * @throws \RuntimeException ข้อผิดพลาดการอัปโหลด
	 */
	public function moveTo($targetPath)
	{
		if ($this->isMoved) {
			throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
		}
		if (!is_writable(dirname($targetPath))) {
			throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
		}
		if (strpos($targetPath, '://') > 0) {
			if (!copy($this->tmp_name, $targetPath)) {
				throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
			}
			if (!unlink($this->tmp_name)) {
				throw new \RuntimeException(sprintf(Language::get('Error removing uploaded file %1s'), $this->name));
			}
		} elseif ($this->sapi) {
			if (!move_uploaded_file($this->tmp_name, $targetPath)) {
				throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
			}
		} else {
			if (!rename($this->tmp_name, $targetPath)) {
				throw new \RuntimeException(sprintf(Language::get('Error moving uploaded file %1s to %2s'), $this->name, $targetPath));
			}
		}
		$this->isMoved = true;
		return true;
	}

	/**
	 * อ่านขนาดของไฟล์อัปโหลด
	 *
	 * @return int|null
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * อ่านข้อผิดพลาดของไฟล์อัปโหลด
	 *
	 * @return int คืนค่า UPLOAD_ERR_XXX
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * อ่านชื่อไฟล์ (ต้นฉบับ) ของไฟล์ที่อัปโหลด
	 *
	 * @return string|null
	 */
	public function getClientFilename()
	{
		return $this->name;
	}

	/**
	 * อ่าน MIME Type ของไฟล์
	 *
	 * @return string|null
	 */
	public function getClientMediaType()
	{
		return $this->mime;
	}

	/**
	 * อ่านนามสกุลของไฟล์อัปโหลด
	 *
	 * @return string คืนค่าตัวพิมพ์เล็ก เช่น jpg
	 */
	public function getClientFileExt()
	{
		if ($this->ext == null) {
			$exts = explode('.', $this->name);
			$this->ext = strtolower(end($exts));
		}
		return $this->ext;
	}

	/**
	 * ตรวจสอบนามสกุลของไฟล์อัปโหลด
	 *
	 * @param array $exts รายการนามสกุลของไฟล์อัปโหลดที่ยอมรับ เช่น [jpg, gif, png]
	 * @return bool คืนค่า true ถ้านามสกุลของไฟล์อัปโหลดอยู่ใน $exts
	 */
	public function validFileExt($exts)
	{
		return in_array($this->getClientFileExt(), $exts);
	}

	/**
	 * ตรวจสอบไฟล์อัปโหลด
	 *
	 * @return bool คืนค่า true ถ้ามีไฟล์อัปโหลด
	 */
	public function hasUploadFile()
	{
		return $this->error == UPLOAD_ERR_OK;
	}

	/**
	 * อ่านการตั้งค่าขนาดของไฟลอัปโหลด
	 *
	 * @return string
	 */
	public static function getUploadSize()
	{
		return ini_get('upload_max_filesize');
	}

	/**
	 * สำเนาไฟล์อัปโหลดไปยังที่อยู่ใหม่
	 *
	 * @param string $targetPath ที่อยู่ปลายทางที่ต้องการย้าย
	 * @return bool true ถ้าอัปโหลดเรียบร้อย
	 * @throws \RuntimeException ข้อผิดพลาดการอัปโหลด
	 * @throws \InvalidArgumentException ไดเรคทอรี่ไม่สามารถเขียนได้
	 */
	public function copyTo($targetPath)
	{
		if ($this->isMoved) {
			throw new \RuntimeException(sprintf(Language::get('Uploaded file %1s has already been moved'), $this->name));
		}
		if (!is_writable(dirname($targetPath))) {
			throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
		}
		if (!copy($this->tmp_name, $targetPath)) {
			throw new \RuntimeException(sprintf(Language::get('Error copying file %1s to %2s'), $this->name, $targetPath));
		}
		return true;
	}

	/**
	 * ฟังก์ชั่น ตัดรูปภาพ ตามขนาดที่กำหนด และย้ายไปยังปลายทาง
	 * รูปภาพปลายทางจะมีขนาดเท่าที่กำหนด หากรูปภาพต้นฉบับมีขนาดหรืออัตราส่วนไม่พอดีกับขนาดของภาพปลายทาง
	 * รูปภาพจะถูกตัดขอบออกหรือจะถูกขยาย เพื่อให้พอดีกับรูปภาพปลายทางที่ต้องการ
	 *
	 * @param array $exts นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
	 * @param string $targetPath path และชื่อไฟล์ของไฟล์รูปภาพปลายทาง
	 * @param int $width ความกว้างของรูปภาพที่ต้องการ
	 * @param int $height ความสูงของรูปภาพที่ต้องการ
	 * @param string $watermark (optional) ข้อความลายน้ำ
	 * @return bool|string สำเร็จคืนค่า true ไม่สำเร็จคืนค่าข้อความผิดพลาด
	 * @throws \InvalidArgumentException ข้อผิดพลาดหากที่อยู่ปลายทางไม่สามารถเขียนได้
	 * @throws \RuntimeException ข้อผิดพลาดไม่สามารถสร้างรูปภาพได้
	 */
	public function cropImage($exts, $targetPath, $width, $height, $watermark = '')
	{
		$this->check($exts, dirname($targetPath));
		$ret = Image::crop($this->tmp_name, $targetPath, $width, $height, $watermark);
		if ($ret === false) {
			throw new \RuntimeException(Language::get('Unable to create image'));
		}
		return true;
	}

	/**
	 * ปรับขนาดของรูปภาพอัปโหลด โดยรักษาอัตราส่วนของภาพตามความกว้างที่ต้องการ
	 * หากรูปภาพมีขนาดเล็กกว่าที่กำหนด จะเป็นการ copy file
	 * หากรูปภาพมาความสูง หรือความกว้างมากกว่า $width
	 * จะถูกปรับขนาดให้มีขนาดไม่เกิน $width (ทั้งความสูงและความกว้าง)
	 * และเปลี่ยนชนิดของภาพเป็น jpg
	 *
	 * @param array $exts นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
	 * @param string $target path ของไฟล์รูปภาพปลายทาง
	 * @param string $name ชื่อไฟล์ของรูปภาพปลายทาง
	 * @param int $width ขนาดสูงสุดของรูปภาพที่ต้องการ
	 * @param string $watermark (optional) ข้อความลายน้ำ
	 * @return array|bool คืนค่าแอเรย์ [name, width, height, mime] ของรูปภาพปลายทาง หรือ false ถ้าไม่สามารถดำเนินการได้
	 */
	public function resizeImage($exts, $target, $name, $width, $watermark = '')
	{
		$this->check($exts, $target);
		$ret = Image::resize($this->tmp_name, $target, $name, $width, $watermark);
		if ($ret === false) {
			throw new \RuntimeException(Language::get('Unable to create image'));
		} else {
			return $ret;
		}
	}

	/**
	 * ฟังชั่นตรวจสอบไฟล์อัปโหลด
	 *
	 * @param array $exts นามสกุลของไฟล์รูปภาพที่ยอมรับ เช่น [jpg, gif, png]
	 * @param string $targetDir ไดเรคทอรี่ปลายทาง
	 * @return bool คืนค่า true ถ้าสามารถอัปโหลดได้
	 * @throws \RuntimeException ถ้าชนิดของไฟล์อัปโหลดไม่ถูกต้อง
	 * @throws \InvalidArgumentException ถ้าไดเร็คทอรี่ไม่สามารถเขียนได้
	 */
	private function check($exts, $targetDir)
	{
		if (!$this->validFileExt($exts)) {
			throw new \RuntimeException(Language::get('The type of file is invalid'));
		}
		if (!is_writable($targetDir)) {
			throw new \InvalidArgumentException(Language::get('Target directory is not writable'));
		}
		return true;
	}
}