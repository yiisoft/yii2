<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use yii\base\InvalidConfigException;

/**
 * ImagickDriver renders the CAPTCHA image based on the code using [ImageMagick](http://php.net/manual/en/book.imagick.php) library.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.1.0
 */
class ImagickDriver extends Driver
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!extension_loaded('imagick') || !in_array('PNG', (new \Imagick())->queryFormats('PNG'), true)) {
            throw new InvalidConfigException('ImageMagick PHP extension with PNG support is required.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderImage($code)
    {
        $backColor = $this->transparent ? new \ImagickPixel('transparent') : new \ImagickPixel('#' . str_pad(dechex($this->backColor), 6, 0, STR_PAD_LEFT));
        $foreColor = new \ImagickPixel('#' . str_pad(dechex($this->foreColor), 6, 0, STR_PAD_LEFT));

        $image = new \Imagick();
        $image->newImage($this->width, $this->height, $backColor);

        $draw = new \ImagickDraw();
        $draw->setFont($this->fontFile);
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int) $fontMetrics['textWidth'] - 8 + $this->offset * ($length - 1);
        $h = (int) $fontMetrics['textHeight'] - 8;
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $draw = new \ImagickDraw();
            $draw->setFont($this->fontFile);
            $draw->setFontSize((int) (mt_rand(26, 32) * $scale * 0.8));
            $draw->setFillColor($foreColor);
            $image->annotateImage($draw, $x, $y, mt_rand(-10, 10), $code[$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code[$i]);
            $x += (int) $fontMetrics['textWidth'] + $this->offset;
        }

        $image->setImageFormat('png');
        return $image->getImageBlob();
    }
}