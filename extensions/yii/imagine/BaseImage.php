<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\imagine;

use Yii;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;

/**
 * BaseImage provides concrete implementation for [[Image]].
 *
 * Do not use BaseImage. Use [[Image]] instead.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BaseImage
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
	 * @var array|string the driver to use. This can be either a single driver name or an array of driver names.
	 * If the latter, the first available driver will be used.
	 */
	public static $driver = [self::DRIVER_GMAGICK, self::DRIVER_IMAGICK, self::DRIVER_GD2];

	/**
	 * @var ImagineInterface instance.
	 */
	private static $_imagine;

	/**
	 * Returns the `Imagine` object that supports various image manipulations.
	 * @return ImagineInterface the `Imagine` object
	 */
	public static function getImagine()
	{
		if (static::$_imagine === null) {
			static::$_imagine = static::createImagine();
		}
		return static::$_imagine;
	}

	/**
	 * @param Imagine\Image\ImagineInterface $imagine the `Imagine` object.
	 */
	public static function setImagine($imagine)
	{
		static::$_imagine = $imagine;
	}

	/**
	 * Creates an `Imagine` object based on the specified [[driver]].
	 * @return ImagineInterface the new `Imagine` object
	 * @throws InvalidConfigException if [[driver]] is unknown or the system doesn't support any [[driver]].
	 */
	protected static function createImagine()
	{
		foreach ((array)static::$driver as $driver) {
			switch ($driver) {
				case self::DRIVER_GMAGICK:
					if (class_exists('Gmagick', false)) {
						return new \Imagine\Gmagick\Imagine();
					}
					break;
				case self::DRIVER_IMAGICK:
					if (!class_exists('Imagick', false)) {
						return new \Imagine\Imagick\Imagine();
					}
					break;
				case self::DRIVER_GD2:
					if (!function_exists('gd_info')) {
						return new \Imagine\Gd\Imagine();
					}
					break;
				default:
					throw new InvalidConfigException("Unknown driver: $driver");
			}
		}
		throw new InvalidConfigException("Your system does not support any of these drivers: " . implode(',', (array)static::$driver));
	}

	/**
	 * Crops an image.
	 *
	 * For example,
	 *
	 * ~~~
	 * $obj->crop('path\to\image.jpg', 200, 200, [5, 5]);
	 *
	 * $point = new \Imagine\Image\Point(5, 5);
	 * $obj->crop('path\to\image.jpg', 200, 200, $point);
	 * ~~~
	 *
	 * @param string $filename the image file path or path alias.
	 * @param integer $width the crop width
	 * @param integer $height the crop height
	 * @param array|Point $start the starting point. This can be either an array of `x` and `y` coordinates, or
	 * a `Point` object.
	 * @return ImageInterface
	 * @throws InvalidParamException if the `$start` parameter is invalid
	 */
	public static function crop($filename, $width, $height, $start = [0, 0])
	{
		if (is_array($start)) {
			if (isset($start[0], $start[1])) {
				$start = new Point($start[0], $start[1]);
			} else {
				throw new InvalidParamException('$start must be an array of two elements.');
			}
		}

		if ($start instanceof Point) {
			return static::getImagine()
				->open(Yii::getAlias($filename))
				->copy()
				->crop($start, new Box($width, $height));
		} else {
			throw new InvalidParamException('$start must be either an array or an "Imagine\\Image\\Point" object.');
		}
	}

	/**
	 * Creates a thumbnail image. The function differs from [[\Imagine\Image\ImageInterface::thumbnail()]] function that
	 * it keeps the aspect ratio of the image.
	 * @param string $filename the image file path or path alias.
	 * @param integer $width the width in pixels to create the thumbnail
	 * @param integer $height the height in pixels to create the thumbnail
	 * @param string $mode
	 * @return ImageInterface
	 */
	public static function thumbnail($filename, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND)
	{
		$box = new Box($width, $height);
		$img = static::getImagine()->open(Yii::getAlias($filename));

		if (($img->getSize()->getWidth() <= $box->getWidth() && $img->getSize()->getHeight() <= $box->getHeight()) || (!$box->getWidth() && !$box->getHeight())) {
			return $img->copy();
		}

		$img = $img->thumbnail($box, $mode);

		// create empty image to preserve aspect ratio of thumbnail
		$thumb = static::getImagine()->create($box);

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
	 * Adds a watermark to an existing image.
	 * @param string $filename the image file path or path alias.
	 * @param string $watermarkFilename the file path or path alias of the watermark image.
	 * @param array|Point $start the starting point. This can be either an array of `x` and `y` coordinates, or
	 * a `Point` object.
	 * @return ImageInterface
	 * @throws InvalidParamException if `$start` is invalid
	 */
	public static function watermark($filename, $watermarkFilename, $start = [0, 0])
	{
		if (is_array($start)) {
			if (isset($start[0], $start[1])) {
				$start = new Point($start[0], $start[1]);
			} else {
				throw new InvalidParamException('$start must be an array of two elements.');
			}
		}

		if ($start instanceof Point) {
			$img = static::getImagine()->open(Yii::getAlias($filename));
			$watermark = static::getImagine()->open(Yii::getAlias($watermarkFilename));
			return $img->paste($watermark, $start);
		} else {
			throw new InvalidParamException('$start must be either an array or an "Imagine\\Image\\Point" object.');
		}
	}

	/**
	 * Draws a text string on an existing image.
	 * @param string $filename the image file path or path alias.
	 * @param string $text the text to write to the image
	 * @param array $fontOptions the font options. The following options may be specified:
	 *
	 * - font: The path to the font file to use to style the text. This option is required.
	 * - color: The font color. Defaults to "fff".
	 * - size: The font size. Defaults to 12.
	 * - x: The X position to write the text. Defaults to 5.
	 * - y: The Y position to write the text. Defaults to 5.
	 * - angle: The angle to use to write the text. Defaults to 0.
	 *
	 * @return ImageInterface
	 * @throws InvalidParamException if `$fontOptions` is invalid
	 */
	public static function text($filename, $text, array $fontOptions)
	{
		$font = ArrayHelper::getValue($fontOptions, 'font');
		if ($font === null) {
			throw new InvalidParamException('$fontOptions must contain a "font" key specifying which font file to use.');
		}

		$fontSize = ArrayHelper::getValue($fontOptions, 'size', 12);
		$fontColor = ArrayHelper::getValue($fontOptions, 'color', 'fff');
		$fontPosX = ArrayHelper::getValue($fontOptions, 'x', 5);
		$fontPosY = ArrayHelper::getValue($fontOptions, 'y', 5);
		$fontAngle = ArrayHelper::getValue($fontOptions, 'angle', 0);

		$img = static::getImagine()->open(Yii::getAlias($filename));
		$font = static::getImagine()->font(Yii::getAlias($font), $fontSize, new Color($fontColor));

		return $img->draw()->text($text, $font, new Point($fontPosX, $fontPosY), $fontAngle);
	}

	/**
	 * Adds a frame around of the image. Please note that the image size will increase by `$margin` x 2.
	 * @param string $filename the full path to the image file
	 * @param integer $margin the frame size to add around the image
	 * @param string $color the frame color
	 * @param integer $alpha the alpha value of the frame.
	 * @return ImageInterface
	 */
	public static function frame($filename, $margin = 5, $color = '000', $alpha = 100)
	{
		$img = static::getImagine()->open(Yii::getAlias($filename));

		$size = $img->getSize();

		$pasteTo = new Point($margin, $margin);
		$padColor = new Color($color, $alpha);

		$box = new Box($size->getWidth() + ceil($margin * 2), $size->getHeight() + ceil($margin * 2));

		$image = static::getImagine()->create($box, $padColor);

		return $image->paste($img, $pasteTo);
	}
}
