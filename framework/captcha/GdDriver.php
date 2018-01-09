<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\captcha;

use yii\base\InvalidConfigException;

/**
 * GdDriver renders the CAPTCHA image based on the code using [GD](http://php.net/manual/en/book.image.php) library.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.1.0
 */
class GdDriver extends Driver
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if (!extension_loaded('gd') || (($gdInfo = gd_info()) && empty($gdInfo['FreeType Support']))) {
            throw new InvalidConfigException('GD PHP extension with FreeType support is required.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function renderImage($code)
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backColor = imagecolorallocate(
            $image,
            (int) ($this->backColor % 0x1000000 / 0x10000),
            (int) ($this->backColor % 0x10000 / 0x100),
            $this->backColor % 0x100
        );
        imagefilledrectangle($image, 0, 0, $this->width - 1, $this->height - 1, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate(
            $image,
            (int) ($this->foreColor % 0x1000000 / 0x10000),
            (int) ($this->foreColor % 0x10000 / 0x100),
            $this->foreColor % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontFile, $code);
        $w = $box[4] - $box[0] + $this->offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;
        $y = round($this->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (random_int(26, 32) * $scale * 0.8);
            $angle = random_int(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $this->fontFile, $letter);
            $x = $box[2] + $this->offset;
        }

        imagecolordeallocate($image, $foreColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }
}