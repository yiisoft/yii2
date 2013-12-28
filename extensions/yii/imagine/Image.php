<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\imagine;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Image implements most common image manipulation functions using Imagine library.
 *
 * To use Image, you should configure it in the application configuration like the following,
 *
 * ~~~
 * 'components' => [
 *     ...
 *     'image' => [
 *         'class' => 'yii\imagine\Image',
 *         'driver' => \yii\imagine\Image::DRIVER_GD2,
 *     ],
 *     ...
 * ],
 * ~~~
 *
 * But you can also use it directly,
 *
 * ~~~
 * use yii\imagine\Image;
 *
 * $img = new Image();
 * ~~~
 *
 * Example of use:
 *
 * ~~~
 * // thumb - saved on runtime path
 * $imagePath = Yii::$app->getBasePath() . '/web/img/test-image.jpg';
 * $runtimePath = Yii::$app->getRuntimePath();
 * Yii::$app->image
 * 	->thumb($imagePath, 120, 120)
 * 	->save($runtime . '/thumb-test-image.jpg', ['quality' => 50]);
 * ~~~
 *
 *
 * @see http://imagine.readthedocs.org/
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @since 2.0
 */
class Image extends Component
{
	/**
	 * GD2 driver definition for Imagine implementation using the GD library.
	 */
	const DRIVER_GD2 = 'gd2';
	/**
	 * imagick driver definition.
	 */
	const DRIVER_IMAGICK = 'imagick';
	/**
	 * gmagick driver definition.
	 */
	const DRIVER_GMAGICK = 'gmagick';
	/**
	 * @var \Imagine\Image\ImagineInterface instance.
	 */
	private $_imagine;
	/**
	 * @var string the driver to use. These can be:
	 * - gd2
	 * - imagick
	 * - gmagick
	 */
	private $_driver = "gd2";

	/**
	 * Sets the driver.
	 * @param $driver
	 * @throws \yii\base\InvalidConfigException
	 */
	public function setDriver($driver)
	{
		if (!is_string($driver) || !in_array($driver, $this->getAvailableDrivers(), true)) {
			throw new InvalidConfigException(
				strtr('"{class}::driver" should be string of these possible options "{drivers}", "{driver}" given.', [
					'{class}' => get_class($this),
					'{drivers}' => implode(', ', $this->getAvailableDrivers()),
					'{driver}' => $driver
				]));
		}
		$this->_driver = $driver;
	}

	/**
	 * Returns the driver which is going to be used for \Imagine\Image\ImagineInterface instance creation.
	 * @return string the driver used.
	 */
	public function getDriver()
	{
		return $this->_driver;
	}

	/**
	 * @return array of available drivers.
	 */
	public function getAvailableDrivers()
	{
		static $drivers;
		if ($drivers === null) {
			$drivers = [static::DRIVER_GD2, static::DRIVER_GMAGICK, static::DRIVER_IMAGICK];
		}
		return $drivers;
	}

	/**
	 * @return \Imagine\Image\ImagineInterface instance
	 */
	public function getImagine()
	{
		if ($this->_imagine === null) {
			switch ($this->_driver) {
				case static::DRIVER_GD2:
					$this->_imagine = new \Imagine\Gd\Imagine();
					break;
				case static::DRIVER_IMAGICK:
					$this->_imagine = new \Imagine\Imagick\Imagine();
					break;
				case static::DRIVER_GMAGICK:
					$this->_imagine = new \Imagine\Gmagick\Imagine();
					break;
			}
		}
		return $this->_imagine;
	}

	/**
	 * Crops an image
	 * @param string $filename the full path to the image file
	 * @param int $width the crop width
	 * @param int $height the crop height
	 * @param int $startX the x starting point to crop from. Defaults to 0.
	 * @param int $startY the y starting point to crop from. Defaults to 0.
	 * @return \Imagine\Image\ManipulatorInterface
	 */
	public function crop($filename, $width, $height, $startX = 0, $startY = 0)
	{
		return $this->getImagine()
			->open($filename)
			->copy()
			->crop(new Point($startX, $startY), new Box($width, $height));
	}

	/**
	 * Creates a thumbnail image. The function differs from [[\Imagine\Image\ImageInterface::thumbnail()]] function that
	 * it keeps the aspect ratio of the image.
	 * @param string $filename the full path to the image file
	 * @param int $width the width to create the thumbnail
	 * @param int $height the height in pixels to create the thumbnail
	 * @param string $mode
	 * @return \Imagine\Image\ImageInterface|ManipulatorInterface
	 */
	public function thumb($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
	{
		$box = new Box($width, $height);
		$img = $this->getImagine()
			->open($filename);

		if(($img->getSize()->getWidth() <= $box->getWidth() && $img->getSize()->getHeight() <= $box->getHeight())
			|| (!$box->getWidth() && !$box->getHeight())) {
			return $img->copy();
		}
		$img = $img->thumbnail($box, $mode);

		// create empty image to preserve aspect ratio of thumbnail
		$thumb = $this->getImagine()
			->create($box);

		// calculate points
		$size = $img->getSize();

		$startX = 0;
		$startY = 0;
		if ($size->getWidth() < $width) {
			$startX = ceil($width - $size->getWidth()) / 2;
		}
		if ($size->getHeight() < $height) {
			$startY = ceil($height - $size->getHeight()) / 2;
		}

		$thumb->paste($img, new Point($startX, $startY));

		return $thumb;
	}

	/**
	 * Paste a watermark image onto another.
	 * @param string $filename the full path to the image file to apply the watermark to
	 * @param string $watermarkFilename the full path to the image file to apply as watermark
	 * @param Point $pos the pixel position where to apply the watermark. If null, bottomRight will be default.
	 * @return ManipulatorInterface
	 */
	public function watermark($filename, $watermarkFilename, Point $pos = null)
	{
		$img = $this->getImagine()->open($filename);
		$watermark = $this->getImagine()->open($watermarkFilename);

		$size = $img->getSize();
		$wSize = $watermark->getSize();

		// if no Point position was given, set its bottom right by default
		$pos = $pos === null ?
			new Point($size->getWidth() - $wSize->getWidth() , $size->getHeight() - $wSize->getHeight())
			: $pos;

		return $img->paste($watermark, $pos);
	}

	/**
	 * Draws text to an image.
	 * @param string $filename the full path to the image file
	 * @param string $text the text to write to the image
	 * @param array $fontConfig the font configuration. The font configuration holds the following keys:
	 * - font: The path to the font file to use to style the text. Required parameter.
	 * - size: The font size. Defaults to 12.
	 * - posX: The X position to write the text. Defaults to 5.
	 * - posY: The Y position to write the text. Defaults to 5.
	 * - angle: The angle to use to write the text. Defaults to 0.
	 * @return \Imagine\Image\ImageInterface
	 * @throws \Imagine\Exception\InvalidArgumentException
	 */
	public function text($filename, $text, array $fontConfig)
	{
		$img = $this->getImagine()->open($filename);

		$font = ArrayHelper::getValue($fontConfig, 'font');
		if ($font === null) {
			throw new InvalidArgumentException('"' . get_class($this) .
				'::text()" "$fontConfig" parameter should contain a "font" key with the path to the font file to use.');
		}
		$fontSize = ArrayHelper::getValue($fontConfig, 'size', 12);
		$fontColor = ArrayHelper::getValue($fontConfig, 'color', 'fff');
		$fontPosX = ArrayHelper::getValue($fontConfig, 'posX', 5);
		$fontPosY = ArrayHelper::getValue($fontConfig, 'posY', 5);
		$fontAngle = ArrayHelper::getValue($fontConfig, 'angle', 0);

		$font = $this->getImagine()->font($font, $fontSize, new Color($fontColor));
		$img->draw()->text($text, $font, new Point($fontPosX, $fontPosY), $fontAngle);
		return $img;
	}
}