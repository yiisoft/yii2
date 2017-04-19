<?php

namespace yii\captcha\drivers;

use yii\base\Object;

/**
 * Renders the CAPTCHA image based on the code using GD library.
 */
class GdDriver extends Object implements DriverInterface
{
    /**
     * {@inheritdoc}
     */
    public function renderCaptcha($code, ImageSettings $imageSettings)
    {
        $image = imagecreatetruecolor($imageSettings->width, $imageSettings->height);

        $backColor = imagecolorallocate(
            $image,
            (int) ($imageSettings->backColor % 0x1000000 / 0x10000),
            (int) ($imageSettings->backColor % 0x10000 / 0x100),
            $imageSettings->backColor % 0x100
        );

        imagefilledrectangle($image, 0, 0, $imageSettings->width - 1, $imageSettings->height - 1, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($imageSettings->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate(
            $image,
            (int) ($imageSettings->foreColor % 0x1000000 / 0x10000),
            (int) ($imageSettings->foreColor % 0x10000 / 0x100),
            $imageSettings->foreColor % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $imageSettings->fontFile, $code);
        $w = $box[4] - $box[0] + $imageSettings->offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($imageSettings->width - $imageSettings->padding * 2) / $w, ($imageSettings->height - $imageSettings->padding * 2) / $h);
        $x = 10;
        $y = round($imageSettings->height * 27 / 40);
        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (rand(26, 32) * $scale * 0.8);
            $angle = rand(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $foreColor, $imageSettings->fontFile, $letter);
            $x = $box[2] + $imageSettings->offset;
        }

        imagecolordeallocate($image, $foreColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function checkRequirements()
    {
        return $this->isAvailableGd() && in_array('FreeType Support', $this->getGDInfo());
    }

    /**
     * {@inheritdoc}
     */
    public function getRequirementsError()
    {
        return 'Not available GD  extension or either GD without FreeType support';
    }

    /**
     * @codeCoverageIgnore
     * @return string[]
     */
    protected function isAvailableGd()
    {
        return extension_loaded('gd');
    }

    /**
     * @codeCoverageIgnore
     * @return string[]
     */
    protected function getGDInfo()
    {
        return gd_info();
    }
}
