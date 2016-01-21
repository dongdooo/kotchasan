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
	 * ไฟล์อัปโหลด
	 *
	 * @param string $path ไฟล์อัปโหลด รวมพาธ
	 * @param string $originalName ชื่อไฟล์ที่อัปโหลด
	 * @param string $mimeType MIME Type
	 * @param int $size ขนาดไฟล์อัปโหลด
	 * @param int $error ข้อผิดพลาดการอัปโหลด UPLOAD_ERR_XXX
	 */
	public function __construct($path, $originalName, $mimeType = null, $size = null, $error = null)
	{
		$this->tmp_name = $path;
		$this->name = $originalName;
		$this->mime = $mimeType;
		$this->size = $size;
		$this->error = $error;
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
			throw new \RuntimeException(sprintf('Uploaded file %1s has already been moved', $this->name));
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
	 * @throws \InvalidArgumentException ข้อผิดพลาดหากที่อยู่ปลายทางไม่สามารถเขียนได้
	 * @throws \RuntimeException ข้อผิดพลาดการอัปโหลด
	 */
	public function moveTo($targetPath)
	{
		if ($this->isMoved) {
			throw new \RuntimeException(sprintf('Uploaded file %1s has already been moved', $this->name));
		}
		if (strpos($targetPath, '://') > 0) {
			if (!copy($this->tmp_name, $targetPath)) {
				throw new \RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
			}
			if (!unlink($this->tmp_name)) {
				throw new \RuntimeException(sprintf('Error removing uploaded file %1s', $this->name));
			}
		} else {
			if (!is_writable(dirname($targetPath))) {
				throw new \InvalidArgumentException('Upload target path is not writable');
			}
			if (!move_uploaded_file($this->tmp_name, $targetPath)) {
				throw new \RuntimeException(sprintf('Error moving uploaded file %1s to %2s', $this->name, $targetPath));
			}
		}
		$this->isMoved = true;
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
}